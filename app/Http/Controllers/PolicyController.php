<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use App\Models\PolicyCategory;
use App\Models\PolicyView;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PolicyController extends Controller
{
    public function __construct()
    {
        // Manager/Student Services can manage policies
        $this->middleware(['auth', 'role:manager,student_services'])->except(['index', 'show', 'download']);

        // All authenticated users can view policies
        $this->middleware('auth')->only(['index', 'show', 'download']);
    }

    /**
     * Display policies for students/staff viewing
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Determine programme types user has access to
        $programmeTypes = $this->getUserProgrammeTypes($user);

        // Get categories with published policies
        $categories = PolicyCategory::active()
            ->ordered()
            ->with(['publishedPolicies' => function ($query) use ($programmeTypes) {
                $query->where(function ($q) use ($programmeTypes) {
                    $q->where('scope', 'college')
                        ->orWhere(function ($subQ) use ($programmeTypes) {
                            $subQ->where('scope', 'programme')
                                ->whereIn('programme_type', $programmeTypes);
                        });
                })
                    ->orderBy('title');
            }])
            ->get()
            ->filter(function ($category) {
                return $category->publishedPolicies->count() > 0;
            });

        // Search functionality
        $searchTerm = $request->get('search');
        if ($searchTerm) {
            $searchResults = Policy::published()
                ->where(function ($query) use ($searchTerm) {
                    $query->where('title', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%")
                        ->orWhere('content', 'like', "%{$searchTerm}%");
                })
                ->where(function ($q) use ($programmeTypes) {
                    $q->where('scope', 'college')
                        ->orWhere(function ($subQ) use ($programmeTypes) {
                            $subQ->where('scope', 'programme')
                                ->whereIn('programme_type', $programmeTypes);
                        });
                })
                ->with('category')
                ->get();
        } else {
            $searchResults = collect();
        }

        return view('policies.index', compact('categories', 'searchResults', 'searchTerm'));
    }

    /**
     * Management interface for staff
     */
    public function manage(Request $request)
    {
        $query = Policy::with(['category', 'creator'])
            ->orderByDesc('created_at');

        // Apply filters
        if ($request->filled('category')) {
            $query->where('policy_category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('programme_type')) {
            $query->where('programme_type', $request->programme_type);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        $policies = $query->paginate(20)->appends($request->query());

        $categories = PolicyCategory::active()->ordered()->get();

        $stats = [
            'total' => Policy::count(),
            'published' => Policy::where('status', 'published')->count(),
            'draft' => Policy::where('status', 'draft')->count(),
            'this_month' => Policy::whereMonth('created_at', now()->month)->count(),
        ];

        return view('policies.manage', compact('policies', 'categories', 'stats'));
    }

    /**
     * Show the form for creating a new policy
     */
    public function create()
    {
        $categories = PolicyCategory::active()->ordered()->get();
        $programmes = Programme::orderBy('title')->get();

        return view('policies.create', compact('categories', 'programmes'));
    }

    /**
     * Store a newly created policy
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'policy_category_id' => 'required|exists:policy_categories,id',
            'scope' => ['required', Rule::in(['college', 'programme'])],
            'programme_type' => ['required', Rule::in(['all', 'elc', 'degree_obu', 'qqi'])],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'policy_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
            'published_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        // Handle file upload
        $filePath = null;
        $fileName = null;
        $fileSize = null;

        \Log::info('File upload debug', [
            'hasFile' => $request->hasFile('policy_file'),
            'files' => $request->allFiles(),
            'request_data' => $request->except(['password', '_token']),
        ]);

        if ($request->hasFile('policy_file')) {
            $file = $request->file('policy_file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();

            \Log::info('Processing file upload', [
                'fileName' => $fileName,
                'fileSize' => $fileSize,
                'fileType' => $file->getMimeType(),
            ]);

            // Store in private disk for security
            try {
                $filePath = $file->store('policies', 'private');

                \Log::info('File stored successfully', [
                    'filePath' => $filePath,
                    'exists' => $filePath ? \Storage::disk('private')->exists($filePath) : false,
                ]);
            } catch (\Exception $e) {
                \Log::error('File storage failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        } else {
            \Log::warning('No file uploaded in request');
        }

        // Create policy
        $policy = Policy::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'policy_category_id' => $validated['policy_category_id'],
            'scope' => $validated['scope'],
            'programme_type' => $validated['programme_type'],
            'status' => $validated['status'],
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'created_by' => auth()->id(),
            'published_at' => $validated['status'] === 'published'
                ? ($validated['published_at'] ?? now())
                : null,
        ]);

        activity()
            ->performedOn($policy)
            ->causedBy(auth()->user())
            ->log('Policy created');

        return redirect()->route('policies.manage')
            ->with('success', 'Policy created successfully.');
    }

    /**
     * Display the specified policy
     */
    public function show(Policy $policy)
    {
        // Check if user can access this policy
        $user = auth()->user();
        $programmeTypes = $this->getUserProgrammeTypes($user);

        if (! $this->canUserAccessPolicy($policy, $programmeTypes)) {
            abort(403, 'You do not have access to this policy.');
        }

        // Log view for analytics
        PolicyView::logView($policy, $user);
        $policy->incrementViews();

        $policy->load(['category', 'creator']);

        return view('policies.show', compact('policy'));
    }

    /**
     * Show the form for editing the specified policy
     */
    public function edit(Policy $policy)
    {
        $categories = PolicyCategory::active()->ordered()->get();
        $programmes = Programme::orderBy('title')->get();

        return view('policies.edit', compact('policy', 'categories', 'programmes'));
    }

    /**
     * Update the specified policy
     */
    public function update(Request $request, Policy $policy)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'policy_category_id' => 'required|exists:policy_categories,id',
            'scope' => ['required', Rule::in(['college', 'programme'])],
            'programme_type' => ['required', Rule::in(['all', 'elc', 'degree_obu', 'qqi'])],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'policy_file' => 'nullable|file|mimes:pdf|max:10240',
            'published_at' => 'nullable|date',
            'remove_file' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        // Handle file operations
        if ($request->boolean('remove_file') && $policy->file_path) {
            Storage::disk('private')->delete($policy->file_path);
            $validated['file_path'] = null;
            $validated['file_name'] = null;
            $validated['file_size'] = null;
        }

        if ($request->hasFile('policy_file')) {
            // Delete old file if exists
            if ($policy->file_path) {
                Storage::disk('private')->delete($policy->file_path);
            }

            $file = $request->file('policy_file');
            $validated['file_path'] = $file->store('policies', 'private');
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_size'] = $file->getSize();
        }

        // Handle publication
        if ($validated['status'] === 'published' && ! $policy->published_at) {
            $validated['published_at'] = $validated['published_at'] ?? now();
        } elseif ($validated['status'] !== 'published') {
            $validated['published_at'] = null;
        }

        $validated['updated_by'] = auth()->id();

        $policy->update($validated);

        activity()
            ->performedOn($policy)
            ->causedBy(auth()->user())
            ->log('Policy updated');

        return redirect()->route('policies.show', $policy)
            ->with('success', 'Policy updated successfully.');
    }

    /**
     * Remove the specified policy
     */
    public function destroy(Policy $policy)
    {
        // Delete associated file
        if ($policy->file_path) {
            Storage::disk('private')->delete($policy->file_path);
        }

        $policyTitle = $policy->title;
        $policy->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['title' => $policyTitle])
            ->log('Policy deleted');

        return redirect()->route('policies.manage')
            ->with('success', 'Policy deleted successfully.');
    }

    /**
     * Download policy file
     */
    public function download(Policy $policy)
    {
        $user = auth()->user();
        $programmeTypes = $this->getUserProgrammeTypes($user);

        if (! $this->canUserAccessPolicy($policy, $programmeTypes)) {
            abort(403, 'You do not have access to this policy.');
        }

        if (! $policy->hasFile()) {
            abort(404, 'Policy file not found.');
        }

        // Log download for analytics
        PolicyView::logView($policy, $user, 'downloaded');
        $policy->incrementDownloads();

        return Storage::disk('private')->download($policy->file_path, $policy->file_name);
    }

    /**
     * Display PDF file inline for viewing
     */
    public function viewPdf(Policy $policy)
    {
        $user = auth()->user();
        $programmeTypes = $this->getUserProgrammeTypes($user);

        if (! $this->canUserAccessPolicy($policy, $programmeTypes)) {
            abort(403, 'You do not have access to this policy.');
        }

        if (! $policy->hasFile()) {
            abort(404, 'Policy file not found.');
        }

        // Log view for analytics (but not download since it's just viewing)
        PolicyView::logView($policy, $user, 'viewed_inline');
        $policy->incrementViews();

        $filePath = Storage::disk('private')->path($policy->file_path);

        if (! file_exists($filePath)) {
            abort(404, 'Policy file not found on disk.');
        }

        // Optimized headers for iframe embedding
        return response(file_get_contents($filePath), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$policy->file_name.'"',
            'Content-Length' => filesize($filePath),
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
            'X-Frame-Options' => 'SAMEORIGIN', // Allow iframe embedding from same origin
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ]);
    }

    /**
     * Get programme types a user has access to
     */
    private function getUserProgrammeTypes($user): array
    {
        // If user is staff, they can see all policies
        if (in_array($user->role, ['manager', 'student_services', 'teacher'])) {
            return ['all', 'elc', 'degree_obu', 'qqi'];
        }

        // For students, determine based on their enrolments
        if ($user->role === 'student' && $user->student) {
            $programmeTypes = ['all']; // Always include college-wide policies

            // Get programme types from student's enrolments
            foreach ($user->student->enrolments as $enrolment) {
                if ($enrolment->programmeInstance) {
                    $programme = $enrolment->programmeInstance->programme;
                    // You'd need to add a programme_type field to programmes table
                    // For now, assume all students can see all programme types
                    $programmeTypes = array_merge($programmeTypes, ['elc', 'degree_obu', 'qqi']);
                }
            }

            return array_unique($programmeTypes);
        }

        return ['all']; // Default to college-wide only
    }

    /**
     * Check if user can access a specific policy
     */
    private function canUserAccessPolicy(Policy $policy, array $programmeTypes): bool
    {
        // Must be published (unless user is staff)
        if (! $policy->isPublished() && ! in_array(auth()->user()->role, ['manager', 'student_services', 'teacher'])) {
            return false;
        }

        // Check programme type access
        return in_array($policy->programme_type, $programmeTypes);
    }
}
