<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Programme;
use App\Models\ProgrammeInstance;
use App\Models\Student;
use App\Services\EnrolmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EnquiryController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:manager,student_services');
    }

    public function index(Request $request)
    {
        $query = Enquiry::with(['programme', 'prospectiveProgrammeInstance', 'convertedStudent', 'createdBy']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('enquiry_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        if ($request->filled('programme_id')) {
            $query->where('programme_id', $request->get('programme_id'));
        }

        $query->orderBy('created_at', 'desc');
        $enquiries = $query->paginate(25);

        $programmes = Programme::orderBy('title')->get();

        return view('enquiries.index', compact('enquiries', 'programmes'));
    }

    public function create()
    {
        $programmes = Programme::orderBy('title')->get();
        $programmeInstances = ProgrammeInstance::with('programme')->orderBy('start_date')->get();

        return view('enquiries.create', compact('programmes', 'programmeInstances'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:enquiries,email|unique:students,email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
            'eircode' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'programme_id' => 'required|exists:programmes,id',
            'prospective_cohort_id' => 'nullable|exists:cohorts,id',
            'amount_due' => 'required|numeric|min:0',
            'payment_due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'microsoft_account_required' => 'boolean',
        ]);

        $validated['enquiry_number'] = Enquiry::generateEnquiryNumber();
        $validated['created_by'] = Auth::id();

        Enquiry::create($validated);

        return redirect()->route('enquiries.index')->with('success', 'Enquiry created successfully.');
    }

    public function show(Enquiry $enquiry)
    {
        $enquiry->load(['programme', 'prospectiveProgrammeInstance', 'convertedStudent', 'createdBy', 'updatedBy']);

        return view('enquiries.show', compact('enquiry'));
    }

    public function edit(Enquiry $enquiry)
    {
        $programmes = Programme::orderBy('title')->get();
        $programmeInstances = ProgrammeInstance::with('programme')->orderBy('start_date')->get();

        return view('enquiries.edit', compact('enquiry', 'programmes', 'programmeInstances'));
    }

    public function update(Request $request, Enquiry $enquiry)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('enquiries')->ignore($enquiry->id),
                Rule::unique('students'),
            ],
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
            'eircode' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'programme_id' => 'required|exists:programmes,id',
            'prospective_cohort_id' => 'nullable|exists:cohorts,id',
            'payment_status' => 'required|in:pending,paid,deposit_paid,overdue',
            'amount_due' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'payment_due_date' => 'nullable|date',
            'status' => 'required|in:enquiry,application,accepted,converted,rejected,withdrawn',
            'notes' => 'nullable|string',
            'microsoft_account_required' => 'boolean',
            'microsoft_account_created' => 'boolean',
        ]);

        $validated['updated_by'] = Auth::id();

        $enquiry->update($validated);

        return redirect()->route('enquiries.show', $enquiry)->with('success', 'Enquiry updated successfully.');
    }

    public function destroy(Enquiry $enquiry)
    {
        if ($enquiry->converted_student_id) {
            return redirect()->route('enquiries.index')->with('error', 'Cannot delete enquiry that has been converted to a student.');
        }

        $enquiry->delete();

        return redirect()->route('enquiries.index')->with('success', 'Enquiry deleted successfully.');
    }

    public function convertToStudent(Enquiry $enquiry)
    {
        if (! $enquiry->canConvertToStudent()) {
            return redirect()->back()->with('error', 'Enquiry cannot be converted to student at this time.');
        }

        try {
            DB::beginTransaction();

            $student = Student::create([
                'student_number' => Student::generateStudentNumber(),
                'first_name' => $enquiry->first_name,
                'last_name' => $enquiry->last_name,
                'email' => $enquiry->email,
                'phone' => $enquiry->phone,
                'address' => $enquiry->address,
                'city' => $enquiry->city,
                'county' => $enquiry->county,
                'eircode' => $enquiry->eircode,
                'date_of_birth' => $enquiry->date_of_birth,
                'status' => 'active',
                'notes' => $enquiry->notes,
                'created_by' => Auth::id(),
            ]);

            $enquiry->update([
                'status' => 'converted',
                'converted_student_id' => $student->id,
                'updated_by' => Auth::id(),
            ]);

            if ($enquiry->prospective_cohort_id) {
                $enrolmentService = app(EnrolmentService::class);
                $enrolmentService->enrollStudentInProgramme(
                    $student,
                    $enquiry->programme_id,
                    $enquiry->prospective_cohort_id
                );
            }

            DB::commit();

            return redirect()->route('students.show', $student)->with('success', 'Enquiry successfully converted to student.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to convert enquiry to student: '.$e->getMessage());
        }
    }
}
