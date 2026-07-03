<?php

// Sidebar navigation as data (not markup). Items with `children` render as
// expandable groups. Consumed by resources/views/components/sidebar.blade.php.
return [
    ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard'],
    ['label' => 'Bank Accounts', 'icon' => 'landmark', 'route' => 'banks'],
    ['label' => 'Party Management', 'icon' => 'users', 'route' => 'parties'],
    ['label' => 'Transactions', 'icon' => 'arrow-left-right', 'children' => [
        ['label' => 'Money Received', 'route' => 'money-received'],
        ['label' => 'Money Paid', 'route' => 'money-paid'],
    ]],
    ['label' => 'Party Ledger', 'icon' => 'book-open', 'route' => 'ledger'],
    ['label' => 'Cheque Management', 'icon' => 'wallet', 'route' => 'cheques'],
    ['label' => 'Reports', 'icon' => 'file-text', 'route' => 'reports'],

    // Admin-only — hidden from users without the permission (see sidebar filter).
    ['label' => 'Users', 'icon' => 'users', 'route' => 'users', 'permission' => 'users.view'],
    ['label' => 'Roles', 'icon' => 'shield-check', 'route' => 'roles', 'permission' => 'roles.view'],

    // Superadmin-only — trash.view is a hidden permission group (only the owner holds it).
    ['label' => 'Trash', 'icon' => 'trash-2', 'route' => 'trash', 'permission' => 'trash.view'],
];
