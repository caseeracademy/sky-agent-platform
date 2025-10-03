@props(['documents'])

<style>
    .document-card {
        margin-bottom: 1.25rem !important;
        padding: 1.25rem !important;
        border: 2px solid #e5e7eb !important;
        border-radius: 0.75rem !important;
        background: #ffffff !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        transition: all 0.2s ease !important;
    }
    
    .document-card:hover {
        border-color: #93c5fd !important;
        box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1) !important;
        transform: translateY(-1px) !important;
    }
    
    .document-icon-box {
        width: 60px !important;
        height: 60px !important;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%) !important;
        border-radius: 0.625rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 2rem !important;
        flex-shrink: 0 !important;
    }
    
    .document-content {
        flex: 1 !important;
        min-width: 0 !important;
        padding-left: 1rem !important;
    }
    
    .document-title {
        font-size: 1rem !important;
        font-weight: 700 !important;
        color: #111827 !important;
        margin-bottom: 0.375rem !important;
        line-height: 1.4 !important;
    }
    
    .document-filename {
        font-size: 0.8125rem !important;
        color: #6b7280 !important;
        margin-bottom: 0.625rem !important;
        word-break: break-all !important;
        font-weight: 500 !important;
    }
    
    .document-meta {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 0.75rem !important;
        align-items: center !important;
        margin-bottom: 0.5rem !important;
    }
    
    .document-meta-item {
        display: flex !important;
        align-items: center !important;
        gap: 0.375rem !important;
        font-size: 0.75rem !important;
        color: #6b7280 !important;
    }
    
    .document-meta-icon {
        width: 0.875rem !important;
        height: 0.875rem !important;
        color: #9ca3af !important;
    }
    
    .document-badges {
        display: flex !important;
        gap: 0.375rem !important;
        flex-wrap: wrap !important;
    }
    
    .document-badge {
        padding: 0.25rem 0.625rem !important;
        border-radius: 0.375rem !important;
        font-size: 0.6875rem !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.025em !important;
    }
    
    .badge-size {
        background: #f3f4f6 !important;
        color: #374151 !important;
    }
    
    .badge-type {
        background: #dbeafe !important;
        color: #1e40af !important;
    }
    
    .document-actions {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.5rem !important;
        flex-shrink: 0 !important;
    }
    
    .document-btn {
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
    
    .document-download-btn {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
        color: white !important;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2) !important;
    }
    
    .document-download-btn:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
        box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3) !important;
        transform: translateY(-1px) !important;
    }
    
    .btn-icon {
        width: 1rem !important;
        height: 1rem !important;
    }
    
    .documents-container {
        display: flex !important;
        flex-direction: column !important;
        gap: 1.25rem !important;
    }
    
    .document-separator {
        height: 1px !important;
        background: linear-gradient(90deg, transparent, #e5e7eb, transparent) !important;
        margin: 0.5rem 0 !important;
    }

    @media (max-width: 768px) {
        .document-card {
            flex-direction: column !important;
            gap: 1rem !important;
        }
        
        .document-actions {
            width: 100% !important;
        }
        
        .document-btn {
            width: 100% !important;
        }
    }
</style>

@if($documents->isEmpty())
    <div style="text-align: center; padding: 3rem 2rem; color: #9ca3af;">
        <svg style="width: 4rem; height: 4rem; margin: 0 auto 1rem; color: #d1d5db;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p style="font-size: 1rem; font-weight: 600; color: #6b7280; margin-bottom: 0.375rem;">No documents uploaded yet</p>
        <p style="font-size: 0.8125rem; color: #9ca3af;">Upload supporting documents using the form below</p>
    </div>
@else
    <div class="documents-container">
        @foreach($documents as $index => $document)
            <div class="document-card" style="display: flex; align-items: flex-start; gap: 1rem;">
                <div class="document-icon-box">
                    <span>{{ str_contains($document->mime_type ?? '', 'pdf') ? '📄' : '🖼️' }}</span>
                </div>
                
                <div class="document-content">
                    @if($document->title)
                        <h3 class="document-title">{{ $document->title }}</h3>
                    @endif
                    
                    <p class="document-filename">{{ $document->original_filename }}</p>
                    
                    <div class="document-meta">
                        <div class="document-meta-item">
                            <svg class="document-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>{{ $document->uploadedByUser->name ?? 'Unknown' }}</span>
                        </div>
                        
                        <span style="color: #d1d5db;">•</span>
                        
                        <div class="document-meta-item">
                            <svg class="document-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ $document->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                    </div>
                    
                    <div class="document-badges">
                        <span class="document-badge badge-size">{{ $document->formatted_file_size }}</span>
                        <span class="document-badge badge-type">{{ strtoupper(pathinfo($document->original_filename, PATHINFO_EXTENSION)) }}</span>
                    </div>
                </div>
                
                <div class="document-actions">
                    <a href="{{ $document->download_url }}" target="_blank" class="document-btn document-download-btn">
                        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download
                    </a>
                </div>
            </div>
            
            @if(!$loop->last)
                <div class="document-separator"></div>
            @endif
        @endforeach
    </div>
@endif





