@props(['applications', 'isAdmin' => false])

<style>
    .application-card {
        border: 2px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 1.5rem;
        background: white;
        margin-bottom: 1.25rem;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    
    .application-card:hover {
        border-color: #93c5fd;
        box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1);
        transform: translateY(-1px);
    }
    
    .application-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    
    .application-title-section {
        flex: 1;
        min-width: 0;
    }
    
    .application-program-name {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 0.25rem 0;
        line-height: 1.4;
    }
    
    .application-university-name {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }
    
    .application-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .application-detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .application-detail-label {
        font-size: 0.75rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
    }
    
    .application-detail-value {
        font-size: 0.875rem;
        color: #111827;
        font-weight: 500;
    }
    
    .application-commission {
        color: #059669;
        font-weight: 600;
    }
    
    .application-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .application-view-btn {
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        transition: all 0.2s ease;
    }
    
    .application-view-btn:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
    }
    
    .btn-icon {
        width: 1rem;
        height: 1rem;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #9ca3af;
    }
    
    .empty-state-icon {
        width: 4rem;
        height: 4rem;
        margin: 0 auto 1rem;
        color: #d1d5db;
    }
    
    .empty-state-title {
        font-size: 1rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 0.375rem;
    }
    
    .empty-state-description {
        font-size: 0.875rem;
        color: #9ca3af;
    }
</style>

@if($applications->isEmpty())
    <div class="empty-state">
        <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="empty-state-title">No applications yet</p>
        <p class="empty-state-description">This student hasn't submitted any applications</p>
    </div>
@else
    <div>
        @foreach($applications as $application)
            <div class="application-card">
                <div class="application-header">
                    <div class="application-title-section">
                        <h4 class="application-program-name">{{ $application->program->name }}</h4>
                        <p class="application-university-name">{{ $application->program->university->name }}</p>
                    </div>
                    <span class="fi-badge fi-color-{{ match($application->status) {
                        'pending' => 'warning',
                        'submitted' => 'info',
                        'under_review' => 'warning',
                        'additional_documents_required' => 'danger',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'enrolled' => 'success',
                        'cancelled' => 'gray',
                        default => 'gray'
                    } }} fi-size-md inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset">
                        {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                    </span>
                </div>
                
                <div class="application-details-grid">
                    <div class="application-detail-item">
                        <span class="application-detail-label">Application #</span>
                        <span class="application-detail-value">{{ $application->application_number }}</span>
                    </div>
                    <div class="application-detail-item">
                        <span class="application-detail-label">Submitted</span>
                        <span class="application-detail-value">{{ $application->created_at->format('M j, Y') }}</span>
                    </div>
                    <div class="application-detail-item">
                        <span class="application-detail-label">Commission</span>
                        <span class="application-detail-value application-commission">
                            {{ $application->commission_amount ? '$' . number_format($application->commission_amount, 2) : 'N/A' }}
                        </span>
                    </div>
                    @if($application->submitted_at)
                        <div class="application-detail-item">
                            <span class="application-detail-label">Submitted At</span>
                            <span class="application-detail-value">{{ $application->submitted_at->format('M j, Y') }}</span>
                        </div>
                    @endif
                </div>
                
                <div class="application-actions">
                    <a href="{{ $isAdmin ? route('filament.admin.resources.applications.view', $application->id) : route('filament.agent.resources.applications.view', $application->id) }}" 
                       class="application-view-btn">
                        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View Application
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif
