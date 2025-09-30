@props(['documents', 'showReplace' => true, 'showUploadButton' => false, 'studentId' => null])

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
    
    .document-replace-btn {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        color: white !important;
        box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2) !important;
    }
    
    .document-replace-btn:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
        box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3) !important;
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
                    @php
                        $icon = '📄'; // Default
                        if (str_contains($document->mime_type ?? '', 'pdf')) {
                            $icon = '📄';
                        } elseif (str_contains($document->mime_type ?? '', 'image')) {
                            $icon = '🖼️';
                        } elseif (str_contains($document->mime_type ?? '', 'word') || str_contains($document->mime_type ?? '', 'document')) {
                            $icon = '📝';
                        } elseif (str_contains($document->mime_type ?? '', 'excel') || str_contains($document->mime_type ?? '', 'spreadsheet')) {
                            $icon = '📊';
                        }
                    @endphp
                    <span>{{ $icon }}</span>
                </div>
                
                <div class="document-content">
                    @if($document->name)
                        <h3 class="document-title">{{ $document->name }}</h3>
                    @endif
                    
                    <p class="document-filename">{{ $document->file_name ?? basename($document->file_path) }}</p>
                    
                    <div class="document-meta">
                        <div class="document-meta-item">
                            <svg class="document-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>{{ $document->uploadedBy->name ?? 'Unknown' }}</span>
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
                        <span class="document-badge badge-type">{{ strtoupper(pathinfo($document->file_name ?? basename($document->file_path), PATHINFO_EXTENSION)) }}</span>
                    </div>
                </div>
                
                <div class="document-actions">
                    <a href="{{ $document->download_url }}" target="_blank" class="document-btn document-download-btn">
                        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download
                    </a>
                    
                    @if($showReplace)
                        <button type="button" 
                                onclick="replaceDocument({{ $document->id }})"
                                class="document-btn document-replace-btn">
                            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Replace
                        </button>
                    @endif
                </div>
            </div>
            
            @if(!$loop->last)
                <div class="document-separator"></div>
            @endif
        @endforeach
    </div>
@endif

@if($showUploadButton)
<!-- Upload Button Section -->
<div style="margin-top: 2rem; padding: 1.5rem; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 0.75rem; text-align: center;">
    <div style="margin-bottom: 1rem;">
        <svg style="width: 3rem; height: 3rem; margin: 0 auto; color: #64748b;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
        </svg>
    </div>
    
    <h3 style="font-size: 1.125rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Upload New Document</h3>
    <p style="color: #6b7280; margin-bottom: 1.5rem; font-size: 0.875rem;">Add supporting documents for this student</p>
    
    <button onclick="openUploadModal({{ $studentId }})" 
            style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.5rem; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2); transition: all 0.2s ease;">
        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Upload Document
    </button>
    
    <p style="color: #9ca3af; margin-top: 0.75rem; font-size: 0.75rem;">Accepted: PDF, JPG, PNG, DOC, DOCX (Max 10MB)</p>
</div>

