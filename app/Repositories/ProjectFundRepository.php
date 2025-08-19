<?php

namespace App\Repositories;

use App\Models\ProjectFund;
use App\Models\Transaction;

class ProjectFundRepository
{
    protected ProjectFund $projectFundModel;
    protected Transaction $transactionModel;

    public function __construct(ProjectFund $projectFundModel, Transaction $transactionModel)
    {
        $this->projectFundModel = $projectFundModel;
        $this->transactionModel = $transactionModel;
    }

    public function createFunding(int $projectId, int $userId, float $amount, string $mode = 'manual'): array
    {
        // Create a transaction record
        $transaction = $this->transactionModel->create([
            'gateway_id' => 'N/A',
            'amount' => $amount,
            'mode' => $mode,
            'status' => 'initiated',
            'payment_status' => 'pending',
        ]);

        // Link to project via project_funds
        $projectFund = $this->projectFundModel->create([
            'project_id' => $projectId,
            'user_id' => $userId,
            'transaction_id' => $transaction->id,
        ]);

        return [
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
            'status' => $transaction->status,
            'project_fund_id' => $projectFund->id,
        ];
    }
}
