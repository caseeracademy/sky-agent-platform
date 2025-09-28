@php
    $documents = $getRecord()?->applicationDocuments ?? collect();
@endphp

@if($documents->count() > 0)
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Supporting Documents ({{ $documents->count() }})</h4>
        
        <div class="space-y-2">
            @foreach($documents as $document)
                <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            @if(str_contains($document->mime_type ?? '', 'pdf'))
                                <svg class="w-10 h-10 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <svg class="w-10 h-10 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
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
                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                Type: {{ $document->mime_type }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ $document->download_url }}" 
                           target="_blank"
                           class="inline-flex items-center px-4 py-2 border border-blue-300 shadow-sm text-sm font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download
                        </a>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Verified ✓
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="text-center py-6">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No documents uploaded yet</p>
        <p class="text-xs text-gray-400 dark:text-gray-500">Agent needs to upload supporting documents</p>
    </div>
@endif





