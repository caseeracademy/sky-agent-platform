@php
    $scholarship = $scholarship ?? null;
    $university = \App\Models\University::find($scholarship->university_id);
    $degree = \App\Models\Degree::find($scholarship->degree_id);
    $isProgress = $scholarship->type === 'progress';
@endphp

<style>
.how-it-works-container {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-radius: 16px;
    padding: 2rem;
    border: 1px solid #f59e0b;
    position: relative;
    overflow: hidden;
}

.how-it-works-container::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(251, 191, 36, 0.3) 0%, transparent 70%);
    border-radius: 50%;
}

.how-it-works-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #92400e;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 10;
}

.instructions-list {
    list-style: none;
    padding: 0;
    margin: 0;
    position: relative;
    z-index: 10;
}

.instruction-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 8px;
    border-left: 4px solid #f59e0b;
    transition: all 0.3s ease;
}

.instruction-item:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: translateX(4px);
}

.instruction-number {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.instruction-text {
    color: #92400e;
    font-size: 0.875rem;
    line-height: 1.5;
    font-weight: 500;
}

.progress-section {
    margin-top: 2rem;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 12px;
    border: 2px solid #fbbf24;
    position: relative;
    z-index: 10;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.progress-title {
    font-size: 1rem;
    font-weight: 600;
    color: #92400e;
}

.progress-percentage {
    font-size: 0.875rem;
    font-weight: 700;
    color: #d97706;
}

.progress-bar-container {
    background: #fef3c7;
    border-radius: 50px;
    height: 8px;
    overflow: hidden;
    margin-bottom: 0.75rem;
}

.progress-bar {
    background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    height: 100%;
    border-radius: 50px;
    transition: width 0.5s ease;
    position: relative;
}

.progress-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.progress-status {
    font-size: 0.75rem;
    color: #a16207;
    font-weight: 500;
    text-align: center;
}

@media (max-width: 768px) {
    .how-it-works-container {
        padding: 1.5rem;
    }
    
    .how-it-works-title {
        font-size: 1.25rem;
    }
    
    .progress-section {
        padding: 1rem;
    }
}

.dark .how-it-works-container {
    background: linear-gradient(135deg, #451a03 0%, #78350f 100%);
    border-color: #a16207;
}

.dark .how-it-works-title {
    color: #fbbf24;
}

.dark .instruction-item {
    background: rgba(0, 0, 0, 0.3);
    border-left-color: #fbbf24;
}

.dark .instruction-item:hover {
    background: rgba(0, 0, 0, 0.5);
}

.dark .instruction-text {
    color: #fde68a;
}

.dark .progress-section {
    background: rgba(0, 0, 0, 0.4);
    border-color: #fbbf24;
}

.dark .progress-title {
    color: #fbbf24;
}

.dark .progress-percentage {
    color: #fbbf24;
}

.dark .progress-status {
    color: #fde68a;
}
</style>

<div class="how-it-works-container">
    <h3 class="how-it-works-title">
        {{ $isProgress ? 'How This Works' : 'How to Use This Scholarship' }}
    </h3>
    
    <ul class="instructions-list">
        @if($isProgress)
            <li class="instruction-item">
                <span class="instruction-number">1</span>
                <span class="instruction-text">Each approved scholarship application counts as 1 point</span>
            </li>
            <li class="instruction-item">
                <span class="instruction-number">2</span>
                <span class="instruction-text">You need {{ $scholarship->threshold ?? 5 }} points to earn a scholarship</span>
            </li>
            <li class="instruction-item">
                <span class="instruction-number">3</span>
                <span class="instruction-text">This scholarship can be used for any {{ $degree->name ?? 'degree' }} program at {{ $university->name ?? 'this university' }}</span>
            </li>
            <li class="instruction-item">
                <span class="instruction-number">4</span>
                <span class="instruction-text">Once earned, the scholarship never expires until used</span>
            </li>
            <li class="instruction-item">
                <span class="instruction-number">5</span>
                <span class="instruction-text">Points expire on November 30th each year if not completed</span>
            </li>
        @else
            <li class="instruction-item">
                <span class="instruction-number">1</span>
                <span class="instruction-text">This scholarship can be used for any <strong>{{ $degree->name ?? 'degree' }}</strong> program at <strong>{{ $university->name ?? 'this university' }}</strong></span>
            </li>
            <li class="instruction-item">
                <span class="instruction-number">2</span>
                <span class="instruction-text">When creating a new application, select this university and degree level</span>
            </li>
            <li class="instruction-item">
                <span class="instruction-number">3</span>
                <span class="instruction-text">The application fee will be waived automatically</span>
            </li>
            <li class="instruction-item">
                <span class="instruction-number">4</span>
                <span class="instruction-text">Each scholarship can only be used once</span>
            </li>
            <li class="instruction-item">
                <span class="instruction-number">5</span>
                <span class="instruction-text">Earned scholarships never expire until used</span>
            </li>
        @endif
    </ul>
    
    @if($isProgress && $scholarship->progress_percentage)
        <div class="progress-section">
            <div class="progress-header">
                <span class="progress-title">
                    Current Progress: {{ $scholarship->progress_text ?? 'N/A' }}
                </span>
                <span class="progress-percentage">
                    {{ $scholarship->progress_percentage ?? 0 }}%
                </span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: {{ min(100, $scholarship->progress_percentage ?? 0) }}%"></div>
            </div>
            <div class="progress-status">
                {{ $scholarship->status_text ?? 'Working towards scholarship...' }}
            </div>
        </div>
    @endif
</div>
