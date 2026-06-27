<?php

namespace App\Data;

use Illuminate\Support\Collection;

/**
 * Static demo data for Shelvi Finance (no DB/models — design-only).
 * Indian business context: INR amounts, Indian party & bank names.
 *
 * Dates are stored ISO (Y-m-d) so DataTables sort chronologically; views and
 * column renderers format them for display via App\Support\Dates::human().
 */
class Mock
{
    /** Dashboard KPI tiles. */
    public static function kpis(): array
    {
        return [
            ['label' => 'Total Bank Balance', 'value' => 4832500, 'tone' => 'navy', 'trend' => '+2.4%', 'up' => true],
            ['label' => "Today's Collections", 'value' => 185000, 'tone' => 'success', 'trend' => '+12%', 'up' => true],
            ['label' => "Today's Payments", 'value' => -72400, 'tone' => 'danger', 'trend' => '-4%', 'up' => false],
            ['label' => 'Amount to Receive', 'value' => 1264000, 'tone' => 'teal', 'trend' => '+1.1%', 'up' => true],
            ['label' => 'Amount to Pay', 'value' => -638500, 'tone' => 'gold', 'trend' => '-0.6%', 'up' => false],
            ['label' => 'Pending Cheques', 'value' => 7, 'tone' => 'gold', 'trend' => '+1', 'up' => true, 'isCount' => true],
        ];
    }

    /** Weekly collection vs payment (dashboard chart). */
    public static function weeklyChart(): array
    {
        return [
            ['week' => 'W1', 'collection' => 580000, 'payment' => 220000],
            ['week' => 'W2', 'collection' => 720000, 'payment' => 310000],
            ['week' => 'W3', 'collection' => 640000, 'payment' => 280000],
            ['week' => 'W4', 'collection' => 905000, 'payment' => 412000],
        ];
    }

    /** Cheques pending verification (dashboard side list). */
    public static function pendingVerifications(): array
    {
        return [
            ['party' => 'Sharma Finance', 'amount' => 15500, 'date' => '2025-06-11'],
            ['party' => 'Modi Electronics', 'amount' => 67500, 'date' => '2025-06-09'],
            ['party' => 'Sunrise Distributors', 'amount' => 92000, 'date' => '2025-06-08'],
            ['party' => 'Kapoor Agency', 'amount' => 28000, 'date' => '2025-06-08'],
        ];
    }

    public static function recentTxns(): Collection
    {
        return collect([
            ['id' => 'T-1012', 'date' => '2025-06-12', 'party' => 'Mehta Traders', 'type' => 'Received', 'bank' => 'HDFC Bank', 'amount' => 85000, 'status' => 'Cleared'],
            ['id' => 'T-1011', 'date' => '2025-06-12', 'party' => 'Patel Enterprises', 'type' => 'Paid', 'bank' => 'SBI', 'amount' => -32000, 'status' => 'Cleared'],
            ['id' => 'T-1010', 'date' => '2025-06-11', 'party' => 'Rajesh & Co', 'type' => 'Received', 'bank' => 'ICICI', 'amount' => 120000, 'status' => 'Cleared'],
            ['id' => 'T-1009', 'date' => '2025-06-11', 'party' => 'Sharma Finance', 'type' => 'Paid', 'bank' => 'Axis', 'amount' => -15500, 'status' => 'Pending'],
            ['id' => 'T-1008', 'date' => '2025-06-10', 'party' => 'Global Imports', 'type' => 'Received', 'bank' => 'HDFC', 'amount' => 240000, 'status' => 'Cleared'],
            ['id' => 'T-1007', 'date' => '2025-06-10', 'party' => 'City Suppliers', 'type' => 'Paid', 'bank' => 'SBI', 'amount' => -8200, 'status' => 'Cleared'],
            ['id' => 'T-1006', 'date' => '2025-06-09', 'party' => 'Modi Electronics', 'type' => 'Received', 'bank' => 'ICICI', 'amount' => 67500, 'status' => 'Pending'],
            ['id' => 'T-1005', 'date' => '2025-06-09', 'party' => 'Kapoor Agency', 'type' => 'Paid', 'bank' => 'HDFC', 'amount' => -45000, 'status' => 'Cleared'],
        ]);
    }

