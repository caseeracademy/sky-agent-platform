@props(['request'])

<div class="warning-panel">
    <style>
        .warning-panel {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #f59e0b;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(245, 158, 11, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .warning-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #d97706, #f59e0b);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .warning-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .warning-icon {
            width: 2.5rem;
            height: 2.5rem;
            background: #f59e0b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            flex-shrink: 0;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .warning-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #92400e;
            margin: 0;
        }
        
        .warning-subtitle {
            font-size: 0.875rem;
            color: #a16207;
            margin: 0;
        }
        
        .warning-content {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 0.5rem;
            padding: 1rem;
            border-left: 4px solid #f59e0b;
            margin-bottom: 1rem;
        }
        
        .warning-description {
            font-size: 0.875rem;
            color: #92400e;
            line-height: 1.6;
            margin: 0;
            font-weight: 500;
        }
        
        .warning-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }
        
        .upload-btn {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
            font-family: inherit;
        }
        
        .upload-btn:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(245, 158, 11, 0.4);
        }
        
        .upload-btn svg {
            width: 1rem;
            height: 1rem;
        }
        
        .view-documents-btn {
            background: transparent;
            color: #92400e;
            border: 2px solid #f59e0b;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            font-family: inherit;
        }
        
        .view-documents-btn:hover {
            background: #f59e0b;
            color: white;
            transform: translateY(-1px);
        }
        
        .view-documents-btn svg {
            width: 1rem;
            height: 1rem;
        }
        
        .priority-badge {
            background: #dc2626;
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            animation: blink 1.5s infinite;
        }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.7; }
        }
    </style>

    <div class="warning-header">
        <div class="warning-icon">
            ⚠️
        </div>
        <div>
            <h3 class="warning-title">Please Upload Missing Documents</h3>
            <p class="warning-subtitle">Admin has requested additional documents for this application</p>
        </div>
        <div class="priority-badge">Priority</div>
    </div>
    
    <div class="warning-content">
        <p class="warning-description">{{ $request }}</p>
    </div>
    
    <div class="warning-actions">
        <button onclick="alert('Upload functionality temporarily disabled - see BUGS_TO_SOLVE.md')" class="upload-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            Upload Documents
        </button>
        
        <button onclick="switchToDocumentTab()" class="view-documents-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            View Documents
        </button>
    </div>
</div>

<script>
    function switchToDocumentTab() {
        const tabs = document.querySelectorAll('[role="tab"]');
        tabs.forEach(tab => {
            if (tab.textContent.trim() === 'Document Review') {
                tab.click();
                return;
            }
        });
    }
</script>