<!-- Upload Modal -->
<div id="uploadModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000; justify-content: center; align-items: center;" data-student-id="{{ $studentId }}">
    <div style="background: white; border-radius: 0.75rem; padding: 2rem; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 1rem;">
            <h3 id="uploadModalTitle" style="font-size: 1.25rem; font-weight: 600; color: #111827; margin: 0;">Upload New Document</h3>
            <button onclick="closeUploadModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280; margin-left: auto;">&times;</button>
        </div>
        
        <form id="uploadForm" enctype="multipart/form-data">
            <input type="hidden" id="student_id" name="student_id" value="{{ $studentId }}">
            <input type="hidden" id="replace_document_id" name="replace_document_id" value="">
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Document Name</label>
                <input type="text" id="document_name" name="document_name" 
                       placeholder="e.g., Passport Copy, Academic Transcript"
                       style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; box-sizing: border-box;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Document Type</label>
                <select id="document_type" name="document_type" 
                        style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; box-sizing: border-box;">
                    <option value="">Select document type</option>
                    <option value="passport">Passport</option>
                    <option value="certificate">Certificate</option>
                    <option value="transcript">Transcript</option>
                    <option value="photo">Photo</option>
                    <option value="visa">Visa Document</option>
                    <option value="language_test">Language Test Result</option>
                    <option value="other">Other Document</option>
                </select>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Upload File</label>
                <div style="border: 2px dashed #d1d5db; border-radius: 0.5rem; padding: 2rem; text-align: center; background: #f9fafb;">
                    <input type="file" id="document_file" name="document_file" 
                           accept="application/pdf,image/*,.doc,.docx"
                           style="display: none;">
                    <svg style="width: 3rem; height: 3rem; margin: 0 auto 1rem; color: #6b7280;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Drag & Drop your files or <span onclick="document.getElementById('document_file').click()" style="color: #3b82f6; cursor: pointer;">Browse</span></p>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.75rem; color: #9ca3af;">Accepted: PDF, Images, Word documents. Max size: 10MB</p>
                </div>
            </div>
            
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeUploadModal()" 
                        style="padding: 0.75rem 1.5rem; border: 1px solid #d1d5db; background: white; color: #374151; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" id="uploadSubmitButton"
                        style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">
                    Upload Document
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
function openUploadModal(studentId, documentId = null) {
    document.getElementById('student_id').value = studentId;
    
    // Get form fields
    const nameField = document.getElementById('document_name').closest('div');
    const typeField = document.getElementById('document_type').closest('div');
    
    if (documentId) {
        // Replace mode - hide name and type fields
        document.getElementById('replace_document_id').value = documentId;
        document.getElementById('uploadModalTitle').textContent = 'Replace Document';
        document.getElementById('uploadSubmitButton').textContent = 'Replace Document';
        
        // Hide name and type fields for replace
        nameField.style.display = 'none';
        typeField.style.display = 'none';
        
        // Clear the values
        document.getElementById('document_name').value = '';
        document.getElementById('document_type').value = '';
    } else {
        // Upload mode - show name and type fields
        document.getElementById('replace_document_id').value = '';
        document.getElementById('uploadModalTitle').textContent = 'Upload New Document';
        document.getElementById('uploadSubmitButton').textContent = 'Upload Document';
        
        // Show name and type fields for upload
        nameField.style.display = 'block';
        typeField.style.display = 'block';
    }
    
    document.getElementById('uploadModal').style.display = 'flex';
}

function closeUploadModal() {
    document.getElementById('uploadModal').style.display = 'none';
    document.getElementById('uploadForm').reset();
    document.getElementById('replace_document_id').value = '';
    
    // Reset form fields visibility
    const nameField = document.getElementById('document_name').closest('div');
    const typeField = document.getElementById('document_type').closest('div');
    nameField.style.display = 'block';
    typeField.style.display = 'block';
}

function replaceDocument(documentId) {
    const studentId = document.querySelector('[data-student-id]')?.getAttribute('data-student-id') || 
                     document.getElementById('student_id')?.value || 
                     window.location.pathname.split('/').pop();
    
    
    openUploadModal(studentId, documentId);
}

// Handle file upload
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    
    submitButton.textContent = 'Uploading...';
    submitButton.disabled = true;
    
    try {
        const replaceDocumentId = formData.get('replace_document_id');
        const studentId = formData.get('student_id');
        let url = '/agent/students/' + studentId + '/documents';
        
        // For replace mode, only send the file
        if (replaceDocumentId) {
            url += '/' + replaceDocumentId + '/replace';
            // Remove name and type from form data for replace
            formData.delete('document_name');
            formData.delete('document_type');
        }
        
        const response = await fetch(url, {
            method: replaceDocumentId ? 'PUT' : 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (response.ok) {
            closeUploadModal();
            location.reload(); // Refresh the page to show the new/replaced document
        } else {
            const errorText = replaceDocumentId ? 'replacing' : 'uploading';
            alert('Error ' + errorText + ' document. Please try again.');
        }
    } catch (error) {
        const errorText = formData.get('replace_document_id') ? 'replacing' : 'uploading';
        alert('Error ' + errorText + ' document. Please try again.');
    } finally {
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    }
});

// Handle file input change
document.getElementById('document_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileName = file.name;
        const fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        console.log('Selected file:', fileName, 'Size:', fileSize);
    }
});
</script>