    public static function banks(): array
    {
        return [
            ['id' => 'hdfc', 'name' => 'HDFC Bank', 'initials' => 'HD', 'account' => 'XXXX XXXX 4821', 'holder' => 'Shelvi Financial Services', 'balance' => 1842000, 'type' => 'Current'],
            ['id' => 'sbi', 'name' => 'State Bank of India', 'initials' => 'SB', 'account' => 'XXXX XXXX 2234', 'holder' => 'Shelvi Financial Services', 'balance' => 1480500, 'type' => 'Current'],
            ['id' => 'icici', 'name' => 'ICICI Bank', 'initials' => 'IC', 'account' => 'XXXX XXXX 9910', 'holder' => 'Shelvi Financial Services', 'balance' => 960000, 'type' => 'Savings'],
            ['id' => 'axis', 'name' => 'Axis Bank', 'initials' => 'AX', 'account' => 'XXXX XXXX 3367', 'holder' => 'Shelvi Financial Services', 'balance' => 550000, 'type' => 'Current'],
        ];
    }

    public static function bankTxns(): Collection
    {
        return collect([
            ['id' => 'B-2012', 'date' => '2025-06-12', 'desc' => 'NEFT - Mehta Traders', 'credit' => 85000, 'debit' => 0, 'balance' => 1842000],
            ['id' => 'B-2011', 'date' => '2025-06-11', 'desc' => 'RTGS - Global Imports', 'credit' => 240000, 'debit' => 0, 'balance' => 1757000],
            ['id' => 'B-2010', 'date' => '2025-06-10', 'desc' => 'Cheque #004521 - Kapoor Agency', 'credit' => 0, 'debit' => 45000, 'balance' => 1517000],
            ['id' => 'B-2009', 'date' => '2025-06-09', 'desc' => 'UPI - Modi Electronics', 'credit' => 67500, 'debit' => 0, 'balance' => 1562000],
            ['id' => 'B-2008', 'date' => '2025-06-08', 'desc' => 'Online Transfer - City Suppliers', 'credit' => 0, 'debit' => 8200, 'balance' => 1494500],
            ['id' => 'B-2007', 'date' => '2025-06-07', 'desc' => 'NEFT - Sunrise Distributors', 'credit' => 320000, 'debit' => 0, 'balance' => 1502700],
            ['id' => 'B-2006', 'date' => '2025-06-06', 'desc' => 'Cheque #004519 - Patel Enterprises', 'credit' => 0, 'debit' => 32000, 'balance' => 1182700],
            ['id' => 'B-2005', 'date' => '2025-06-05', 'desc' => 'UPI - Rajesh & Co', 'credit' => 120000, 'debit' => 0, 'balance' => 1214700],
            ['id' => 'B-2004', 'date' => '2025-06-04', 'desc' => 'Bank Charges', 'credit' => 0, 'debit' => 1500, 'balance' => 1094700],
            ['id' => 'B-2003', 'date' => '2025-06-03', 'desc' => 'NEFT - National Finance Ltd', 'credit' => 500000, 'debit' => 0, 'balance' => 1096200],
        ]);
    }

    public static function parties(): Collection
    {
        return collect([
            ['name' => 'Mehta Traders', 'category' => 'Customer', 'phone' => '9876543210', 'opening' => 50000, 'current' => 132000, 'balType' => 'DR', 'limit' => 500000, 'status' => 'Active'],
            ['name' => 'Patel Enterprises', 'category' => 'Vendor', 'phone' => '9812345678', 'opening' => 0, 'current' => 68500, 'balType' => 'CR', 'limit' => 200000, 'status' => 'Active'],
            ['name' => 'Rajesh & Co', 'category' => 'Customer', 'phone' => '9988776655', 'opening' => 25000, 'current' => 240000, 'balType' => 'DR', 'limit' => 1000000, 'status' => 'Active'],
            ['name' => 'Sharma Finance', 'category' => 'Finance Co', 'phone' => '9123456789', 'opening' => 500000, 'current' => 450000, 'balType' => 'CR', 'limit' => 5000000, 'status' => 'Active'],
            ['name' => 'Global Imports', 'category' => 'Vendor', 'phone' => '9234567891', 'opening' => 0, 'current' => 180000, 'balType' => 'CR', 'limit' => 800000, 'status' => 'Active'],
            ['name' => 'City Suppliers', 'category' => 'Vendor', 'phone' => '9345678912', 'opening' => 10000, 'current' => 92000, 'balType' => 'CR', 'limit' => 300000, 'status' => 'Active'],
            ['name' => 'Modi Electronics', 'category' => 'Customer', 'phone' => '9456789123', 'opening' => 75000, 'current' => 315000, 'balType' => 'DR', 'limit' => 1500000, 'status' => 'Active'],
            ['name' => 'Kapoor Agency', 'category' => 'Agency', 'phone' => '9567891234', 'opening' => 0, 'current' => 45000, 'balType' => 'CR', 'limit' => 100000, 'status' => 'Inactive'],
            ['name' => 'Sunrise Distributors', 'category' => 'Customer', 'phone' => '9678912345', 'opening' => 100000, 'current' => 580000, 'balType' => 'DR', 'limit' => 2000000, 'status' => 'Active'],
            ['name' => 'National Finance Ltd', 'category' => 'Finance Co', 'phone' => '9789123456', 'opening' => 1000000, 'current' => 820000, 'balType' => 'CR', 'limit' => 10000000, 'status' => 'Active'],
        ]);
    }

