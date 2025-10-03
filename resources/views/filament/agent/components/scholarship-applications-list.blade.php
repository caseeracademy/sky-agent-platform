@php
    $applications = $applications ?? collect();
    $scholarship = $scholarship ?? null;
@endphp

<style>
.applications-container {
    display: grid;
    gap: 1rem;
}

.application-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.application-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #10b981 0%, #059669 100%);
    border-radius: 0 2px 2px 0;
}

.application-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    border-color: #cbd5e1;
}

.student-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
}

.student-name {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.status-approved {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    color: #166534;
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: 1px solid #a7f3d0;
}

.application-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.detail-value {
    font-size: 0.875rem;
    font-weight: 500;
    color: #334155;
}

.application-number {
    background: #f1f5f9;
    color: #475569;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-block;
}

.points-value {
    color: #059669;
    font-weight: 700;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    background: linear-gradient(145deg, #f8fafc 0%, #f1f5f9 100%);
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
}

.empty-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 0.5rem;
}

.empty-description {
    font-size: 0.875rem;
    color: #94a3b8;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .application-details {
        grid-template-columns: 1fr;
    }
    
    .student-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
}

.dark .application-card {
    background: linear-gradient(145deg, #1e293b 0%, #334155 100%);
    border-color: #475569;
}

.dark .student-name {
    color: #f1f5f9;
}

.dark .detail-value {
    color: #e2e8f0;
}

.dark .application-number {
    background: #475569;
    color: #cbd5e1;
}

.dark .empty-state {
    background: linear-gradient(145deg, #1e293b 0%, #334155 100%);
    border-color: #475569;
}

.dark .empty-title {
    color: #94a3b8;
}

.dark .empty-description {
    color: #64748b;
}
</style>

@if($applications->count() > 0)
    <div class="applications-container">
        @foreach($applications as $application)
            <div class="application-card">
                <div class="student-header">
                    <h4 class="student-name">{{ $application->student->name }}</h4>
                    <span class="status-approved">Approved</span>
                </div>
                
                <div class="application-details">
                    <div class="detail-item">
                        <span class="detail-label">Program</span>
                        <span class="detail-value">{{ $application->program->name }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Application Number</span>
                        <span class="application-number">{{ $application->application_number }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Submitted Date</span>
                        <span class="detail-value">
                            {{ $application->submitted_at ? $application->submitted_at->format('M j, Y') : 'N/A' }}
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Scholarship Points</span>
                        <span class="detail-value points-value">+1 Point</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="empty-state">
        <div class="empty-title">No Contributing Applications</div>
        <div class="empty-description">
            Applications must be approved and set as scholarship type to contribute to this progress.
            <br>Start submitting scholarship applications to see them here.
        </div>
    </div>
@endif
