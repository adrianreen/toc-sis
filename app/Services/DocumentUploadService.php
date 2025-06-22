<?php

namespace App\Services;

use App\Models\StudentDocument;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Exception;

class DocumentUploadService
{
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif'
    ];

    private const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];

    public function uploadDocument(
        Student $student,
        UploadedFile $file,
        string $documentType,
        ?string $title = null,
        ?string $description = null
    ): StudentDocument {
        // Validate file
        $this->validateFile($file);

        // Generate storage path
        $storagePath = $this->generateStoragePath($documentType, $student->id);
        $storedFilename = $this->generateStoredFilename($file, $student->id);
        $fullPath = $storagePath . '/' . $storedFilename;

        // Calculate file hash for integrity
        $fileHash = hash_file('sha256', $file->getPathname());

        // Check for duplicate files
        $existingDocument = StudentDocument::where('student_id', $student->id)
            ->where('document_type', $documentType)
            ->where('file_hash', $fileHash)
            ->first();

        if ($existingDocument) {
            throw new Exception('This file has already been uploaded.');
        }

        // Store the file
        $file->storeAs(
            dirname($fullPath),
            basename($fullPath),
            'student_documents'
        );

        // Create database record
        $document = StudentDocument::create([
            'student_id' => $student->id,
            'document_type' => $documentType,
            'title' => $title ?: $this->generateDefaultTitle($documentType, $file),
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $storedFilename,
            'file_path' => $fullPath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_hash' => $fileHash,
            'status' => 'uploaded',
            'description' => $description,
            'uploaded_by' => Auth::id(),
            'uploaded_at' => now(),
            'metadata' => $this->extractMetadata($file)
        ]);

        // Log activity
        activity()
            ->causedBy(Auth::user())
            ->performedOn($document)
            ->withProperties([
                'student_id' => $student->id,
                'document_type' => $documentType,
                'file_size' => $file->getSize()
            ])
            ->log('Document uploaded');

        return $document;
    }

    private function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new Exception('File size exceeds maximum allowed size of 10MB.');
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            throw new Exception('File type not allowed. Only PDF, JPEG, PNG, and GIF files are permitted.');
        }

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new Exception('File extension not allowed.');
        }

        // Additional security: check if file is actually what it claims to be
        if (!$this->verifyFileType($file)) {
            throw new Exception('File appears to be corrupted or is not the expected file type.');
        }
    }

    private function verifyFileType(UploadedFile $file): bool
    {
        // Read first few bytes to verify file signature
        $handle = fopen($file->getPathname(), 'rb');
        $bytes = fread($handle, 8);
        fclose($handle);

        $hex = bin2hex($bytes);

        // PDF signature
        if (substr($hex, 0, 8) === '25504446') {
            return $file->getMimeType() === 'application/pdf';
        }

        // JPEG signatures
        if (substr($hex, 0, 4) === 'ffd8') {
            return in_array($file->getMimeType(), ['image/jpeg', 'image/jpg']);
        }

        // PNG signature
        if (substr($hex, 0, 16) === '89504e470d0a1a0a') {
            return $file->getMimeType() === 'image/png';
        }

        // GIF signatures
        if (substr($hex, 0, 12) === '474946383761' || substr($hex, 0, 12) === '474946383961') {
            return $file->getMimeType() === 'image/gif';
        }

        return false;
    }

    private function generateStoragePath(string $documentType, int $studentId): string
    {
        $year = date('Y');
        $month = date('m');
        
        return "{$documentType}/{$year}/{$month}";
    }

    private function generateStoredFilename(UploadedFile $file, int $studentId): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Ymd-His');
        $random = Str::random(8);
        
        return "student-{$studentId}-{$timestamp}-{$random}.{$extension}";
    }

    private function generateDefaultTitle(string $documentType, UploadedFile $file): string
    {
        $typeLabels = StudentDocument::getDocumentTypeLabels();
        $baseTitle = $typeLabels[$documentType] ?? 'Document';
        
        return "{$baseTitle} - " . now()->format('M d, Y');
    }

    private function extractMetadata(UploadedFile $file): array
    {
        $metadata = [
            'original_extension' => $file->getClientOriginalExtension(),
            'upload_timestamp' => now()->toISOString(),
            'user_agent' => request()->header('User-Agent')
        ];

        // Extract additional metadata for images
        if (Str::startsWith($file->getMimeType(), 'image/')) {
            try {
                $imageInfo = getimagesize($file->getPathname());
                if ($imageInfo) {
                    $metadata['image_width'] = $imageInfo[0];
                    $metadata['image_height'] = $imageInfo[1];
                }
            } catch (Exception $e) {
                // Ignore errors in metadata extraction
            }
        }

        return $metadata;
    }

    public function deleteDocument(StudentDocument $document): bool
    {
        try {
            // Delete physical file
            if ($document->fileExists()) {
                Storage::disk('student_documents')->delete($document->file_path);
            }

            // Log activity before deletion
            activity()
                ->causedBy(Auth::user())
                ->performedOn($document)
                ->withProperties([
                    'student_id' => $document->student_id,
                    'document_type' => $document->document_type,
                    'original_filename' => $document->original_filename
                ])
                ->log('Document deleted');

            // Soft delete the record
            $document->delete();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function verifyDocument(StudentDocument $document, User $verifier): bool
    {
        $document->update([
            'status' => 'verified',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'rejection_reason' => null
        ]);

        activity()
            ->causedBy($verifier)
            ->performedOn($document)
            ->log('Document verified');

        return true;
    }

    public function rejectDocument(StudentDocument $document, User $verifier, string $reason): bool
    {
        $document->update([
            'status' => 'rejected',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'rejection_reason' => $reason
        ]);

        activity()
            ->causedBy($verifier)
            ->performedOn($document)
            ->withProperties(['rejection_reason' => $reason])
            ->log('Document rejected');

        return true;
    }
}