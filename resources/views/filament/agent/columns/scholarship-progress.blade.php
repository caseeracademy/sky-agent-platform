@php
    $record = $getRecord();
    $progressPercentage = $record->progress_percentage ?? 0;
    $progressText = $record->progress_text ?? 'N/A';
    $type = $record->type ?? 'completed';
@endphp

<div class="flex items-center space-x-3">
    @if($type === 'progress')
        {{-- Progress Bar for In-Progress Scholarships --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ $progressText }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $progressPercentage }}%
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                <div 
                    class="h-2 rounded-full transition-all duration-300 {{ $progressPercentage >= 100 ? 'bg-green-600' : ($progressPercentage >= 50 ? 'bg-blue-600' : 'bg-blue-400') }}"
                    style="width: {{ min(100, $progressPercentage) }}%"
                ></div>
            </div>
        </div>
    @else
        {{-- Completed Scholarship Badge --}}
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <span class="ml-2 text-sm font-medium text-green-700 dark:text-green-400">
                Completed
            </span>
        </div>
    @endif
</div>
