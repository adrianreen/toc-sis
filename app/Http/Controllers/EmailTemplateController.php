<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:manager,student_services']);
    }

    public function index(Request $request)
    {
        $query = EmailTemplate::with(['createdBy', 'updatedBy'])
                              ->withCount('emailLogs');

        // Filter by category
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Filter by active status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search by name or subject
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $templates = $query->latest()->paginate(15);

        $categories = ['academic', 'administrative', 'system'];

        return view('admin.email-templates.index', compact('templates', 'categories'));
    }

    public function create()
    {
        $availableVariables = EmailTemplate::getAvailableVariables();
        return view('admin.email-templates.create', compact('availableVariables'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:email_templates,name',
            'subject' => 'required|string|max:255',
            'category' => 'required|in:academic,administrative,system',
            'description' => 'nullable|string|max:500',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->boolean('is_active', true);

        $template = EmailTemplate::create($validated);

        return redirect()
            ->route('admin.email-templates.show', $template)
            ->with('success', 'Email template created successfully.');
    }

    public function show(EmailTemplate $emailTemplate)
    {
        $emailTemplate->load(['createdBy', 'updatedBy']);
        
        // Get recent email logs for this template
        $recentLogs = $emailTemplate->emailLogs()
                                   ->with(['student', 'sentBy'])
                                   ->latest()
                                   ->limit(10)
                                   ->get();

        $availableVariables = EmailTemplate::getAvailableVariables();

        return view('admin.email-templates.show', compact('emailTemplate', 'recentLogs', 'availableVariables'));
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        $availableVariables = EmailTemplate::getAvailableVariables();
        return view('admin.email-templates.edit', compact('emailTemplate', 'availableVariables'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:email_templates,name,' . $emailTemplate->id,
            'subject' => 'required|string|max:255',
            'category' => 'required|in:academic,administrative,system',
            'description' => 'nullable|string|max:500',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['updated_by'] = Auth::id();
        $validated['is_active'] = $request->boolean('is_active', $emailTemplate->is_active);

        $emailTemplate->update($validated);

        return redirect()
            ->route('admin.email-templates.show', $emailTemplate)
            ->with('success', 'Email template updated successfully.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        // Prevent deletion of system templates
        if ($emailTemplate->system_template) {
            return back()->with('error', 'System templates cannot be deleted.');
        }

        // Check if template has been used
        if ($emailTemplate->emailLogs()->exists()) {
            return back()->with('error', 'Cannot delete template that has been used to send emails.');
        }

        $emailTemplate->delete();

        return redirect()
            ->route('admin.email-templates.index')
            ->with('success', 'Email template deleted successfully.');
    }

    public function preview(Request $request, EmailTemplate $emailTemplate)
    {
        // Get a sample student for preview
        $sampleStudent = Student::with('enrolments.programmeInstance.programme', 'enrolments.moduleInstance.module')->first();
        
        if (!$sampleStudent) {
            return back()->with('error', 'No students available for preview. Please add a student first.');
        }

        $processed = $emailTemplate->replaceVariables($sampleStudent, Auth::user());

        return view('admin.email-templates.preview', compact('emailTemplate', 'processed', 'sampleStudent'));
    }

    public function duplicate(EmailTemplate $emailTemplate)
    {
        $newTemplate = $emailTemplate->replicate();
        $newTemplate->name = $emailTemplate->name . ' (Copy)';
        $newTemplate->created_by = Auth::id();
        $newTemplate->updated_by = null;
        $newTemplate->system_template = false;
        $newTemplate->save();

        return redirect()
            ->route('admin.email-templates.edit', $newTemplate)
            ->with('success', 'Template duplicated successfully. You can now edit the copy.');
    }
}