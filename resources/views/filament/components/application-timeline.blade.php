@props(['logs'])

<style>
    .timeline-container {
        position: relative;
        padding-left: 2rem;
    }
    
    .timeline-container::before {
        content: '';
        position: absolute;
        left: 1rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #e5e7eb, #d1d5db);
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
        padding-left: 2rem;
    }
    
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    
    .timeline-dot {
        position: absolute;
        left: -0.5rem;
        top: 0.5rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 3px #e5e7eb;
        z-index: 2;
    }
    
    .timeline-content {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #f3f4f6;
        transition: all 0.2s ease;
    }
    
    .timeline-content:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }
    
    .timeline-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }
    
    .timeline-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }
    
    .timeline-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .timeline-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.75rem;
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .timeline-user {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .timeline-user-avatar {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.75rem;
    }
    
    .timeline-user-info {
        display: flex;
        flex-direction: column;
    }
    
    .timeline-user-name {
        font-weight: 500;
        color: #374151;
    }
    
    .timeline-user-role {
        font-size: 0.75rem;
        color: #9ca3af;
    }
    
    .timeline-time {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        text-align: right;
    }
    
    .timeline-date {
        font-weight: 500;
        color: #374151;
    }
    
    .timeline-ago {
        font-size: 0.75rem;
        color: #9ca3af;
    }
    
    .timeline-description {
        color: #4b5563;
        line-height: 1.5;
        margin: 0;
    }
    
    .timeline-status-change {
        margin-top: 0.75rem;
        padding: 0.75rem;
        background: #f9fafb;
        border-radius: 0.5rem;
        border-left: 3px solid #e5e7eb;
    }
    
    .timeline-status-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    
    .timeline-status-value {
        font-size: 0.875rem;
        color: #374151;
        font-weight: 500;
    }
    
    /* Status-specific colors */
    .timeline-dot.created { background: #3b82f6; }
    .timeline-dot.submitted { background: #10b981; }
    .timeline-dot.approved { background: #f59e0b; }
    .timeline-dot.rejected { background: #ef4444; }
    .timeline-dot.review { background: #8b5cf6; }
    .timeline-dot.document { background: #06b6d4; }
    .timeline-dot.default { background: #6b7280; }
    
    .timeline-badge.created { background: #dbeafe; color: #1e40af; }
    .timeline-badge.submitted { background: #d1fae5; color: #065f46; }
    .timeline-badge.approved { background: #fef3c7; color: #92400e; }
    .timeline-badge.rejected { background: #fee2e2; color: #991b1b; }
    .timeline-badge.review { background: #ede9fe; color: #6d28d9; }
    .timeline-badge.document { background: #cffafe; color: #0e7490; }
    .timeline-badge.default { background: #f3f4f6; color: #374151; }
    
    .timeline-status-change.created { border-left-color: #3b82f6; }
    .timeline-status-change.submitted { border-left-color: #10b981; }
    .timeline-status-change.approved { border-left-color: #f59e0b; }
    .timeline-status-change.rejected { border-left-color: #ef4444; }
    .timeline-status-change.review { border-left-color: #8b5cf6; }
    .timeline-status-change.document { border-left-color: #06b6d4; }
    .timeline-status-change.default { border-left-color: #6b7280; }
</style>

@if($logs->isEmpty())
    <div class="text-center py-12">
        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No activity yet</h3>
        <p class="text-gray-500">Activity will appear here as you progress the application.</p>
    </div>
@else
    <div class="timeline-container">
        @foreach($logs as $log)
            @php
                $userName = $log->user->name ?? 'System';
                $userRole = $log->user ? Str::of($log->user->role)->headline() : 'System';
                $userInitials = strtoupper(substr($userName, 0, 1) . substr($userName, strpos($userName, ' ') + 1, 1));
                $timestamp = $log->created_at->format('M j, Y');
                $timeAgo = $log->created_at->diffForHumans();
                
                // Determine log type and styling
                $logType = match (true) {
                    str_contains(strtolower($log->note), 'created') => 'created',
                    str_contains(strtolower($log->note), 'submitted') => 'submitted',
                    str_contains(strtolower($log->note), 'approved') => 'approved',
                    str_contains(strtolower($log->note), 'rejected') => 'rejected',
                    str_contains(strtolower($log->note), 'under review') => 'review',
                    str_contains(strtolower($log->note), 'document') => 'document',
                    default => 'default',
                };
                
                $statusLabels = [
                    'created' => 'Application Created',
                    'submitted' => 'Application Submitted',
                    'approved' => 'Application Approved',
                    'rejected' => 'Application Rejected',
                    'review' => 'Under Review',
                    'document' => 'Document Updated',
                    'default' => 'Application Updated',
                ];
            @endphp
            
            <div class="timeline-item">
                <div class="timeline-dot {{ $logType }}"></div>
                
                <div class="timeline-content">
                    <div class="timeline-header">
                        <h3 class="timeline-title">{{ $statusLabels[$logType] }}</h3>
                        <span class="timeline-badge {{ $logType }}">{{ $statusLabels[$logType] }}</span>
                    </div>
                    
                    <div class="timeline-meta">
                        <div class="timeline-user">
                            <div class="timeline-user-avatar">{{ $userInitials }}</div>
                            <div class="timeline-user-info">
                                <div class="timeline-user-name">{{ $userName }}</div>
                                <div class="timeline-user-role">{{ $userRole }}</div>
                            </div>
                        </div>
                        
                        <div class="timeline-time">
                            <div class="timeline-date">{{ $timestamp }}</div>
                            <div class="timeline-ago">{{ $timeAgo }}</div>
                        </div>
                    </div>
                    
                    <p class="timeline-description">{{ $log->note }}</p>
                    
                    @if($log->status_change)
                        <div class="timeline-status-change {{ $logType }}">
                            <div class="timeline-status-label">Status Changed</div>
                            <div class="timeline-status-value">{{ Str::of($log->status_change)->headline() }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
