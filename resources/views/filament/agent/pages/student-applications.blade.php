@php
    $student = $record;
    $applications = $student->applications()->with(['program.university'])->get();
@endphp

@if($applications->isEmpty())
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No applications yet</h3>
        <p class="mt-2 text-gray-500 dark:text-gray-400">This student hasn't submitted any university applications.</p>
    </div>
@else
    <div class="space-y-4">
        @foreach($applications as $application)
            <div class="fi-card bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900 dark:text-white">{{ $application->program->name }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $application->program->university->name }}</p>
                        <div class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                            Applied: {{ $application->created_at->format('M j, Y') }}
                        </div>
                    </div>
                    <div class="ml-4">
                        @php
                            $statusColors = [
                                'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                'under_review' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                'pending' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                                'additional_documents_required' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                'enrolled' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                            ];
                            $colorClass = $statusColors[$application->status] ?? $statusColors['pending'];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                            {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif


