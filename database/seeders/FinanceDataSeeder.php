<?php

namespace Database\Seeders;

use App\Data\Mock;
use App\Models\Bank;
use App\Models\Cheque;
use App\Models\LedgerEntry;
use App\Models\Party;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

/**
 * Ports the design-time App\Data\Mock fixtures into the database.
 * Fixture amounts are whole rupees; DB stores paise, so everything is ×100.
 * Bank running/party balances are derived, so `current` fixture values are not seeded.
 */
class FinanceDataSeeder extends Seeder
{
    /** rupees → paise */
    private function paise(int|float $rupees): int
    {
        return (int) round($rupees * 100);
    }

    public function run(): void
    {
        // --- Banks (fixtures only carry masked numbers; synthesize a full one) ---
        $banks = [];
        foreach (Mock::banks() as $b) {
            $last4 = substr($b['account'], -4);
            $banks[$b['id']] = Bank::create([
                'name' => $b['name'],
                'initials' => $b['initials'],
                'account_number' => '5010'.str_pad($b['id'] === 'hdfc' ? '1' : '2', 6, '0', STR_PAD_LEFT).$last4,
                'holder' => $b['holder'],
                'type' => $b['type'],
                'opening_balance' => $this->paise($b['balance']),
            ]);
        }

        // Short labels used across transaction/cheque fixtures → bank row.
        $byLabel = [
            'HDFC' => $banks['hdfc'], 'HDFC Bank' => $banks['hdfc'],
            'SBI' => $banks['sbi'],
            'ICICI' => $banks['icici'],
            'Axis' => $banks['axis'],
        ];

        // --- Parties ---
        // Balances are DERIVED from opening + journal. Only the ledger party has a
        // seeded journal, so for every other party we fold their displayed balance
        // into opening_balance, signed by side (CR = payable = negative) so the
        // derived current balance and DR/CR reproduce the original UI.
        $parties = [];
        foreach (Mock::parties() as $p) {
            $isLedgerParty = $p['name'] === Mock::ledgerParty();
            $sign = $p['balType'] === 'CR' ? -1 : 1;
            $opening = $isLedgerParty ? $p['opening'] : $p['current'] * $sign;

            $parties[$p['name']] = Party::create([
                'name' => $p['name'],
                'category' => $p['category'],
                'phone' => $p['phone'],
                'opening_balance' => $this->paise($opening),
                'balance_type' => $p['balType'],
                'credit_limit' => $this->paise($p['limit']),
                'status' => $p['status'],
            ]);
        }

        // --- Transactions: unified received + paid ---
        foreach (Mock::received() as $r) {
            Transaction::create([
                'reference' => $r['ref'],
                'direction' => 'received',
                'party_id' => $parties[$r['party']]->id,
                'bank_id' => $byLabel[$r['bank']]->id,
                'method' => $r['method'],
                'amount' => $this->paise($r['amount']),
                'status' => $r['status'],
                'txn_date' => $r['date'],
                'description' => $r['method'].' from '.$r['party'],
            ]);
        }
        foreach (Mock::paid() as $p) {
            Transaction::create([
                'reference' => $p['ref'],
                'direction' => 'paid',
                'party_id' => $parties[$p['party']]->id,
                'bank_id' => $byLabel[$p['bank']]->id,
                'method' => $p['method'],
                'amount' => $this->paise($p['amount']),
                'status' => $p['status'],
                'txn_date' => $p['date'],
                'description' => $p['method'].' to '.$p['party'],
            ]);
        }
        // A non-party bank line (bank charges) — exercises nullable party_id.
        Transaction::create([
            'reference' => 'CHG-001',
            'direction' => 'paid',
            'party_id' => null,
            'bank_id' => $banks['hdfc']->id,
            'method' => 'Online',
            'amount' => $this->paise(1500),
            'status' => 'Cleared',
            'txn_date' => '2025-06-04',
            'description' => 'Bank Charges',
        ]);

        // --- Cheques ---
        foreach (Mock::cheques() as $c) {
            Cheque::create([
                'cheque_no' => $c['no'],
                'direction' => 'received',
                'party_id' => $parties[$c['party']]->id,
                'bank_id' => $byLabel[$c['bank']]->id,
                'amount' => $this->paise($c['amount']),
                'issue_date' => $c['issue'],
                'deposit_date' => $c['deposit'],
                'due_date' => $c['due'],
                'status' => $c['status'],
            ]);
        }

        // --- Ledger journal for the demo party (skip the opening-balance row;
        //     that lives in parties.opening_balance). ---
        $ledgerParty = $parties[Mock::ledgerParty()];
        foreach (Mock::ledger() as $row) {
            if ($row['particulars'] === 'Opening Balance') {
                continue;
            }
            LedgerEntry::create([
                'party_id' => $ledgerParty->id,
                'entry_date' => $row['date'],
                'particulars' => $row['particulars'],
                'vch' => $row['vch'] === '-' ? null : $row['vch'],
                'debit' => $this->paise($row['debit']),
                'credit' => $this->paise($row['credit']),
            ]);
        }
    }
}
