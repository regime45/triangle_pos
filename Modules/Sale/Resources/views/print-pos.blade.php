<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Receipt</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
* {
    box-sizing: border-box;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.4;
}

body {
    margin: 0;
    padding: 0;
    background: #fff;
}

.receipt {
    max-width: 300px; /* 58mm = ~280px | 80mm = ~380px */
    margin: 0 auto;
    padding: 10px;
}

.center {
    text-align: center;
}

.bold {
    font-weight: bold;
}

.small {
    font-size: 11px;
}

hr {
    border: none;
    border-top: 1px dashed #000;
    margin: 8px 0;
}

table {
    width: 100%;
    border-collapse: collapse;
}

td, th {
    padding: 4px 0;
    vertical-align: top;
}

.right {
    text-align: right;
}

.item-name {
    font-size: 12px;
}

.item-sub {
    font-size: 11px;
    color: #333;
}

.total-row th {
    font-size: 13px;
    font-weight: bold;
}

.footer {
    margin-top: 10px;
}

@media print {
    body {
        margin: 0;
    }
}
</style>
</head>

<body>

<div class="receipt">

    <!-- HEADER -->
    <div class="center">
        <div class="bold" style="font-size:16px;">
            {{ settings()->company_name }}
        </div>
        <div class="small">
            {{ settings()->company_email }}<br>
            {{ settings()->company_phone }}<br>
            {{ settings()->company_address }}
        </div>
    </div>

    <hr>

    <!-- SALE INFO -->
    <div class="small">
        Date: {{ \Carbon\Carbon::parse($sale->date)->format('d M, Y') }}<br>
        Ref: {{ $sale->reference }}<br>
        Customer: {{ $sale->customer_name }}
    </div>

    <hr>

    <!-- ITEMS -->
    <table>
        <tbody>
        @foreach($sale->saleDetails as $saleDetail)
            <tr>
                <td>
                    <div class="item-name">
                        {{ $saleDetail->product->product_name }}
                    </div>
                    <div class="item-sub">
                        {{ $saleDetail->quantity }} x {{ format_currency($saleDetail->price) }}
                    </div>
                </td>
                <td class="right">
                    {{ format_currency($saleDetail->sub_total) }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <hr>

    <!-- TOTALS -->
    <table>
        <tbody>
        @if($sale->tax_percentage)
            <tr>
                <td>Tax ({{ $sale->tax_percentage }}%)</td>
                <td class="right">{{ format_currency($sale->tax_amount) }}</td>
            </tr>
        @endif

        @if($sale->discount_percentage)
            <tr>
                <td>Discount ({{ $sale->discount_percentage }}%)</td>
                <td class="right">-{{ format_currency($sale->discount_amount) }}</td>
            </tr>
        @endif

        @if($sale->shipping_amount)
            <tr>
                <td>Shipping</td>
                <td class="right">{{ format_currency($sale->shipping_amount) }}</td>
            </tr>
        @endif

        <tr class="total-row">
            <th>GRAND TOTAL</th>
            <th class="right">{{ format_currency($sale->total_amount) }}</th>
        </tr>
        </tbody>
    </table>

    <hr>

    <!-- PAYMENT -->
    <table>
        <tr>
            <td>Paid By</td>
            <td class="right">{{ $sale->payment_method }}</td>
        </tr>
        <tr>
            <td>Amount</td>
            <td class="right">{{ format_currency($sale->paid_amount) }}</td>
        </tr>
    </table>

    <hr>

    <!-- BARCODE -->
    <div class="center footer">
        {!! \Milon\Barcode\Facades\DNS1DFacade::getBarcodeSVG(
            $sale->reference,
            'C128',
            1.2,
            30,
            'black',
            false
        ) !!}
        <div class="small">{{ $sale->reference }}</div>
    </div>

    <hr>

    <!-- FOOTER -->
    <div class="center small">
        Thank you for your purchase!<br>
        Please come again 🙂
    </div>

</div>

</body>
</html>
