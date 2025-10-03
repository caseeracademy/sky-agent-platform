@php
    $record = $getRecord();
    $totalApplications = $record->total_applications_count ?? 0;
    $systemThreshold = 4; // Default, will be overridden by actual requirements
    $earnedScholarships = floor($record->system_scholarships_earned ?? 0);
    
    // Try to get actual system threshold from university
    if ($record->university && method_exists($record->university, 'getScholarshipRequirementForDegree')) {
        $requirements = $record->university->getScholarshipRequirementForDegree($record->degree_type);
        if ($requirements && isset($requirements['system_threshold'])) {
            $systemThreshold = (int) $requirements['system_threshold'];
        }
    }
    
    // Calculate progress toward next scholarship
    $remainder = $totalApplications % $systemThreshold;
    $progressPercentage = $systemThreshold > 0 ? min(100, round(($remainder / $systemThreshold) * 100)) : 0;
    $progressText = "{$remainder}/{$systemThreshold}";
    
    // If we have earned scholarships, show that too
    if ($earnedScholarships > 0) {
        $progressText = "{$earnedScholarships} earned + {$remainder}/{$systemThreshold}";
    }
@endphp

<div class="flex items-center space-x-3">
    @if($earnedScholarships > 0)
        {{-- Show earned scholarships count --}}
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <span class="ml-2 text-sm font-medium text-green-700 dark:text-green-400">
                {{ $earnedScholarships }} Earned
            </span>
        </div>
    @endif
    
    @if($remainder > 0)
        {{-- Progress Bar for Next Scholarship --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Next: {{ $remainder }}/{{ $systemThreshold }}
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
            <div class="mt-1 text-xs text-gray-500">
                {{ $systemThreshold - $remainder }} more students needed
            </div>
        </div>
    @else
        {{-- No progress toward next scholarship --}}
        <div class="text-sm text-gray-500">
            @if($earnedScholarships > 0)
                Ready for next cycle
            @else
                No applications yet
            @endif
        </div>
    @endif
</div>