    public static function received(): Collection
    {
        return collect([
            ['id' => 'R-9001', 'date' => '2025-06-12', 'party' => 'Mehta Traders', 'method' => 'Online', 'bank' => 'HDFC', 'amount' => 85000, 'ref' => 'NEFT-7723', 'status' => 'Cleared'],
            ['id' => 'R-9002', 'date' => '2025-06-12', 'party' => 'Global Imports', 'method' => 'UPI', 'bank' => 'HDFC', 'amount' => 24000, 'ref' => 'UPI-AX9912', 'status' => 'Cleared'],
            ['id' => 'R-9003', 'date' => '2025-06-11', 'party' => 'Rajesh & Co', 'method' => 'Online', 'bank' => 'ICICI', 'amount' => 120000, 'ref' => 'RTGS-5512', 'status' => 'Cleared'],
            ['id' => 'R-9004', 'date' => '2025-06-11', 'party' => 'Modi Electronics', 'method' => 'Cheque', 'bank' => 'ICICI', 'amount' => 67500, 'ref' => 'CHQ-004521', 'status' => 'Pending'],
            ['id' => 'R-9005', 'date' => '2025-06-10', 'party' => 'Sunrise Distributors', 'method' => 'Online', 'bank' => 'SBI', 'amount' => 320000, 'ref' => 'NEFT-8821', 'status' => 'Cleared'],
            ['id' => 'R-9006', 'date' => '2025-06-09', 'party' => 'National Finance Ltd', 'method' => 'Cash', 'bank' => 'HDFC', 'amount' => 50000, 'ref' => 'CASH-001', 'status' => 'Cleared'],
            ['id' => 'R-9007', 'date' => '2025-06-08', 'party' => 'Mehta Traders', 'method' => 'UPI', 'bank' => 'HDFC', 'amount' => 18000, 'ref' => 'UPI-MT9981', 'status' => 'Cleared'],
            ['id' => 'R-9008', 'date' => '2025-06-07', 'party' => 'Rajesh & Co', 'method' => 'Cheque', 'bank' => 'Axis', 'amount' => 80000, 'ref' => 'CHQ-004519', 'status' => 'Cleared'],
            ['id' => 'R-9009', 'date' => '2025-06-06', 'party' => 'Global Imports', 'method' => 'Online', 'bank' => 'HDFC', 'amount' => 150000, 'ref' => 'NEFT-7611', 'status' => 'Cleared'],
            ['id' => 'R-9010', 'date' => '2025-06-05', 'party' => 'Modi Electronics', 'method' => 'UPI', 'bank' => 'ICICI', 'amount' => 22000, 'ref' => 'UPI-MD7711', 'status' => 'Cleared'],
        ]);
    }

