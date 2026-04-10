<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\Medicine;
use App\Models\MedicinePurchaseLog;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalWorkflowService
{
    public function __construct(
        private readonly InventoryMutationService $inventoryMutationService,
    ) {
    }

    public function submit(
        User $requester,
        string $type,
        string $module,
        string $title,
        array $payload,
        array $beforeData = [],
        ?string $requestNote = null,
    ): ApprovalRequest {
        return ApprovalRequest::query()->create([
            'request_type' => $type,
            'module' => $module,
            'title' => $title,
            'status' => ApprovalRequest::STATUS_PENDING,
            'requested_by' => $requester->id,
            'payload' => $payload,
            'before_data' => $beforeData !== [] ? $beforeData : null,
            'request_note' => $requestNote,
            'requested_at' => now(),
        ]);
    }

    public function approve(ApprovalRequest $approvalRequest, User $approver, ?string $decisionNote = null): ApprovalRequest
    {
        if ($approvalRequest->status !== ApprovalRequest::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'approval' => 'Permintaan approval sudah diproses sebelumnya.',
            ]);
        }

        DB::transaction(function () use ($approvalRequest, $approver, $decisionNote): void {
            $this->applyApprovedMutation($approvalRequest, $approver);

            $approvalRequest->update([
                'status' => ApprovalRequest::STATUS_APPROVED,
                'processed_by' => $approver->id,
                'processed_at' => now(),
                'decision_note' => $decisionNote,
            ]);
        });

        ActivityLogger::log(
            $approver,
            'master_admin.approval',
            'approve',
            "Master admin menyetujui approval {$approvalRequest->id} ({$approvalRequest->typeLabel()}).",
            $approvalRequest,
            [
                'request_type' => $approvalRequest->request_type,
                'requested_by' => $approvalRequest->requested_by,
                'decision_note' => $decisionNote,
            ]
        );

        return $approvalRequest->refresh();
    }

    public function reject(ApprovalRequest $approvalRequest, User $approver, ?string $decisionNote = null): ApprovalRequest
    {
        if ($approvalRequest->status !== ApprovalRequest::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'approval' => 'Permintaan approval sudah diproses sebelumnya.',
            ]);
        }

        $approvalRequest->update([
            'status' => ApprovalRequest::STATUS_REJECTED,
            'processed_by' => $approver->id,
            'processed_at' => now(),
            'decision_note' => $decisionNote,
        ]);

        ActivityLogger::log(
            $approver,
            'master_admin.approval',
            'reject',
            "Master admin menolak approval {$approvalRequest->id} ({$approvalRequest->typeLabel()}).",
            $approvalRequest,
            [
                'request_type' => $approvalRequest->request_type,
                'requested_by' => $approvalRequest->requested_by,
                'decision_note' => $decisionNote,
            ]
        );

        return $approvalRequest->refresh();
    }

    private function applyApprovedMutation(ApprovalRequest $approvalRequest, User $approver): void
    {
        $payload = (array) ($approvalRequest->payload ?? []);
        $requester = $approvalRequest->requestedBy ?? $approver;

        match ($approvalRequest->request_type) {
            ApprovalRequest::TYPE_PURCHASE_ITEM => $this->applyPurchaseItem($requester, $payload),
            ApprovalRequest::TYPE_PURCHASE_DOCUMENTATION => $this->applyPurchaseDocumentation($requester, $payload),
            ApprovalRequest::TYPE_STOCK_ADJUSTMENT,
            ApprovalRequest::TYPE_PRICE_CHANGE => $this->applyMedicineUpdate($requester, $payload),
            ApprovalRequest::TYPE_USER_CREATION => $this->applyUserCreation($payload),
            ApprovalRequest::TYPE_ROLE_PERMISSION_CHANGE => $this->applyRolePermissionChange($payload),
            default => throw ValidationException::withMessages([
                'approval' => 'Tipe approval tidak dikenali sistem.',
            ]),
        };
    }

    private function applyPurchaseItem(User $requester, array $payload): void
    {
        $this->inventoryMutationService->createWarehousePurchase($requester, $payload);
    }

    private function applyPurchaseDocumentation(User $requester, array $payload): void
    {
        $purchaseLog = MedicinePurchaseLog::query()->findOrFail((int) ($payload['purchase_log_id'] ?? 0));
        $this->inventoryMutationService->updateWarehousePurchase($requester, $purchaseLog, $payload);
    }

    private function applyMedicineUpdate(User $requester, array $payload): void
    {
        $medicine = Medicine::query()->findOrFail((int) ($payload['medicine_id'] ?? 0));
        $this->inventoryMutationService->updateMedicineMaster($requester, $medicine, $payload);
    }

    private function applyUserCreation(array $payload): void
    {
        $email = trim((string) ($payload['email'] ?? ''));

        if ($email === '') {
            throw ValidationException::withMessages([
                'email' => 'Email user wajib tersedia pada payload approval.',
            ]);
        }

        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Email user sudah digunakan.',
            ]);
        }

        User::query()->create([
            'name' => $payload['name'] ?? 'User Baru',
            'email' => $email,
            'password' => $payload['password'] ?? 'rahasia123',
            'role' => $payload['role'] ?? User::ROLE_STAF,
            'phone' => $payload['phone'] ?? null,
            'address' => $payload['address'] ?? null,
            'bio' => $payload['bio'] ?? null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    private function applyRolePermissionChange(array $payload): void
    {
        $targetUser = User::query()->findOrFail((int) ($payload['target_user_id'] ?? 0));
        $newRole = (string) ($payload['new_role'] ?? '');

        if (! in_array($newRole, User::roles(), true)) {
            throw ValidationException::withMessages([
                'new_role' => 'Role tujuan tidak valid.',
            ]);
        }

        $targetUser->update([
            'role' => $newRole,
        ]);
    }
}
