@props(['applications'])

<style>
    .application-card {
        margin-bottom: 1.25rem !important;
        padding: 1.25rem !important;
        border: 2px solid #e5e7eb !important;
        border-radius: 0.75rem !important;
        background: #ffffff !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        transition: all 0.2s ease !important;
    }
    
    .application-card:hover {
        border-color: #93c5fd !important;
        box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1) !important;
        transform: translateY(-1px) !important;
    }
    
    .application-icon-box {
        width: 60px !important;
        height: 60px !important;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%) !important;
        border-radius: 0.625rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 2rem !important;
        flex-shrink: 0 !important;
    }
    
    .application-content {
        flex: 1 !important;
        min-width: 0 !important;
        padding-left: 1rem !important;
    }
    
    .application-title {
        font-size: 1rem !important;
        font-weight: 700 !important;
        color: #111827 !important;
        margin-bottom: 0.375rem !important;
        line-height: 1.4 !important;
    }
    
    .application-subtitle {
        font-size: 0.8125rem !important;
        color: #6b7280 !important;
        margin-bottom: 0.625rem !important;
        word-break: break-all !important;
        font-weight: 500 !important;
    }
    
    .application-meta {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 0.75rem !important;
        align-items: center !important;
        margin-bottom: 0.5rem !important;
    }
    
    .application-meta-item {
        display: flex !important;
        align-items: center !important;
        gap: 0.375rem !important;
        font-size: 0.75rem !important;
        color: #6b7280 !important;
    }
    
    .application-meta-icon {
        width: 0.875rem !important;
        height: 0.875rem !important;
        color: #9ca3af !important;
    }
    
    .application-badges {
        display: flex !important;
        gap: 0.375rem !important;
        flex-wrap: wrap !important;
    }
    
    .application-badge {
        padding: 0.25rem 0.625rem !important;
        border-radius: 0.375rem !important;
        font-size: 0.6875rem !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.025em !important;
    }
    
    .badge-status {
        background: #f3f4f6 !important;
        color: #374151 !important;
    }
    
    .badge-commission {
        background: #dcfce7 !important;
        color: #166534 !important;
    }
    
    .badge-number {
        background: #e0f2fe !important;
        color: #0c4a6e !important;
    }
    
    .application-actions {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.5rem !important;
        flex-shrink: 0 !important;
    }
    
    .application-btn {
        padding: 0.625rem 1.25rem !important;
        border-radius: 0.5rem !important;
        font-weight: 600 !important;
        font-size: 0.8125rem !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.375rem !important;
        transition: all 0.2s ease !important;
        text-decoration: none !important;
        white-space: nowrap !important;
        border: none !important;
        cursor: pointer !important;
    }
    
    .application-details-btn {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
        color: white !important;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2) !important;
    }
    
    .application-details-btn:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
        box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3) !important;
        transform: translateY(-1px) !important;
    }
    
    .btn-icon {
        width: 1rem !important;
        height: 1rem !important;
    }
    
    .applications-container {
        display: flex !important;
        flex-direction: column !important;
        gap: 1.25rem !important;
    }
    
    .application-separator {
        height: 1px !important;
        background: linear-gradient(90deg, transparent, #e5e7eb, transparent) !important;
        margin: 0.5rem 0 !important;
    }

    @media (max-width: 768px) {
        .application-card {
            flex-direction: column !important;
            gap: 1rem !important;
        }
        
        .application-actions {
            width: 100% !important;
        }
        
        .application-btn {
            width: 100% !important;
        }
    }
</style>

@if($applications->isEmpty())
    <div style="text-align: center; padding: 3rem 2rem; color: #9ca3af;">
        <svg style="width: 4rem; height: 4rem; margin: 0 auto 1rem; color: #d1d5db;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p style="font-size: 1rem; font-weight: 600; color: #6b7280; margin-bottom: 0.375rem;">No applications submitted yet</p>
        <p style="font-size: 0.8125rem; color: #9ca3af;">Applications will appear here when students are created with university and program selection</p>
    </div>
@else
    <div class="applications-container">
        @foreach($applications as $index => $application)
            <div class="application-card" style="display: flex; align-items: flex-start; gap: 1rem;">
                <div class="application-icon-box">
                    <span>🎓</span>
                </div>
                
                <div class="application-content">
                    <h3 class="application-title">{{ $application->program->name ?? 'Unknown Program' }}</h3>
                    
                    <p class="application-subtitle">{{ $application->program->university->name ?? 'Unknown University' }}</p>
                    
                    <div class="application-meta">
                        <div class="application-meta-item">
                            <svg class="application-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ $application->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                        
                        <span style="color: #d1d5db;">•</span>
                        
                        <div class="application-meta-item">
                            <svg class="application-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>{{ $application->agent->name ?? 'Unknown Agent' }}</span>
                        </div>
                    </div>
                    
                    <div class="application-badges">
                        @php
                            $statusColors = [
                                'pending' => ['bg' => '#fef3c7', 'color' => '#92400e'],
                                'submitted' => ['bg' => '#dbeafe', 'color' => '#1e40af'],
                                'under_review' => ['bg' => '#fef3c7', 'color' => '#92400e'],
                                'additional_documents_required' => ['bg' => '#fecaca', 'color' => '#991b1b'],
                                'approved' => ['bg' => '#dcfce7', 'color' => '#166534'],
                                'rejected' => ['bg' => '#fecaca', 'color' => '#991b1b'],
                                'enrolled' => ['bg' => '#dcfce7', 'color' => '#166534'],
                                'cancelled' => ['bg' => '#f3f4f6', 'color' => '#374151'],
                            ];
                            $statusColor = $statusColors[$application->status] ?? $statusColors['pending'];
                            $statusLabel = ucfirst(str_replace('_', ' ', $application->status));
                        @endphp
                        
                        <span class="application-badge" style="background: {{ $statusColor['bg'] }} !important; color: {{ $statusColor['color'] }} !important;">
                            {{ $statusLabel }}
                        </span>
                        
                        @if($application->commission_amount)
                            <span class="application-badge badge-commission">
                                ${{ number_format($application->commission_amount, 2) }}
                            </span>
                        @endif
                        
                        <span class="application-badge badge-number">
                            {{ $application->application_number }}
                        </span>
                    </div>
                </div>
                
                <div class="application-actions">
                    <a href="{{ route('filament.agent.resources.applications.view', $application->id) }}" 
                       class="application-btn application-details-btn">
                        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Application Details
                    </a>
                </div>
            </div>
            
            @if(!$loop->last)
                <div class="application-separator"></div>
            @endif
        @endforeach
    </div>
@endif
