@php
    $offerLetterDoc = $application->applicationDocuments()
        ->where(function($query) {
            $query->where('title', 'LIKE', '%offer%letter%')
                  ->orWhere('title', 'LIKE', '%Offer%Letter%')
                  ->orWhere('path', 'LIKE', '%offer-letter%')
                  ->orWhere('path', 'LIKE', '%offer-letters%');
        })
        ->latest()
        ->first();
@endphp

@if(!$offerLetterDoc)
    <div class="text-gray-600 dark:text-gray-400">
        <p>The offer letter is being processed by the admin. Please check back soon.</p>
    </div>
@else
    @php
        $downloadUrl = \Storage::disk('public')->url($offerLetterDoc->path);
    @endphp
    
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 2rem; color: white; margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
            <div style="width: 64px; height: 64px; background: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                🎓
            </div>
            <div>
                <h3 style="font-size: 1.5rem; font-weight: bold; margin: 0; color: white;">Congratulations!</h3>
                <p style="margin: 0.5rem 0 0 0; opacity: 0.9; color: white;">The student has received an offer letter from the university!</p>
            </div>
        </div>
        
        <div style="background: rgba(255,255,255,0.1); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; backdrop-filter: blur(10px);">
            <p style="margin: 0 0 1rem 0; font-weight: 600; color: white;">📄 Offer Letter Details:</p>
            <p style="margin: 0 0 0.5rem 0; color: white;"><strong>File:</strong> {{ $offerLetterDoc->original_filename }}</p>
            <p style="margin: 0 0 0.5rem 0; color: white;"><strong>Uploaded:</strong> {{ $offerLetterDoc->created_at->format('M j, Y g:i A') }}</p>
            <p style="margin: 0; color: white;"><strong>Size:</strong> {{ number_format($offerLetterDoc->file_size / 1024, 2) }} KB</p>
        </div>
        
        <div style="background: rgba(255,255,255,0.1); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <p style="margin: 0 0 1rem 0; font-weight: 600; color: white;">✅ Next Steps:</p>
            <ol style="margin: 0; padding-left: 1.5rem; color: white;">
                <li style="margin-bottom: 0.5rem;">Download the offer letter below</li>
                <li style="margin-bottom: 0.5rem;">Send it to the student for review</li>
                <li style="margin-bottom: 0.5rem;">Once student pays, upload payment proof using the button in the page header</li>
            </ol>
        </div>
        
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="{{ $downloadUrl }}" target="_blank" download class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom bg-white text-gray-800 hover:bg-gray-100 focus-visible:ring-white/50 dark:bg-white dark:hover:bg-gray-100 fi-ac-btn-action" style="display: inline-flex; gap: 0.5rem; padding: 0.75rem 1.5rem; text-decoration: none;">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Download Offer Letter</span>
            </a>
            
            <span style="display: inline-flex; gap: 0.5rem; padding: 0.75rem 1.5rem; background: rgba(255,255,255,0.2); border-radius: 8px; color: white; font-size: 0.875rem; align-items: center;">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>After student pays, use "Student Paid - Upload Receipt" button in the page header</span>
            </span>
        </div>
    </div>
@endif

