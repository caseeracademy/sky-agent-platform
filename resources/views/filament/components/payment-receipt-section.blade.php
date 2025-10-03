<div class="rounded-xl overflow-hidden shadow-lg border-2 border-green-400" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);">
    <div class="p-6">
        <!-- Header -->
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 rounded-full bg-green-600 flex items-center justify-center mr-4">
                <span class="text-2xl">💵</span>
            </div>
            <div>
                <h3 class="text-xl font-bold text-green-900">Payment Receipt Uploaded</h3>
                <p class="text-sm text-green-700">Agent has submitted proof of payment</p>
            </div>
        </div>

        <!-- Receipt Info -->
        <div class="bg-white rounded-lg p-4 mb-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">File Name</p>
                    <p class="font-semibold text-gray-900">{{ $receiptDoc->original_filename }}</p>
                </div>
                <div>
                    <p class="text-gray-500">File Size</p>
                    <p class="font-semibold text-gray-900">{{ number_format($receiptDoc->file_size / 1024, 2) }} KB</p>
                </div>
                <div>
                    <p class="text-gray-500">Uploaded By</p>
                    <p class="font-semibold text-gray-900">{{ $receiptDoc->uploadedBy->name ?? 'Unknown' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Upload Date</p>
                    <p class="font-semibold text-gray-900">{{ $receiptDoc->created_at->format('M j, Y g:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Download Button -->
        <a href="{{ Storage::disk('public')->url($receiptDoc->path) }}" 
           target="_blank"
           class="inline-flex items-center justify-center w-full px-6 py-4 text-white font-bold rounded-lg transition-all duration-200"
           style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)'"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)'">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Download Payment Receipt
        </a>

        <!-- Next Steps -->
        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-900">
                <strong>⚡ Next Step:</strong> Review the payment receipt above. If payment is verified, use the status action buttons in the page header to approve the application.
            </p>
        </div>
    </div>
</div>

