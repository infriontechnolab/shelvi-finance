<?php

namespace App\Data;

/** Typed ledger totals + period bounds (replaces the loose summary array). */
final readonly class LedgerSummary
{
    public function __construct(
        public int $opening,
        public int $totalDebit,
        public int $totalCredit,
        public int $closing,
        public string $closingType,
        public string $from,
        public string $to,
    ) {}
}
