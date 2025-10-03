@php
    $record = $getRecord();
    $university = $record->university;
    $degree = $record->degree;
    $isInProgress = $record->status === 'in_progress';
@endphp

<style>
.system-scholarship-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.5rem;
    margin: 0.5rem 0;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.system-scholarship-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: {{ $isInProgress ? 'linear-gradient(180deg, #f59e0b 0%, #d97706 100%)' : 'linear-gradient(180deg, #10b981 0%, #059669 100%)' }};
    border-radius: 0 2px 2px 0;
}

.system-scholarship-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    border-color: #cbd5e1;
}

.system-scholarship-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.system-scholarship-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.25rem 0;
    line-height: 1.2;
}

.system-scholarship-subtitle {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 500;
}

.system-status-badge {
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
}

.system-status-progress {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    border: 1px solid #f59e0b;
}

.system-status-earned {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    color: #166534;
    border: 1px solid #a7f3d0;
}

.system-scholarship-content {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1rem;
    align-items: center;
}

.system-scholarship-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
}

.system-info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.system-info-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.system-info-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: #334155;
}

.system-degree-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.system-degree-certificate { background: #f1f5f9; color: #475569; }
.system-degree-diploma { background: #dbeafe; color: #1e40af; }
.system-degree-bachelor { background: #dcfce7; color: #166534; }
.system-degree-master { background: #fef3c7; color: #92400e; }
.system-degree-phd { background: #fecaca; color: #dc2626; }

.system-progress-section {
    margin-top: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-radius: 12px;
    border: 1px solid #f59e0b;
}

.system-progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.system-progress-text {
    font-size: 0.875rem;
    font-weight: 600;
    color: #92400e;
}

.system-progress-percentage {
    font-size: 0.75rem;
    font-weight: 700;
    color: #d97706;
    background: #ffffff;
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    border: 1px solid #fbbf24;
}

.system-progress-bar-container {
    background: #fef3c7;
    border-radius: 50px;
    height: 8px;
    overflow: hidden;
    position: relative;
}

.system-progress-bar {
    background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    height: 100%;
    border-radius: 50px;
    transition: width 0.5s ease;
    position: relative;
}

.system-progress-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.4) 50%, transparent 100%);
    animation: system-shimmer 2s infinite;
}

@keyframes system-shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.system-progress-status {
    font-size: 0.75rem;
    color: #92400e;
    margin-top: 0.5rem;
    text-align: center;
}

.system-earned-info {
    font-size: 0.75rem;
    color: #059669;
    font-weight: 500;
    background: #ecfdf5;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    border: 1px solid #a7f3d0;
}

.system-details-button-container {
    display: flex;
    align-items: center;
}

.system-details-button {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid #8b5cf6;
    box-shadow: 0 2px 4px rgba(124, 58, 237, 0.2);
    position: relative;
    overflow: hidden;
}

.system-details-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.system-details-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
    background: linear-gradient(135deg, #6d28d9 0%, #5b21b6 100%);
}

.system-details-button:hover::before {
    left: 100%;
}

.system-details-button:active {
    transform: translateY(0);
}

.system-metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 0.75rem;
    margin-top: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-radius: 12px;
    border: 1px solid #bae6fd;
}

.system-metric-item {
    text-align: center;
}

.system-metric-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0c4a6e;
    line-height: 1;
}

.system-metric-label {
    font-size: 0.75rem;
    color: #0369a1;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 0.25rem;
}

