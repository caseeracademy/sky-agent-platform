<style>
.scholarship-widget-header {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 1rem;
}

.ready-alert {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border: 2px solid #10b981;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.ready-alert-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #065f46;
    margin-bottom: 0.25rem;
}

.ready-alert-text {
    color: #047857;
    font-size: 0.875rem;
}

.progress-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    transition: all 0.2s;
}

.progress-card:hover {
    border-color: #0ea5e9;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.1);
}

.progress-card.complete {
    background: linear-gradient(135deg, #fff7ed 0%, #fed7aa 100%);
    border-color: #f97316;
}

.progress-card.available {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    border-color: #10b981;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.university-name {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.degree-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    background: #e0f2fe;
    color: #0369a1;
    margin-right: 0.5rem;
}

.days-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.days-badge.urgent {
    background: #fee2e2;
    color: #991b1b;
}

.days-badge.warning {
    background: #fef3c7;
    color: #92400e;
}

.days-badge.good {
    background: #dcfce7;
    color: #14532d;
}

.progress-count {
    font-size: 1.25rem;
    font-weight: 700;
    color: #475569;
}

.progress-count.ready {
    color: #10b981;
}

.progress-count.complete {
    color: #f97316;
}

.progress-bar-container {
    width: 100%;
    height: 12px;
    background: #e5e7eb;
    border-radius: 9999px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-bar {
    height: 100%;
    border-radius: 9999px;
    transition: width 0.5s ease-out;
}

.progress-bar.in-progress {
    background: linear-gradient(90deg, #0ea5e9 0%, #3b82f6 100%);
}

.progress-bar.complete {
    background: linear-gradient(90deg, #f97316 0%, #fb923c 100%);
}

.progress-bar.available {
    background: linear-gradient(90deg, #10b981 0%, #34d399 100%);
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #64748b;
}

.empty-state-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.empty-state-text {
    font-size: 0.875rem;
    color: #64748b;
}
</style>

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="scholarship-widget-header">
                Scholarship Progress
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            @if($this->getTotalAvailableScholarships() > 0)
                <span style="display: inline-block; padding: 0.375rem 0.75rem; background: #dcfce7; color: #14532d; border-radius: 9999px; font-size: 0.875rem; font-weight: 600;">
                    {{ $this->getTotalAvailableScholarships() }} Ready
                </span>
            @endif
        </x-slot>

        <div class="space-y-4">
            @if($this->getTotalAvailableScholarships() > 0)
                <div class="ready-alert">
                    <div class="ready-alert-title">
                        {{ $this->getTotalAvailableScholarships() }} Scholarship{{ $this->getTotalAvailableScholarships() > 1 ? 's' : '' }} Ready!
                    </div>
                    <p class="ready-alert-text">You can now use these for free student applications!</p>
                </div>
            @endif

            @php
                $progressData = $this->getScholarshipProgress();
            @endphp

            @if($progressData->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-title">No scholarship progress yet</div>
                    <p class="empty-state-text">Start applying students to universities with scholarship programs!</p>
                </div>
            @else
                <div>
                    @foreach($progressData as $progress)
                        <div class="progress-card {{ $progress['is_available'] ? 'available' : ($progress['is_complete'] ? 'complete' : '') }}">
                            <div class="progress-header">
                                <div>
                                    <div class="university-name">{{ $progress['university_name'] }}</div>
                                    <div>
                                        <span class="degree-badge">{{ $progress['degree_name'] }}</span>
                                        
                                        @if($progress['days_until_expiry'] > 0 && $progress['current_points'] > 0)
                                            <span class="days-badge {{ $progress['days_until_expiry'] > 30 ? 'good' : ($progress['days_until_expiry'] > 7 ? 'warning' : 'urgent') }}">
                                                {{ $progress['days_until_expiry'] }} days left
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="text-right">
                                    @if($progress['is_available'])
                                        <div class="progress-count ready">
                                            {{ $progress['available_scholarships'] }} Ready
                                        </div>
                                    @elseif($progress['is_complete'])
                                        <div class="progress-count complete">
                                            Complete
                                        </div>
                                    @else
                                        <div class="progress-count">
                                            {{ $progress['current_points'] }}/{{ $progress['threshold'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem;">
                                    <span>{{ $progress['status'] ?? ($progress['is_available'] ? 'Available' : ($progress['is_complete'] ? 'Complete' : 'In Progress')) }}</span>
                                    <span>{{ $progress['progress_percentage'] }}%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar {{ $progress['is_available'] ? 'available' : ($progress['is_complete'] ? 'complete' : 'in-progress') }}"
                                        style="width: {{ $progress['progress_percentage'] }}%">
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Details -->
                            <div class="text-sm text-gray-600">
                                @if($progress['is_available'])
                                    <div class="flex items-center gap-1">
                                        <x-heroicon-o-check-circle class="h-4 w-4 text-green-600" />
                                        <span>Completed! You earned {{ $progress['available_scholarships'] }} scholarship{{ $progress['available_scholarships'] > 1 ? 's' : '' }} from {{ $progress['total_points'] }} approved students.</span>
                                    </div>
                                @elseif($progress['is_complete'])
                                    <div class="flex items-center gap-1">
                                        <x-heroicon-o-exclamation-triangle class="h-4 w-4 text-orange-600" />
                                        <span>You have {{ $progress['current_points'] }} approved students! A scholarship will be awarded soon.</span>
                                    </div>
                                @elseif($progress['current_points'] > 0)
                                    <div class="flex items-center gap-1">
                                        <x-heroicon-o-chart-bar class="h-4 w-4 text-blue-600" />
                                        <span>Great progress! You need {{ $progress['threshold'] - $progress['current_points'] }} more approved student{{ $progress['threshold'] - $progress['current_points'] > 1 ? 's' : '' }} to earn a scholarship.</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1">
                                        <x-heroicon-o-plus-circle class="h-4 w-4 text-gray-500" />
                                        <span>Apply {{ $progress['threshold'] }} students to this program to earn a scholarship.</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($this->getTotalActivePoints() > 0)
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-chart-bar class="h-6 w-6 text-blue-600" />
                        <div>
                            <h4 class="font-semibold text-blue-800">Total Active Points: {{ $this->getTotalActivePoints() }}</h4>
                            <p class="text-sm text-blue-600">Keep applying students to earn more scholarships!</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
