<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

class PayoutReceiptController extends Controller
{
    public function download(Payout $payout): Response
    {
        // Security: Ensure agent can only download their own receipts
        if ($payout->agent_id !== auth()->id()) {
            abort(403, 'Unauthorized access to payout receipt.');
        }

        // Only allow downloads for finalized payouts
        if (!in_array($payout->status, ['paid', 'rejected'])) {
            abort(403, 'Receipt not available for pending payouts.');
        }

        // Generate PDF content
        $html = $this->generateReceiptHtml($payout);
        
        // Configure Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', false);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Generate filename
        $filename = sprintf(
            'payout-receipt-%s-%s.pdf',
            $payout->id,
            $payout->created_at->format('Y-m-d')
        );

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function generateReceiptHtml(Payout $payout): string
    {
        $statusColor = match($payout->status) {
            'paid' => '#10b981',
            'rejected' => '#ef4444',
            default => '#6b7280',
        };

        $statusLabel = match($payout->status) {
            'paid' => 'PAID',
            'rejected' => 'REJECTED',
            default => strtoupper($payout->status),
        };

        $receiptNumber = 'SKY-' . str_pad($payout->id, 4, '0', STR_PAD_LEFT);

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Payout Receipt - {$receiptNumber}</title>
            <style>
                @page { margin: 20mm; size: A4; }
                body { 
                    font-family: Arial, sans-serif; 
                    font-size: 14px;
                    color: #333; 
                    line-height: 1.4;
                    margin: 0;
                    padding: 0;
                }
                
                .header {
                    text-align: center;
                    border-bottom: 2px solid #667eea;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                
                .company-name {
                    font-size: 24px;
                    font-weight: bold;
                    color: #667eea;
                    margin-bottom: 5px;
                }
                
                .receipt-title {
                    font-size: 18px;
                    color: #666;
                    margin: 0;
                }
                
                .receipt-info {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 30px;
                    font-size: 13px;
                }
                
                .info-label {
                    font-weight: bold;
                    color: #444;
                }
                
                .main-content {
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 8px;
                    padding: 25px;
                    margin-bottom: 25px;
                }
                
                .amount-section {
                    text-align: center;
                    background: white;
                    border: 2px solid {$statusColor};
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                }
                
                .amount {
                    font-size: 32px;
                    font-weight: bold;
                    color: #333;
                    margin: 0;
                }
                
                .status-badge {
                    display: inline-block;
                    background: {$statusColor};
                    color: white;
                    padding: 6px 15px;
                    border-radius: 15px;
                    font-weight: bold;
                    font-size: 12px;
                    margin-top: 10px;
                }
                
                .details-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 15px;
                    margin-top: 20px;
                }
                
                .detail-item {
                    padding: 10px 0;
                    border-bottom: 1px solid #e9ecef;
                }
                
                .footer {
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #dee2e6;
                    text-align: center;
                    font-size: 11px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <!-- Header -->
            <div class='header'>
                <div class='company-name'>Sky Education Services</div>
                <div class='receipt-title'>Payout Receipt</div>
            </div>
            
            <!-- Receipt Info -->
            <div class='receipt-info'>
                <div>
                    <div class='info-label'>Receipt #:</div>
                    <div>{$receiptNumber}</div>
                </div>
                <div>
                    <div class='info-label'>Generated:</div>
                    <div>" . now()->format('M j, Y g:i A') . "</div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class='main-content'>
                <div class='details-grid'>
                    <div class='detail-item'>
                        <div class='info-label'>Agent</div>
                        <div>{$payout->agent->name}</div>
                    </div>
                    <div class='detail-item'>
                        <div class='info-label'>Email</div>
                        <div>{$payout->agent->email}</div>
                    </div>
                    <div class='detail-item'>
                        <div class='info-label'>Requested</div>
                        <div>{$payout->created_at->format('M j, Y g:i A')}</div>
                    </div>
                    <div class='detail-item'>
                        <div class='info-label'>Processed</div>
                        <div>{$payout->updated_at->format('M j, Y g:i A')}</div>
                    </div>
                </div>
                
                <!-- Amount Highlight -->
                <div class='amount-section'>
                    <div class='amount'>\${$payout->amount}</div>
                    <div class='status-badge'>{$statusLabel}</div>
                </div>
                
                <p style='text-align: center; margin: 15px 0 0 0; color: #666; font-size: 13px;'>
                    " . ($payout->status === 'paid' ? 'This payout has been successfully processed.' : 'This payout request was not approved.') . "
                </p>
            </div>
            
            <!-- Footer -->
            <div class='footer'>
                <p><strong>Sky Education Services</strong> | Agent Financial Management</p>
                <p>Document ID: {$receiptNumber} | Generated: " . now()->format('M j, Y g:i A') . "</p>
                <p>© " . now()->year . " Sky Education Services. All rights reserved.</p>
            </div>
        </body>
        </html>
        ";
    }
}
