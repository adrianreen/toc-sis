<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentDocument;
use App\Services\DocumentUploadService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudentDocumentController extends Controller
{
    private DocumentUploadService $documentService;

    public function __construct(DocumentUploadService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Display student documents
     */
    public function index(Student $student, Request $request)
    {
        // Check if user can view this student's documents
        if (Auth::user()->role === 'student' && Auth::user()->student_id !== $student->id) {
            abort(403, 'Unauthorized to view these documents.');
        }

        if (! in_array(Auth::user()->role, ['student', 'manager', 'student_services', 'teacher'])) {
            abort(403, 'Unauthorized.');
        }

        $query = $student->documents()->with(['uploadedBy', 'verifiedBy']);

        // Filter by document type
        if ($request->filled('type')) {
            $query->where('document_type', $request->get('type'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $documents = $query->orderBy('uploaded_at', 'desc')->paginate(20);
        $documentTypes = StudentDocument::getDocumentTypeLabels();

        return view('students.documents.index', [
            'student' => $student,
            'documents' => $documents,
            'documentTypes' => $documentTypes,
        ]);
    }

    /**
     * Show upload form
     */
    public function create(Student $student, Request $request)
    {
        // Check if user can manage this student's documents
        if (Auth::user()->role === 'student' && Auth::user()->student_id !== $student->id) {
            abort(403, 'Unauthorized to manage these documents.');
        }

        if (! in_array(Auth::user()->role, ['student', 'manager', 'student_services'])) {
            abort(403, 'Unauthorized.');
        }

        $documentType = $request->get('type', 'other');
        $documentTypes = StudentDocument::getDocumentTypeLabels();

        return view('students.documents.create', [
            'student' => $student,
            'documentType' => $documentType,
            'documentTypes' => $documentTypes,
        ]);
    }

    /**
     * Handle document upload
     */
    public function store(Student $student, Request $request)
    {
        \Log::info('Document upload attempt', [
            'student_id' => $student->id,
            'user_id' => Auth::id(),
            'has_files' => $request->hasFile('files'),
            'files_count' => $request->hasFile('files') ? count($request->file('files')) : 0,
        ]);

        // Check if user can manage this student's documents
        if (Auth::user()->role === 'student' && Auth::user()->student_id !== $student->id) {
            abort(403, 'Unauthorized to manage these documents.');
        }

        if (! in_array(Auth::user()->role, ['student', 'manager', 'student_services'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'document_type' => 'required|in:rpl_proof,transcript,certificate,identity_document,qualification_certificate,other',
            'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png,gif|max:10240', // 10MB
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $uploadedDocuments = [];
        $errors = [];

        foreach ($request->file('files') as $file) {
            try {
                \Log::info('Attempting to upload file', [
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ]);

                $document = $this->documentService->uploadDocument(
                    $student,
                    $file,
                    $request->get('document_type'),
                    $request->get('title'),
                    $request->get('description')
                );
                $uploadedDocuments[] = $document;

                \Log::info('File uploaded successfully', ['document_id' => $document->id]);
            } catch (Exception $e) {
                \Log::error('File upload failed', [
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $errors[] = "Failed to upload {$file->getClientOriginalName()}: {$e->getMessage()}";
            }
        }

        $successCount = count($uploadedDocuments);
        $errorCount = count($errors);

        if ($successCount > 0 && $errorCount === 0) {
            $message = $successCount === 1 ? 'Document uploaded successfully.' : "{$successCount} documents uploaded successfully.";

            return redirect()->route('students.documents.index', $student)
                ->with('success', $message);
        } elseif ($successCount > 0 && $errorCount > 0) {
            return redirect()->route('students.documents.index', $student)
                ->with('warning', "{$successCount} documents uploaded successfully, but {$errorCount} failed.")
                ->with('errors', $errors);
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to upload documents.')
                ->with('errors', $errors);
        }
    }

    /**
     * Download document
     */
    public function download(StudentDocument $document)
    {
        // Check if user can view this student's documents
        if (Auth::user()->role === 'student' && Auth::user()->student_id !== $document->student_id) {
            abort(403, 'Unauthorized to access this document.');
        }

        if (! in_array(Auth::user()->role, ['student', 'manager', 'student_services', 'teacher'])) {
            abort(403, 'Unauthorized.');
        }

        if (! $document->fileExists()) {
            abort(404, 'File not found.');
        }

        // Log download activity
        activity()
            ->causedBy(Auth::user())
            ->performedOn($document)
            ->log('Document downloaded');

        return Storage::disk('student_documents')->download(
            $document->file_path,
            $document->original_filename
        );
    }

    /**
     * View document (for PDFs)
     */
    public function view(StudentDocument $document)
    {
        // Check if user can view this student's documents
        if (Auth::user()->role === 'student' && Auth::user()->student_id !== $document->student_id) {
            abort(403, 'Unauthorized to access this document.');
        }

        if (! in_array(Auth::user()->role, ['student', 'manager', 'student_services', 'teacher'])) {
            abort(403, 'Unauthorized.');
        }

        if (! $document->fileExists()) {
            abort(404, 'File not found.');
        }

        if ($document->mime_type !== 'application/pdf') {
            return $this->download($document);
        }

        // Log view activity
        activity()
            ->causedBy(Auth::user())
            ->performedOn($document)
            ->log('Document viewed');

        return Storage::disk('student_documents')->response(
            $document->file_path,
            $document->original_filename,
            ['Content-Type' => $document->mime_type]
        );
    }

    /**
     * Delete document
     */
    public function destroy(StudentDocument $document)
    {
        // Check if user can manage this student's documents
        if (Auth::user()->role === 'student' && Auth::user()->student_id !== $document->student_id) {
            abort(403, 'Unauthorized to manage this document.');
        }

        if (! in_array(Auth::user()->role, ['student', 'manager', 'student_services'])) {
            abort(403, 'Unauthorized.');
        }

        if ($this->documentService->deleteDocument($document)) {
            return redirect()->route('students.documents.index', $document->student)
                ->with('success', 'Document deleted successfully.');
        }

        return redirect()->back()
            ->with('error', 'Failed to delete document.');
    }

    /**
     * Verify document (staff only)
     */
    public function verify(StudentDocument $document)
    {
        if (! in_array(Auth::user()->role, ['manager', 'student_services'])) {
            abort(403, 'Unauthorized.');
        }

        if ($this->documentService->verifyDocument($document, Auth::user())) {
            return redirect()->back()
                ->with('success', 'Document verified successfully.');
        }

        return redirect()->back()
            ->with('error', 'Failed to verify document.');
    }

    /**
     * Reject document (staff only)
     */
    public function reject(StudentDocument $document, Request $request)
    {
        if (! in_array(Auth::user()->role, ['manager', 'student_services'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($this->documentService->rejectDocument($document, Auth::user(), $request->get('rejection_reason'))) {
            return redirect()->back()
                ->with('success', 'Document rejected.');
        }

        return redirect()->back()
            ->with('error', 'Failed to reject document.');
    }
}
