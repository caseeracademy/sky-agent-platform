<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Http\Request;

class StudentDocumentController extends Controller
{
    public function store(Request $request, Student $student)
    {
        // Validate the request
        $request->validate([
            'document_name' => 'required|string|max:255',
            'document_type' => 'required|string|in:passport,certificate,transcript,photo,visa,language_test,other',
            'document_file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        // Check if the student belongs to the current agent
        if ($student->agent_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this student.');
        }

        // Store the file
        $file = $request->file('document_file');
        $fileName = time().'_'.$file->getClientOriginalName();
        $filePath = $file->storeAs('student-documents', $fileName, 'public');

        // Create the document record
        $document = StudentDocument::create([
            'student_id' => $student->id,
            'name' => $request->document_name,
            'type' => $request->document_type,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully.',
            'document' => $document,
        ]);
    }

    public function replace(Request $request, Student $student, StudentDocument $document)
    {
        // Validate the request - only require the file
        $request->validate([
            'document_file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        // Check if the student belongs to the current agent
        if ($student->agent_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this student.');
        }

        // Check if the document belongs to the student
        if ($document->student_id !== $student->id) {
            abort(403, 'Unauthorized access to this document.');
        }

        // Delete the old file
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($document->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        }

        // Store the new file
        $file = $request->file('document_file');
        $fileName = time().'_'.$file->getClientOriginalName();
        $filePath = $file->storeAs('student-documents', $fileName, 'public');

        // Update the document record - keep existing name and type, only update file details
        $document->update([
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document replaced successfully.',
            'document' => $document,
        ]);
    }

    public function download(Student $student, StudentDocument $document)
    {
        // Check if the student belongs to the current agent
        if ($student->agent_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this student.');
        }

        // Check if the document belongs to the student
        if ($document->student_id !== $student->id) {
            abort(403, 'Unauthorized access to this document.');
        }

        // Check if the file exists
        if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        // Return the file download response
        return \Illuminate\Support\Facades\Storage::disk('public')->download(
            $document->file_path,
            $document->file_name
        );
    }
}
