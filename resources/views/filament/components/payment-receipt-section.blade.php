<div style="background: linear-gradient(135deg, #d1fae5 0%, #6ee7b7 100%); border: 3px solid #10b981; border-radius: 16px; padding: 24px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.3);">
    <!-- Header with Icon -->
    <div style="display: flex; align-items: center; margin-bottom: 20px;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #059669 0%, #10b981 100%); display: flex; align-items: center; justify-content: center; margin-right: 16px; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.4);">
            <span style="font-size: 28px;">💵</span>
        </div>
        <div>
            <h3 style="font-size: 22px; font-weight: 800; color: #065f46; margin: 0; line-height: 1.2;">Payment Receipt Uploaded</h3>
            <p style="font-size: 14px; color: #047857; margin: 4px 0 0 0;">Agent has submitted proof of payment for review</p>
        </div>
    </div>

    <!-- Receipt Details Card -->
    <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 14px;">
            <div>
                <p style="color: #6b7280; margin: 0 0 4px 0; font-weight: 500;">📄 File Name</p>
                <p style="color: #111827; font-weight: 600; margin: 0;">{{ $receiptDoc->original_filename }}</p>
            </div>
            <div>
                <p style="color: #6b7280; margin: 0 0 4px 0; font-weight: 500;">📦 File Size</p>
                <p style="color: #111827; font-weight: 600; margin: 0;">{{ number_format($receiptDoc->file_size / 1024, 2) }} KB</p>
            </div>
            <div>
                <p style="color: #6b7280; margin: 0 0 4px 0; font-weight: 500;">👤 Uploaded By</p>
                <p style="color: #111827; font-weight: 600; margin: 0;">{{ $receiptDoc->uploadedBy->name ?? 'Unknown' }}</p>
            </div>
            <div>
                <p style="color: #6b7280; margin: 0 0 4px 0; font-weight: 500;">📅 Upload Date</p>
                <p style="color: #111827; font-weight: 600; margin: 0;">{{ $receiptDoc->created_at->format('M j, Y g:i A') }}</p>
            </div>
        </div>
    </div>

    <!-- Download Button -->
    <a href="{{ Storage::disk('public')->url($receiptDoc->path) }}" 
       target="_blank"
       style="display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 16px 24px; background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: white; font-weight: 700; font-size: 16px; border-radius: 12px; text-decoration: none; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.4); transition: all 0.3s ease; border: none;"
       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(5, 150, 105, 0.5)'"
       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(5, 150, 105, 0.4)'">
        <span style="margin-right: 8px; font-size: 20px;">⬇️</span>
        Download Payment Receipt
    </a>

    <!-- Instructions -->
    <div style="margin-top: 16px; padding: 14px; background: rgba(59, 130, 246, 0.1); border: 2px solid #3b82f6; border-radius: 10px;">
        <p style="margin: 0; font-size: 13px; color: #1e40af; line-height: 1.6;">
            <strong style="color: #1e3a8a;">⚡ Next Step:</strong> Download and review the payment receipt. If payment is verified, use the status action buttons in the page header to move the application forward.
        </p>
    </div>
</div>

