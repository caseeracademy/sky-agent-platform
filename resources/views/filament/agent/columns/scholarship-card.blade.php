@php
    $record = $getRecord();
    $university = \App\Models\University::find($record->university_id);
    $degree = \App\Models\Degree::find($record->degree_id);
    $isProgress = $record->type === 'progress';
@endphp

<style>
.scholarship-card {
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

.scholarship-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: {{ $isProgress ? 'linear-gradient(180deg, #3b82f6 0%, #1d4ed8 100%)' : 'linear-gradient(180deg, #10b981 0%, #059669 100%)' }};
    border-radius: 0 2px 2px 0;
}

.scholarship-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    border-color: #cbd5e1;
}

.scholarship-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.scholarship-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.25rem 0;
    line-height: 1.2;
}

.scholarship-subtitle {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 500;
}

.status-badge {
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
}

.status-progress {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1e40af;
    border: 1px solid #93c5fd;
}

.status-earned {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    color: #166534;
    border: 1px solid #a7f3d0;
}

.scholarship-content {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1rem;
    align-items: center;
}

.scholarship-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.info-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: #334155;
}

.degree-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.degree-certificate { background: #f1f5f9; color: #475569; }
.degree-diploma { background: #dbeafe; color: #1e40af; }
.degree-bachelor { background: #dcfce7; color: #166534; }
.degree-master { background: #fef3c7; color: #92400e; }
.degree-phd { background: #fecaca; color: #dc2626; }

.progress-section {
    margin-top: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-radius: 12px;
    border: 1px solid #bae6fd;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.progress-text {
    font-size: 0.875rem;
    font-weight: 600;
    color: #0c4a6e;
}

.progress-percentage {
    font-size: 0.75rem;
    font-weight: 700;
    color: #0369a1;
    background: #ffffff;
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    border: 1px solid #7dd3fc;
}

.progress-bar-container {
    background: #e0f2fe;
    border-radius: 50px;
    height: 6px;
    overflow: hidden;
    position: relative;
}

.progress-bar {
    background: linear-gradient(90deg, #0ea5e9 0%, #0284c7 100%);
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
    background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.4) 50%, transparent 100%);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.progress-status {
    font-size: 0.75rem;
    color: #0c4a6e;
    margin-top: 0.5rem;
    text-align: center;
}

.earned-date {
    font-size: 0.75rem;
    color: #059669;
    font-weight: 500;
    background: #ecfdf5;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    border: 1px solid #a7f3d0;
}

.details-button-container {
    display: flex;
    align-items: center;
}

.details-button {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid #2563eb;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
    position: relative;
    overflow: hidden;
}

.details-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.details-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
}

.details-button:hover::before {
    left: 100%;
}

.details-button:active {
    transform: translateY(0);
}

.convert-button {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid #059669;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
    position: relative;
    overflow: hidden;
    margin-left: 0.75rem;
}

.convert-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.convert-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
}

.convert-button:hover::before {
    left: 100%;
}

.convert-button:active {
    transform: translateY(0);
}

.buttons-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

@media (max-width: 768px) {
    .scholarship-card {
        padding: 1rem;
    }
    
    .scholarship-header {
        flex-direction: column;
        gap: 0.75rem;
        align-items: flex-start;
    }
    
    .scholarship-content {
        grid-template-columns: 1fr;
    }
    
    .scholarship-info {
        grid-template-columns: 1fr;
    }
}

.dark .scholarship-card {
    background: linear-gradient(145deg, #1e293b 0%, #334155 100%);
    border-color: #475569;
}

.dark .scholarship-title {
    color: #f1f5f9;
}

.dark .scholarship-subtitle {
    color: #94a3b8;
}

.dark .info-value {
    color: #e2e8f0;
}

.dark .progress-section {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    border-color: #475569;
}

.dark .progress-text {
    color: #7dd3fc;
}

.dark .progress-percentage {
    background: #334155;
    color: #7dd3fc;
    border-color: #475569;
}

.dark .progress-status {
    color: #7dd3fc;
}

.dark .earned-date {
    background: #064e3b;
    color: #6ee7b7;
    border-color: #047857;
}

.dark .details-button {
    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
    border-color: #1d4ed8;
}

.dark .details-button:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
}

.dark .convert-button {
    background: linear-gradient(135deg, #047857 0%, #065f46 100%);
    border-color: #059669;
}

.dark .convert-button:hover {
    background: linear-gradient(135deg, #065f46 0%, #064e3b 100%);
}
</style>

<div class="scholarship-card">
    <div class="scholarship-header">
        <div>
            <h3 class="scholarship-title">
                {{ $record->commission_number ?: ($isProgress ? 'Scholarship in Progress' : 'Earned Scholarship') }}
            </h3>
            <p class="scholarship-subtitle">
                {{ $university->name ?? 'Unknown University' }} • {{ $degree->name ?? 'Unknown Degree' }}
            </p>
        </div>
        <div class="status-badge {{ $isProgress ? 'status-progress' : 'status-earned' }}">
            {{ $isProgress ? 'In Progress' : 'Earned' }}
        </div>
    </div>
    
    <div class="scholarship-content">
        <div class="scholarship-info">
            <div class="info-item">
                <span class="info-label">Degree Level</span>
                <span class="degree-badge degree-{{ strtolower($degree->name ?? 'unknown') }}">
                    {{ $degree->name ?? 'Unknown' }}
                </span>
            </div>
            
            @if($isProgress)
                <div class="info-item">
                    <span class="info-label">Current Points</span>
                    <span class="info-value">{{ $record->current_points ?? 0 }} / {{ $record->threshold ?? 5 }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Progress</span>
                    <span class="info-value">{{ $record->progress_percentage ?? 0 }}%</span>
                </div>
            @else
                <div class="info-item">
                    <span class="info-label">Earned Date</span>
                    <span class="earned-date">
                        {{ $record->earned_at ? $record->earned_at->format('M j, Y') : 'N/A' }}
                    </span>
                </div>
                @if($record->used_at)
                <div class="info-item">
                    <span class="info-label">Used Date</span>
                    <span class="info-value">{{ $record->used_at->format('M j, Y') }}</span>
                </div>
                @endif
            @endif
        </div>
        
        <div class="buttons-container">
            <a href="{{ route('filament.agent.resources.scholarships.view', ['record' => $record->id]) }}" 
               class="details-button">
                View Details
            </a>
            
            @if(!$isProgress && $record->status === 'earned')
                <a href="{{ route('filament.agent.resources.scholarships.convert', ['record' => $record->id]) }}" 
                   class="convert-button">
                    Convert to Application
                </a>
            @endif
        </div>
    </div>
    
    @if($isProgress && ($record->progress_percentage ?? 0) > 0)
        <div class="progress-section">
            <div class="progress-header">
                <span class="progress-text">{{ $record->progress_text ?? 'N/A' }}</span>
                <span class="progress-percentage">{{ $record->progress_percentage ?? 0 }}%</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: {{ min(100, $record->progress_percentage ?? 0) }}%"></div>
            </div>
            <div class="progress-status">
                {{ $record->status_text ?? 'Working towards scholarship...' }}
            </div>
        </div>
    @endif
</div>
