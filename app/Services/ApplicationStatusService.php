<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApplicationStatusService
{
    /**
     * Define allowed status transitions.
     * Key = current status, Value = array of allowed next statuses
     */
    private const TRANSITIONS = [
        'needs_review' => ['submitted', 'rejected'],
        'submitted' => ['additional_documents_needed', 'applied', 'rejected'],
        'additional_documents_needed' => ['submitted', 'rejected'],
        'applied' => ['offer_received', 'rejected'],
        'offer_received' => ['payment_approval', 'rejected'],
        'payment_approval' => ['ready_for_approval', 'rejected'],
        'ready_for_approval' => ['approved', 'payment_approval', 'rejected'],
        'approved' => [], // FINAL - no transitions allowed
        'rejected' => [], // FINAL - no transitions allowed
    ];

    /**
     * Define which roles can make which transitions.
     */
    private const ROLE_PERMISSIONS = [
        'super_admin' => 'all', // Can do everything except reverse final statuses
        'admin_staff' => 'all', // Same as super_admin
        'agent_owner' => [
            'additional_documents_needed' => ['submitted'],
            'offer_received' => ['payment_approval'], // Agent uploads payment proof directly
        ],
        'agent_staff' => [
            'additional_documents_needed' => ['submitted'],
            'offer_received' => ['payment_approval'], // Agent uploads payment proof directly
        ],
    ];

    /**
     * Check if a status transition is allowed.
     */
    public function canTransitionTo(Application $application, string $newStatus, ?string $userRole = null): bool
    {
        $currentStatus = $application->status;
        $userRole = $userRole ?? Auth::user()->role ?? 'guest';

        // Final statuses cannot be changed
        if ($currentStatus === 'approved' || $currentStatus === 'rejected') {
            return false;
        }

        // Check if transition is in allowed list
        $allowedTransitions = self::TRANSITIONS[$currentStatus] ?? [];
        if (! in_array($newStatus, $allowedTransitions)) {
            return false;
        }

        // Check role permissions
        if ($userRole === 'super_admin' || $userRole === 'admin_staff') {
            return true; // Admins can do all allowed transitions
        }

        // Check agent permissions
        $agentPermissions = self::ROLE_PERMISSIONS[$userRole] ?? [];
        if (isset($agentPermissions[$currentStatus]) && in_array($newStatus, $agentPermissions[$currentStatus])) {
            return true;
        }

        return false;
    }

    /**
     * Transition application to new status with history logging.
     */
    public function transitionTo(
        Application $application,
        string $newStatus,
        ?string $reason = null,
        ?array $metadata = null
    ): bool {
        $oldStatus = $application->status;
        $userId = Auth::id();

        // Validate transition
        if (! $this->canTransitionTo($application, $newStatus)) {
            Log::warning('Invalid status transition attempted', [
                'application_id' => $application->id,
                'from' => $oldStatus,
                'to' => $newStatus,
                'user_id' => $userId,
            ]);

            return false;
        }

        try {
            // Update application status
            $application->status = $newStatus;

            // Handle status-specific updates
            $this->handleStatusSpecificUpdates($application, $newStatus, $userId);

            $application->save();

            // Log status change to history
            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by_user_id' => $userId,
                'reason' => $reason,
                'metadata' => $metadata,
            ]);

            Log::info('Application status transitioned', [
                'application_id' => $application->id,
                'application_number' => $application->application_number,
                'from' => $oldStatus,
                'to' => $newStatus,
                'user_id' => $userId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to transition application status', [
                'application_id' => $application->id,
                'from' => $oldStatus,
                'to' => $newStatus,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handle status-specific field updates.
     */
    private function handleStatusSpecificUpdates(Application $application, string $newStatus, int $userId): void
    {
        switch ($newStatus) {
            case 'rejected':
                $application->rejected_at = now();
                $application->rejected_by = $userId;
                break;

            case 'payment_approval':
                $application->payment_receipt_uploaded_at = now();
                $application->payment_receipt_uploaded_by = $userId;
                break;

            case 'ready_for_approval':
                $application->payment_verified_at = now();
                $application->payment_verified_by = $userId;
                break;

            case 'offer_received':
                $application->university_response_date = now();
                break;
        }
    }

    /**
     * Get available actions for current status and user role.
     */
    public function getAvailableActions(Application $application, string $userRole): array
    {
        $currentStatus = $application->status;
        $allowedTransitions = self::TRANSITIONS[$currentStatus] ?? [];

        $actions = [];

        foreach ($allowedTransitions as $targetStatus) {
            if ($this->canTransitionTo($application, $targetStatus, $userRole)) {
                $actions[] = [
                    'status' => $targetStatus,
                    'label' => $this->getActionLabel($currentStatus, $targetStatus),
                    'color' => $this->getActionColor($targetStatus),
                    'icon' => $this->getActionIcon($targetStatus),
                    'requires_confirmation' => $this->requiresConfirmation($targetStatus),
                    'requires_input' => $this->requiresInput($currentStatus, $targetStatus),
                ];
            }
        }

        return $actions;
    }

    /**
     * Get human-readable label for action button.
     */
    private function getActionLabel(string $fromStatus, string $toStatus): string
    {
        return match ($toStatus) {
            'submitted' => $fromStatus === 'additional_documents_needed' ? 'Documents Uploaded - Resubmit' : 'Mark as Submitted',
            'additional_documents_needed' => 'Request Additional Documents',
            'applied' => 'Apply to University',
            'offer_received' => 'Offer Letter Received',
            'payment_approval' => 'Verify Payment',
            'ready_for_approval' => 'Ready for Final Approval',
            'approved' => '🔒 FINAL APPROVE',
            'rejected' => 'Reject Application',
            default => ucwords(str_replace('_', ' ', $toStatus)),
        };
    }

    /**
     * Get color for action button.
     */
    private function getActionColor(string $status): string
    {
        return match ($status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'ready_for_approval' => 'warning',
            'payment_approval' => 'info',
            'additional_documents_needed' => 'gray',
            default => 'primary',
        };
    }

    /**
     * Get icon for action button.
     */
    private function getActionIcon(string $status): string
    {
        return match ($status) {
            'submitted' => 'heroicon-o-document-check',
            'additional_documents_needed' => 'heroicon-o-paper-clip',
            'applied' => 'heroicon-o-paper-airplane',
            'offer_received' => 'heroicon-o-envelope',
            'payment_approval' => 'heroicon-o-receipt-percent',
            'ready_for_approval' => 'heroicon-o-check-badge',
            'approved' => 'heroicon-o-check-circle',
            'rejected' => 'heroicon-o-x-circle',
            default => 'heroicon-o-arrow-right',
        };
    }

    /**
     * Check if transition requires confirmation modal.
     */
    private function requiresConfirmation(string $status): bool
    {
        return in_array($status, ['approved', 'rejected', 'ready_for_approval']);
    }

    /**
     * Check if transition requires additional input (reason, file upload, etc.).
     */
    private function requiresInput(string $fromStatus, string $toStatus): bool
    {
        // Rejection always requires reason
        if ($toStatus === 'rejected') {
            return true;
        }

        // Payment approval requires receipt upload
        if ($toStatus === 'payment_approval') {
            return true;
        }

        // Additional documents request requires note
        if ($toStatus === 'additional_documents_needed') {
            return true;
        }

        return false;
    }

    /**
     * Get all possible statuses.
     */
    public static function getAllStatuses(): array
    {
        return [
            'needs_review' => ['label' => 'Needs Review', 'color' => 'warning'],
            'submitted' => ['label' => 'Submitted', 'color' => 'info'],
            'additional_documents_needed' => ['label' => 'Additional Documents Needed', 'color' => 'gray'],
            'applied' => ['label' => 'Applied to University', 'color' => 'indigo'],
            'offer_received' => ['label' => 'Offer Received', 'color' => 'teal'],
            'payment_approval' => ['label' => 'Awaiting Payment Approval', 'color' => 'amber'],
            'ready_for_approval' => ['label' => 'Ready for Final Approval', 'color' => 'orange'],
            'approved' => ['label' => 'Approved', 'color' => 'success'],
            'rejected' => ['label' => 'Rejected', 'color' => 'danger'],
        ];
    }
}
