<?php

namespace App\Data;

/** Typed cheque status counts for the summary tiles. */
final readonly class ChequeStats
{
    public function __construct(
        public int $total,
        public int $pending,
        public int $cleared,
        public int $bounced,
    ) {}
}
