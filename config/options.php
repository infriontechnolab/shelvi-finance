<?php

// Static select-option lists (config, not data). Consumed by controllers and
// passed to the create/edit forms. Kept out of the data/repository layer because
// these are fixed domain enums, not records.
return [
    'party_categories' => [
        'Customer' => 'Customer',
        'Vendor' => 'Vendor',
        'Finance Co' => 'Finance Co',
        'Agency' => 'Agency',
    ],
    'party_statuses' => [
        'Active' => 'Active',
        'Inactive' => 'Inactive',
    ],
    'balance_types' => [
        'DR' => 'DR — Receivable',
        'CR' => 'CR — Payable',
    ],
    'cheque_statuses' => [
        'Pending' => 'Pending',
        'Cleared' => 'Cleared',
        'Bounced' => 'Bounced',
    ],
    'payment_methods' => [
        'Online' => 'Online',
        'UPI' => 'UPI',
        'Cheque' => 'Cheque',
        'Cash' => 'Cash',
    ],
    'transaction_statuses' => [
        'Pending' => 'Pending',
        'Cleared' => 'Cleared',
        'Bounced' => 'Bounced',
    ],
    'bank_types' => [
        'Current' => 'Current',
        'Savings' => 'Savings',
    ],
];
