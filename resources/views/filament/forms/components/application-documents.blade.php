@php
    $documents = $getRecord()?->applicationDocuments ?? collect();
@endphp

@if($documents->count() > 0)
    <div class="space-y-2">
        @foreach($documents as $document)
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        @if(str_contains($document->mime_type, 'pdf'))
                            <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $document->original_filename }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $document->formatted_file_size }} • Uploaded {{ $document->created_at->diffForHumans() }} by {{ $document->uploadedByUser->name }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ $document->download_url }}" 
                       target="_blank"
                       class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Download
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@else
    <p class="text-sm text-gray-500 dark:text-gray-400">No documents uploaded yet.</p>
@endif