    public static function paid(): Collection
    {
        return collect([
            ['id' => 'P-7001', 'date' => '2025-06-12', 'party' => 'Patel Enterprises', 'method' => 'Online', 'bank' => 'SBI', 'amount' => 32000, 'ref' => 'NEFT-OUT-991', 'status' => 'Cleared'],
            ['id' => 'P-7002', 'date' => '2025-06-11', 'party' => 'Sharma Finance', 'method' => 'Cheque', 'bank' => 'Axis', 'amount' => 15500, 'ref' => 'CHQ-OUT-118', 'status' => 'Pending'],
            ['id' => 'P-7003', 'date' => '2025-06-10', 'party' => 'City Suppliers', 'method' => 'UPI', 'bank' => 'SBI', 'amount' => 8200, 'ref' => 'UPI-OUT-441', 'status' => 'Cleared'],
            ['id' => 'P-7004', 'date' => '2025-06-09', 'party' => 'Kapoor Agency', 'method' => 'Online', 'bank' => 'HDFC', 'amount' => 45000, 'ref' => 'NEFT-OUT-882', 'status' => 'Cleared'],
            ['id' => 'P-7005', 'date' => '2025-06-08', 'party' => 'Global Imports', 'method' => 'Cheque', 'bank' => 'HDFC', 'amount' => 92000, 'ref' => 'CHQ-OUT-117', 'status' => 'Cleared'],
            ['id' => 'P-7006', 'date' => '2025-06-07', 'party' => 'Patel Enterprises', 'method' => 'Online', 'bank' => 'SBI', 'amount' => 18500, 'ref' => 'NEFT-OUT-771', 'status' => 'Cleared'],
            ['id' => 'P-7007', 'date' => '2025-06-06', 'party' => 'Sharma Finance', 'method' => 'UPI', 'bank' => 'Axis', 'amount' => 6700, 'ref' => 'UPI-OUT-330', 'status' => 'Cleared'],
            ['id' => 'P-7008', 'date' => '2025-06-05', 'party' => 'Kapoor Agency', 'method' => 'Cash', 'bank' => 'HDFC', 'amount' => 12000, 'ref' => 'CASH-OUT-08', 'status' => 'Cleared'],
        ]);
    }

    public static function ledgerParty(): string
    {
        return 'Mehta Traders';
    }

    public static function ledger(): Collection
    {
        return collect([
            ['date' => '2025-06-01', 'particulars' => 'Opening Balance', 'vch' => '-', 'debit' => 0, 'credit' => 0, 'balance' => 50000, 'balType' => 'DR'],
            ['date' => '2025-06-02', 'particulars' => 'Invoice INV-2001', 'vch' => 'INV-2001', 'debit' => 120000, 'credit' => 0, 'balance' => 170000, 'balType' => 'DR'],
            ['date' => '2025-06-03', 'particulars' => 'Payment Received - NEFT', 'vch' => 'REC-501', 'debit' => 0, 'credit' => 80000, 'balance' => 90000, 'balType' => 'DR'],
            ['date' => '2025-06-04', 'particulars' => 'Invoice INV-2014', 'vch' => 'INV-2014', 'debit' => 65000, 'credit' => 0, 'balance' => 155000, 'balType' => 'DR'],
            ['date' => '2025-06-05', 'particulars' => 'Payment Received - UPI', 'vch' => 'REC-509', 'debit' => 0, 'credit' => 25000, 'balance' => 130000, 'balType' => 'DR'],
            ['date' => '2025-06-06', 'particulars' => 'Invoice INV-2030', 'vch' => 'INV-2030', 'debit' => 95000, 'credit' => 0, 'balance' => 225000, 'balType' => 'DR'],
            ['date' => '2025-06-07', 'particulars' => 'Cheque Received #004519', 'vch' => 'REC-512', 'debit' => 0, 'credit' => 80000, 'balance' => 145000, 'balType' => 'DR'],
            ['date' => '2025-06-08', 'particulars' => 'Invoice INV-2044', 'vch' => 'INV-2044', 'debit' => 40000, 'credit' => 0, 'balance' => 185000, 'balType' => 'DR'],
            ['date' => '2025-06-09', 'particulars' => 'Payment Received - Online', 'vch' => 'REC-520', 'debit' => 0, 'credit' => 35000, 'balance' => 150000, 'balType' => 'DR'],
            ['date' => '2025-06-10', 'particulars' => 'Invoice INV-2059', 'vch' => 'INV-2059', 'debit' => 70000, 'credit' => 0, 'balance' => 220000, 'balType' => 'DR'],
            ['date' => '2025-06-11', 'particulars' => 'Payment Received - UPI', 'vch' => 'REC-528', 'debit' => 0, 'credit' => 18000, 'balance' => 202000, 'balType' => 'DR'],
            ['date' => '2025-06-12', 'particulars' => 'Payment Received - NEFT', 'vch' => 'REC-535', 'debit' => 0, 'credit' => 70000, 'balance' => 132000, 'balType' => 'DR'],
        ]);
    }

