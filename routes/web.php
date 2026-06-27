<?php

use App\Http\Controllers\BankController;
use App\Http\Controllers\ChequeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\MoneyController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Design-only app (no DB/auth). Controllers stay thin — they pull demo data from
// App\Data\Mock and hand it to views / Yajra DataTable service classes. There are
// no store/update/destroy actions yet (forms are inert until a backend lands).

// Dashboard — page + dedicated ajax route for its recent-transactions widget.
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/recent-txns', [DashboardController::class, 'recentTxns'])->name('dashboard.recent-txns');

// Bank Accounts.
Route::get('/banks', [BankController::class, 'index'])->name('banks');
Route::get('/banks/create', [BankController::class, 'create'])->name('banks.create');
Route::get('/banks/{bank}/edit', [BankController::class, 'edit'])->name('banks.edit');

// Party Management.
Route::get('/parties', [PartyController::class, 'index'])->name('parties');
Route::get('/parties/create', [PartyController::class, 'create'])->name('parties.create');
Route::get('/parties/{party}/edit', [PartyController::class, 'edit'])->name('parties.edit');

// Money Received / Money Paid.
Route::get('/money-received', [MoneyController::class, 'received'])->name('money-received');
Route::get('/money-paid', [MoneyController::class, 'paid'])->name('money-paid');

// Party Ledger.
Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger');

// Cheque Management.
Route::get('/cheques', [ChequeController::class, 'index'])->name('cheques');
Route::get('/cheques/create', [ChequeController::class, 'create'])->name('cheques.create');
Route::get('/cheques/{cheque}/edit', [ChequeController::class, 'edit'])->name('cheques.edit');

// Reports.
Route::get('/reports', [ReportController::class, 'index'])->name('reports');
