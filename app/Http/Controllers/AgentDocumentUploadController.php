<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ApplicationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentDocumentUploadController extends Controller
{
    public function uploadDocuments(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'titles' => 'required|array',
            'titles.*' => 'required|string|max:255',
            'resubmit' => 'boolean',
        ]);

        $application = Application::findOrFail($request->application_id);

        // Check if user is authorized to upload documents for this application
        if (Auth::user()->role === 'agent' && $application->agent_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to upload documents for this application.',
            ], 403);
        }

        $uploadedDocuments = [];

        foreach ($request->file('files') as $index => $file) {
            // Store the file
            $filename = time().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('application-documents', $filename, 'public');

            // Create application document record
            $document = ApplicationDocument::create([
                'application_id' => $application->id,
                'uploaded_by_user_id' => Auth::id(),
                'title' => $request->titles[$index],
                'original_filename' => $file->getClientOriginalName(),
                'disk' => 'public',
                'path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);

            $uploadedDocuments[] = $document;
        }

        // Log the document upload
        ApplicationLog::create([
            'application_id' => $application->id,
            'user_id' => Auth::id(),
            'note' => 'Uploaded '.count($uploadedDocuments).' additional document(s) as requested by admin.',
            'status_change' => null,
        ]);

        // Handle resubmit toggle
        if ($request->boolean('resubmit')) {
            $application->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            // Log the status change
            ApplicationLog::create([
                'application_id' => $application->id,
                'user_id' => Auth::id(),
                'note' => 'Application resubmitted with additional documents.',
                'status_change' => 'submitted',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Documents uploaded successfully.',
            'documents' => $uploadedDocuments,
            'resubmitted' => $request->boolean('resubmit'),
        ]);
    }
}