@media (max-width: 768px) {
    .system-scholarship-card {
        padding: 1rem;
    }
    
    .system-scholarship-header {
        flex-direction: column;
        gap: 0.75rem;
        align-items: flex-start;
    }
    
    .system-scholarship-content {
        grid-template-columns: 1fr;
    }
    
    .system-scholarship-info {
        grid-template-columns: 1fr;
    }
    
    .system-metrics-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.dark .system-scholarship-card {
    background: linear-gradient(145deg, #1e293b 0%, #334155 100%);
    border-color: #475569;
}

.dark .system-scholarship-title {
    color: #f1f5f9;
}

.dark .system-scholarship-subtitle {
    color: #94a3b8;
}

.dark .system-info-value {
    color: #e2e8f0;
}

.dark .system-progress-section {
    background: linear-gradient(135deg, #451a03 0%, #78350f 100%);
    border-color: #a16207;
}

.dark .system-progress-text {
    color: #fbbf24;
}

.dark .system-progress-percentage {
    background: #78350f;
    color: #fbbf24;
    border-color: #a16207;
}

.dark .system-progress-status {
    color: #fbbf24;
}

.dark .system-earned-info {
    background: #064e3b;
    color: #6ee7b7;
    border-color: #047857;
}

.dark .system-details-button {
    background: linear-gradient(135deg, #5b21b6 0%, #4c1d95 100%);
    border-color: #6d28d9;
}

.dark .system-details-button:hover {
    background: linear-gradient(135deg, #4c1d95 0%, #3730a3 100%);
}

.dark .system-metrics-grid {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    border-color: #475569;
}

.dark .system-metric-value {
    color: #7dd3fc;
}

.dark .system-metric-label {
    color: #0284c7;
}
</style>

<div class="system-scholarship-card">
    <div class="system-scholarship-header">
        <div>
            <h3 class="system-scholarship-title">
                System Scholarship Progress
            </h3>
            <p class="system-scholarship-subtitle">
                {{ $university->name ?? 'Unknown University' }} • {{ $degree->name ?? 'Unknown Degree' }}
            </p>
        </div>
        <div class="system-status-badge {{ $isInProgress ? 'system-status-progress' : 'system-status-earned' }}">
            {{ $isInProgress ? 'In Progress' : 'Earned' }}
        </div>
    </div>
    
    <div class="system-scholarship-content">
        <div class="system-scholarship-info">
            <div class="system-info-item">
                <span class="system-info-label">Degree Level</span>
                <span class="system-degree-badge system-degree-{{ strtolower($degree->name ?? 'unknown') }}">
                    {{ $degree->name ?? 'Unknown' }}
                </span>
            </div>
            
            <div class="system-info-item">
                <span class="system-info-label">Total Students</span>
                <span class="system-info-value">{{ $record->total_students ?? 0 }}</span>
            </div>
            
            <div class="system-info-item">
                <span class="system-info-label">System Progress</span>
                <span class="system-info-value">{{ $record->progress_text ?? 'N/A' }}</span>
            </div>
            
            @if($isInProgress)
                <div class="system-info-item">
                    <span class="system-info-label">Need More</span>
                    <span class="system-info-value">{{ $record->students_needed_for_next ?? 0 }} students</span>
                </div>
            @else
                <div class="system-info-item">
                    <span class="system-info-label">Scholarships Earned</span>
                    <span class="system-earned-info">
                        {{ $record->system_scholarships_earned ?? 0 }} scholarship{{ ($record->system_scholarships_earned ?? 0) !== 1 ? 's' : '' }}
                    </span>
                </div>
            @endif
        </div>
        
        <div class="system-details-button-container">
            <a href="{{ route('filament.admin.resources.system-scholarship-awards.view', ['record' => $record->id]) }}" 
               class="system-details-button">
                View Agents
            </a>
        </div>
    </div>
    
    @if($isInProgress && ($record->progress_percentage ?? 0) > 0)
        <div class="system-progress-section">
            <div class="system-progress-header">
                <span class="system-progress-text">{{ $record->progress_text ?? 'N/A' }}</span>
                <span class="system-progress-percentage">{{ $record->progress_percentage ?? 0 }}%</span>
            </div>
            <div class="system-progress-bar-container">
                <div class="system-progress-bar" style="width: {{ min(100, $record->progress_percentage ?? 0) }}%"></div>
            </div>
            <div class="system-progress-status">
                {{ $record->status_text ?? 'Working towards system scholarship...' }}
            </div>
        </div>
    @endif
    
    <div class="system-metrics-grid">
        <div class="system-metric-item">
            <div class="system-metric-value">{{ $record->university_threshold ?? 4 }}</div>
            <div class="system-metric-label">Uni Threshold</div>
        </div>
        <div class="system-metric-item">
            <div class="system-metric-value">{{ $record->agent_threshold ?? 5 }}</div>
            <div class="system-metric-label">Agent Threshold</div>
        </div>
        <div class="system-metric-item">
            <div class="system-metric-value">{{ $record->students_per_system_scholarship ?? 20 }}</div>
            <div class="system-metric-label">Per System Award</div>
        </div>
        <div class="system-metric-item">
            <div class="system-metric-value">{{ count($record->contributing_agents ?? []) }}</div>
            <div class="system-metric-label">Active Agents</div>
        </div>
    </div>
</div>
