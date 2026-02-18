<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 3px 0;
            font-size: 11px;
        }

        .meta {
            width: 100%;
            margin-bottom: 10px;
        }

        .meta td {
            padding: 4px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th {
            background: #eaeaea;
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
            text-align: center;
        }

        td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 11px;
        }

        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .summary-title {
            font-size: 13px;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        .totals-table td,
        .totals-table th {
            padding: 6px;
            font-size: 11px;
        }

        .highlight {
            background: #f0f0f0;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
        }

        .meta {
            border: none;
        }

        .meta td {
            border: none !important;
            padding: 4px;
            font-size: 11px;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <h2>SALES REPORT</h2>
        <p>From {{ $start_date }} to {{ $end_date }}</p>
    </div>

    <!-- META -->
    <table class="meta" width="100%">
        <tr>
            <td><strong>Total Sales For this Day:</strong> {{ $sales->count() }}</td>
            <td class="right">
                <strong>Generated:</strong> {{ now()->format('Y-m-d H:i') }}
            </td>
        </tr>
    </table>


    <!-- SALES TABLE -->
    <!--
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Invoice</th>
            <th>Customer</th>
            <th>Status</th>
            <th>Payment</th>
            <th>Method</th>
            <th class="right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $sale)
        <tr>
            <td class="center">{{ $sale->date }}</td>
            <td class="center">{{ $sale->reference }}</td>
            <td>{{ $sale->customer_name ?? '-' }}</td>
            <td class="center">{{ $sale->status }}</td>
            <td class="center">{{ $sale->payment_status }}</td>
            <td class="center">{{ $sale->payment_method }}</td>
            <td class="right">{{ number_format($sale->paid_amount, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
    -->

    @php
    $summary = $sales
    ->groupBy(fn($s) => strtolower($s->payment_method))
    ->map(fn($rows) => $rows->sum('paid_amount'));
    @endphp

    <!-- TWO COLUMN SUMMARY WRAPPER -->
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <!-- LEFT COLUMN -->
            <td width="50%" valign="top" style="padding-right:10px;">

                <div class="summary-title">Payment Summary</div>

                <table class="totals-table" width="100%">
                    <tr>
                        <th class="right">Cash</th>
                        <td class="right">{{ number_format($summary['cash'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="right">GCash</th>
                        <td class="right">{{ number_format($summary['gcash'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="right">Credit</th>
                        <td class="right">{{ number_format($summary['credit'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="right">Bank Transfer</th>
                        <td class="right">{{ number_format($summary['bank transfer'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="right">Cheque</th>
                        <td class="right">{{ number_format($summary['cheque'] ?? 0, 2) }}</td>
                    </tr>
                    <tr class="highlight">
                        <th>Total Sales</th>
                        <td class="right">{{ number_format($summary->sum(), 2) }}</td>
                    </tr>
                </table>

            </td>

            <!-- RIGHT COLUMN -->
            <td width="50%" valign="top" style="padding-left:10px;">

                <div class="summary-title">Financial Summary</div>

                <table class="totals-table" width="100%">
                    <tr>
                        <th class="right">Total Sales</th>
                        <td class="right">{{ number_format($totalSales, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="right">Total Expenses</th>
                        <td class="right">{{ number_format($totalExpenses, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="right">Total Sales Return</th>
                        <td class="right">{{ number_format($totalSalesReturns, 2) }}</td>
                    </tr>
                    <tr class="highlight">
                        <th class="right">Net Sales</th>
                        <td class="right">{{ number_format($profit, 2) }}</td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        Generated by Sales System
    </div>

</body>

</html>