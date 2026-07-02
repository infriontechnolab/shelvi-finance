<?php

use App\Http\Controllers\BankController;
use App\Http\Controllers\ChequeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\MoneyController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// All application pages require an authenticated user; each route additionally
// requires the matching permission (admin has all; accountant has the subset).
Route::middleware('auth')->group(function () {
    // Dashboard — page + dedicated ajax route for its recent-transactions widget.
    Route::middleware('permission:dashboard.view')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/recent-txns', [DashboardController::class, 'recentTxns'])->name('dashboard.recent-txns');
    });

    // Bank Accounts.
    Route::get('/banks', [BankController::class, 'index'])->name('banks')->middleware('permission:banks.view');
    Route::get('/banks/create', [BankController::class, 'create'])->name('banks.create')->middleware('permission:banks.create');
    Route::post('/banks', [BankController::class, 'store'])->name('banks.store')->middleware('permission:banks.create');
    Route::get('/banks/{bank}/edit', [BankController::class, 'edit'])->name('banks.edit')->middleware('permission:banks.update');
    Route::put('/banks/{bank}', [BankController::class, 'update'])->name('banks.update')->middleware('permission:banks.update');
    Route::delete('/banks/{bank}', [BankController::class, 'destroy'])->name('banks.destroy')->middleware('permission:banks.delete');

    // Party Management.
    Route::get('/parties', [PartyController::class, 'index'])->name('parties')->middleware('permission:parties.view');
    Route::get('/parties/create', [PartyController::class, 'create'])->name('parties.create')->middleware('permission:parties.create');
    Route::post('/parties', [PartyController::class, 'store'])->name('parties.store')->middleware('permission:parties.create');
    Route::get('/parties/{party}/edit', [PartyController::class, 'edit'])->name('parties.edit')->middleware('permission:parties.update');
    Route::put('/parties/{party}', [PartyController::class, 'update'])->name('parties.update')->middleware('permission:parties.update');
    Route::delete('/parties/{party}', [PartyController::class, 'destroy'])->name('parties.destroy')->middleware('permission:parties.delete');

    // Money Received / Money Paid (both are `transactions` rows, direction-tagged).
    Route::middleware('permission:transactions.view')->group(function () {
        Route::get('/money-received', [MoneyController::class, 'received'])->name('money-received');
        Route::get('/money-paid', [MoneyController::class, 'paid'])->name('money-paid');
    });
    Route::post('/money-received', [MoneyController::class, 'store'])->name('money-received.store')->middleware('permission:transactions.create');
    Route::post('/money-paid', [MoneyController::class, 'store'])->name('money-paid.store')->middleware('permission:transactions.create');
    Route::get('/transactions/{transaction}/edit', [MoneyController::class, 'edit'])->name('transactions.edit')->middleware('permission:transactions.update');
    Route::put('/transactions/{transaction}', [MoneyController::class, 'update'])->name('transactions.update')->middleware('permission:transactions.update');
    Route::delete('/transactions/{transaction}', [MoneyController::class, 'destroy'])->name('transactions.destroy')->middleware('permission:transactions.delete');

    // Party Ledger.
    Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger')->middleware('permission:ledger.view');

    // Cheque Management.
    Route::get('/cheques', [ChequeController::class, 'index'])->name('cheques')->middleware('permission:cheques.view');
    Route::get('/cheques/create', [ChequeController::class, 'create'])->name('cheques.create')->middleware('permission:cheques.create');
    Route::post('/cheques', [ChequeController::class, 'store'])->name('cheques.store')->middleware('permission:cheques.create');
    Route::get('/cheques/{cheque}/edit', [ChequeController::class, 'edit'])->name('cheques.edit')->middleware('permission:cheques.update');
    Route::put('/cheques/{cheque}', [ChequeController::class, 'update'])->name('cheques.update')->middleware('permission:cheques.update');
    Route::delete('/cheques/{cheque}', [ChequeController::class, 'destroy'])->name('cheques.destroy')->middleware('permission:cheques.delete');
    Route::post('/cheques/{cheque}/verify', [ChequeController::class, 'verify'])->name('cheques.verify')->middleware('permission:cheques.verify');

    // Reports.
    Route::get('/reports', [ReportController::class, 'index'])->name('reports')->middleware('permission:reports.view');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show')->middleware('permission:reports.view');

    // User Management (admin).
    Route::get('/users', [UserController::class, 'index'])->name('users')->middleware('permission:users.view');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('permission:users.create');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:users.update');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:users.delete');

    // Role & permission management (superadmin only — no visible role grants roles.*).
    Route::get('/roles', [RoleController::class, 'index'])->name('roles')->middleware('permission:roles.view');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('permission:roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:roles.create');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('permission:roles.update');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:roles.delete');
});

require __DIR__.'/auth.php';
