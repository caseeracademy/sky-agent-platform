@php
    $application = $application ?? null;
    $userRole = auth()->user()->role ?? 'guest';
    
    if (!$application) {
        return;
    }
    
    $statusService = app(\App\Services\ApplicationStatusService::class);
    $availableActions = $statusService->getAvailableActions($application, $userRole);
    $allStatuses = \App\Services\ApplicationStatusService::getAllStatuses();
    $currentStatusInfo = $allStatuses[$application->status] ?? ['label' => $application->status, 'color' => 'gray'];
    
    // Generate unique ID for this component instance
    $componentId = 'status-actions-' . $application->id;
@endphp

<style>
.status-actions-container {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border: 2px solid #e2e8f0;
    border-radius: 20px;
    padding: 2rem;
    margin: 1.5rem 0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Modal Styles */
[x-cloak] { display: none !important; }

.modal-backdrop {
    backdrop-filter: blur(4px);
}

.dark .status-actions-container {
    background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
    border-color: #334155;
}

.current-status-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #f1f5f9;
}

.current-status-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 0.75rem;
}

.current-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    font-size: 1.125rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.status-warning { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #92400e; border: 2px solid #f59e0b; }
.status-info { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1e40af; border: 2px solid #3b82f6; }
.status-gray { background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); color: #475569; border: 2px solid #94a3b8; }
.status-purple { background: linear-gradient(135deg, #e9d5ff 0%, #d8b4fe 100%); color: #6b21a8; border: 2px solid #a855f7; }
.status-indigo { background: linear-gradient(135deg, #c7d2fe 0%, #a5b4fc 100%); color: #3730a3; border: 2px solid #6366f1; }
.status-teal { background: linear-gradient(135deg, #ccfbf1 0%, #99f6e4 100%); color: #115e59; border: 2px solid #14b8a6; }
.status-amber { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #92400e; border: 2px solid #f59e0b; }
.status-lime { background: linear-gradient(135deg, #ecfccb 0%, #d9f99d 100%); color: #3f6212; border: 2px solid #84cc16; }
.status-orange { background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%); color: #9a3412; border: 2px solid #f97316; }
.status-success { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #166534; border: 2px solid #10b981; }
.status-danger { background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%); color: #991b1b; border: 2px solid #ef4444; }

.actions-title {
    font-size: 1rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 1rem;
    text-align: center;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.action-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.action-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s ease;
}

.action-button:hover::before {
    left: 100%;
}

.action-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.2);
}

.action-button:active {
    transform: translateY(0);
}

.action-primary { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; border-color: #2563eb; }
.action-primary:hover { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); }

.action-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-color: #059669; }
.action-success:hover { background: linear-gradient(135deg, #059669 0%, #047857 100%); }

.action-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border-color: #dc2626; }
.action-danger:hover { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); }

.action-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border-color: #d97706; }
.action-warning:hover { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); }

.action-info { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white; border-color: #0891b2; }
.action-info:hover { background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%); }

.action-gray { background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); color: white; border-color: #6b7280; }
.action-gray:hover { background: linear-gradient(135deg, #4b5563 0%, #374151 100%); }

.action-icon {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
}

.no-actions-message {
    text-align: center;
    padding: 2rem;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 2px dashed #bae6fd;
    border-radius: 12px;
    color: #0c4a6e;
    font-weight: 500;
}

@media (max-width: 768px) {
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .status-actions-container {
        padding: 1.5rem;
    }
}

.dark .status-actions-container {
    background: linear-gradient(145deg, #1e293b 0%, #334155 100%);
    border-color: #475569;
}

.dark .current-status-header {
    border-bottom-color: #475569;
}

.dark .current-status-label {
    color: #94a3b8;
}

.dark .actions-title {
    color: #e2e8f0;
}

.dark .no-actions-message {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    border-color: #475569;
    color: #7dd3fc;
}
</style>

<div x-data="{ 
    showModal: false, 
    showConfirmModal: false,
    modalStatus: '', 
    confirmStatus: '',
    modalNote: '',
    statusLabels: {
        'additional_documents_needed': 'Request Additional Documents',
        'rejected': 'Reject Application'
    },
    openModal(status) {
        console.log('Opening modal for status:', status);
        this.modalStatus = status;
        this.modalNote = '';
        this.showModal = true;
    },
    openConfirmModal(status) {
        console.log('Opening confirm modal for status:', status);
        this.confirmStatus = status;
        this.showConfirmModal = true;
    },
    closeModal() {
        this.showModal = false;
        this.modalNote = '';
    },
    closeConfirmModal() {
        this.showConfirmModal = false;
    },
    submitModal() {
        console.log('Submitting:', this.modalStatus, this.modalNote);
        $wire.changeApplicationStatusWithNote(this.modalStatus, this.modalNote);
        this.closeModal();
    },
    submitConfirm() {
        console.log('Confirming:', this.confirmStatus);
        $wire.changeApplicationStatus(this.confirmStatus);
        this.closeConfirmModal();
    }
}" class="status-actions-container">
    <div class="current-status-header">
        <div class="current-status-label">Current Application Status</div>
        <div class="current-status-badge status-{{ $currentStatusInfo['color'] }}">
            {{ $currentStatusInfo['label'] }}
        </div>
    </div>
    
    @if(count($availableActions) > 0)
        <div class="actions-title">Available Actions</div>
        <div class="actions-grid">
            @foreach($availableActions as $action)
                <button 
                    @if($action['requires_input'] || $action['status'] === 'additional_documents_needed')
                        @click="openModal('{{ $action['status'] }}')"
                    @elseif($action['requires_confirmation'] || $action['status'] === 'approved')
                        @click="openConfirmModal('{{ $action['status'] }}')"
                    @else
                        wire:click="changeApplicationStatus('{{ $action['status'] }}')"
                    @endif
                    class="action-button action-{{ $action['color'] }}"
                    type="button"
                >
                    <svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ $action['label'] }}</span>
                </button>
            @endforeach
        </div>
    @else
        <div class="no-actions-message">
            @if($application->status === 'approved')
                🎉 Application Approved! No further actions needed.
            @elseif($application->status === 'rejected')
                ❌ Application Rejected. This is a final status.
            @else
                No actions available for your role.
            @endif
        </div>
    @endif

    {{-- Modal for status changes that require input --}}
    <div x-show="showModal"
         @keydown.escape.window="closeModal()"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[9999] flex items-center justify-center"
         style="display: none;">
        
        {{-- Backdrop --}}
        <div class="modal-backdrop fixed inset-0 bg-black/50" @click="closeModal()"></div>
        
        {{-- Modal Content --}}
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full mx-4 p-8 z-10"
             @click.stop
             x-transition:enter="transition ease-out duration-300 delay-100"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95">
            
            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100" x-text="statusLabels[modalStatus] || 'Update Status'"></h3>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            {{-- Body --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                    <span x-show="modalStatus === 'additional_documents_needed'">
                        📝 Describe which documents are needed and why:
                    </span>
                    <span x-show="modalStatus !== 'additional_documents_needed'">
                        💬 Add a note (optional):
                    </span>
                </label>
                <textarea 
                    x-model="modalNote"
                    rows="6"
                    class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 transition-all"
                    placeholder="Enter details here..."
                ></textarea>
                
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400" x-show="modalStatus === 'additional_documents_needed'">
                    💡 <strong>Tip:</strong> Be specific about what documents are needed. The agent will see this message and can upload the documents directly.
                </p>
            </div>
            
            {{-- Footer --}}
            <div class="flex gap-3 justify-end">
                <button 
                    type="button"
                    @click="closeModal()"
                    class="px-6 py-3 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                    Cancel
                </button>
                <button 
                    type="button"
                    @click="submitModal()"
                    class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-semibold hover:from-blue-600 hover:to-blue-700 shadow-lg hover:shadow-xl transition-all"
                >
                    Confirm
                </button>
            </div>
        </div>
    </div>

    {{-- Confirmation Modal for critical actions --}}
    <div x-show="showConfirmModal"
         @keydown.escape.window="closeConfirmModal()"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[9999] flex items-center justify-center"
         style="display: none;">
        
        {{-- Backdrop --}}
        <div class="modal-backdrop fixed inset-0 bg-black/50" @click="closeConfirmModal()"></div>
        
        {{-- Modal Content --}}
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full mx-4 p-8 z-10"
             @click.stop
             x-transition:enter="transition ease-out duration-300 delay-100"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95">
            
            {{-- Icon --}}
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 bg-yellow-100 rounded-full">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            
            {{-- Header --}}
            <div class="text-center mb-6">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">⚠️ Confirm Action</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    This action cannot be undone. Are you sure you want to proceed?
                </p>
            </div>
            
            {{-- Footer --}}
            <div class="flex gap-3">
                <button 
                    type="button"
                    @click="closeConfirmModal()"
                    class="flex-1 px-6 py-3 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                    Cancel
                </button>
                <button 
                    type="button"
                    @click="submitConfirm()"
                    class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl font-semibold hover:from-red-600 hover:to-red-700 shadow-lg hover:shadow-xl transition-all"
                >
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