    public static function cheques(): Collection
    {
        return collect([
            ['no' => '004521', 'party' => 'Modi Electronics', 'bank' => 'ICICI', 'amount' => 67500, 'issue' => '2025-06-08', 'deposit' => '2025-06-09', 'due' => '2025-06-13', 'status' => 'Pending'],
            ['no' => '004519', 'party' => 'Rajesh & Co', 'bank' => 'Axis', 'amount' => 80000, 'issue' => '2025-06-05', 'deposit' => '2025-06-07', 'due' => '2025-06-11', 'status' => 'Cleared'],
            ['no' => '004518', 'party' => 'Sharma Finance', 'bank' => 'Axis', 'amount' => 15500, 'issue' => '2025-06-10', 'deposit' => '2025-06-11', 'due' => '2025-06-15', 'status' => 'Pending'],
            ['no' => '004517', 'party' => 'Global Imports', 'bank' => 'HDFC', 'amount' => 92000, 'issue' => '2025-06-06', 'deposit' => '2025-06-08', 'due' => '2025-06-12', 'status' => 'Cleared'],
            ['no' => '004516', 'party' => 'City Suppliers', 'bank' => 'SBI', 'amount' => 21000, 'issue' => '2025-06-01', 'deposit' => '2025-06-02', 'due' => '2025-06-06', 'status' => 'Bounced'],
            ['no' => '004515', 'party' => 'Mehta Traders', 'bank' => 'HDFC', 'amount' => 45000, 'issue' => '2025-05-30', 'deposit' => '2025-05-31', 'due' => '2025-06-04', 'status' => 'Cleared'],
            ['no' => '004514', 'party' => 'Patel Enterprises', 'bank' => 'SBI', 'amount' => 32000, 'issue' => '2025-05-28', 'deposit' => '2025-05-29', 'due' => '2025-06-02', 'status' => 'Cleared'],
            ['no' => '004513', 'party' => 'Kapoor Agency', 'bank' => 'HDFC', 'amount' => 28000, 'issue' => '2025-06-12', 'deposit' => null, 'due' => '2025-06-17', 'status' => 'Pending'],
            ['no' => '004512', 'party' => 'Sunrise Distributors', 'bank' => 'ICICI', 'amount' => 120000, 'issue' => '2025-06-11', 'deposit' => '2025-06-12', 'due' => '2025-06-16', 'status' => 'Pending'],
            ['no' => '004511', 'party' => 'National Finance Ltd', 'bank' => 'HDFC', 'amount' => 50000, 'issue' => '2025-05-20', 'deposit' => '2025-05-22', 'due' => '2025-05-26', 'status' => 'Bounced'],
        ]);
    }

    public static function reportTypes(): array
    {
        return [
            ['title' => 'Daily Collection Report', 'desc' => 'Day-wise inflow summary across banks', 'icon' => 'trending-up'],
            ['title' => 'Daily Payment Report', 'desc' => 'Day-wise outflow summary across banks', 'icon' => 'trending-down'],
            ['title' => 'Monthly Summary Report', 'desc' => 'Comprehensive monthly performance overview', 'icon' => 'calendar'],
            ['title' => 'Bank-wise Report', 'desc' => 'Inflow and outflow segregated per bank', 'icon' => 'landmark'],
            ['title' => 'Party-wise Report', 'desc' => 'Detailed activity per party', 'icon' => 'users'],
            ['title' => 'Outstanding Report', 'desc' => 'Receivables and payables pending', 'icon' => 'alert-circle'],
            ['title' => 'Credit Report', 'desc' => 'All credit entries across the ledger', 'icon' => 'arrow-down-left'],
            ['title' => 'Debit Report', 'desc' => 'All debit entries across the ledger', 'icon' => 'arrow-up-right'],
            ['title' => 'Ledger Report', 'desc' => 'Full general ledger with running balances', 'icon' => 'book-open'],
        ];
    }
}
