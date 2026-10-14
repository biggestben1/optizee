<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class PaymentSplit
{
    /**
     * Work out how much was received per channel (cash / transfer / POS).
     *
     * Change is always handed back in cash, so it is taken off the cash portion.
     *
     * @return array{amount_paid: float, change: float, cash_amount: float, transfer_amount: float, pos_amount: float}
     */
    public static function resolve(string $method, float $total, float $amountPaid, array $mixed = []): array
    {
        $result = [
            'amount_paid' => 0.0,
            'change' => 0.0,
            'cash_amount' => 0.0,
            'transfer_amount' => 0.0,
            'pos_amount' => 0.0,
        ];

        if ($method === 'credit') {
            return $result;
        }

        if ($method === 'mixed') {
            $cash = round((float) ($mixed['cash'] ?? 0), 2);
            $transfer = round((float) ($mixed['transfer'] ?? 0), 2);
            $pos = round((float) ($mixed['pos'] ?? 0), 2);
            $paid = $cash + $transfer + $pos;

            if ($cash < 0 || $transfer < 0 || $pos < 0) {
                throw ValidationException::withMessages([
                    'mixed' => 'Mixed payment amounts cannot be negative.',
                ]);
            }

            if ((int) ($cash > 0) + (int) ($transfer > 0) + (int) ($pos > 0) < 2) {
                throw ValidationException::withMessages([
                    'mixed' => 'For a mixed payment, enter amounts for at least two of Cash, Transfer and POS.',
                ]);
            }

            $change = max(0, round($paid - $total, 2));
            if ($change > $cash) {
                throw ValidationException::withMessages([
                    'mixed' => 'Transfer and POS amounts are more than the bill. Reduce them so only cash needs change.',
                ]);
            }

            return [
                'amount_paid' => $paid,
                'change' => $change,
                'cash_amount' => $cash - $change,
                'transfer_amount' => $transfer,
                'pos_amount' => $pos,
            ];
        }

        $change = max(0, round($amountPaid - $total, 2));
        $result['amount_paid'] = $amountPaid;
        $result['change'] = $change;
        $result["{$method}_amount"] = $amountPaid - $change;

        return $result;
    }
}
