<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Penjualan</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }

        * {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            padding: 15px;
            background: #f4f4f4;
        }

        .receipt {
            width: 100%;
            max-width: 300px;
            background: white;
            padding: 10px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .header img {
            width: 35px;
            height: auto;
            margin-right: 10px;
        }

        .header .title {
            text-align: left;
        }

        .header .title h1 {
            font-size: 14px;
            margin: 0;
            text-transform: uppercase;
        }

        .header .title p {
            font-size: 10px;
            margin: 2px 0;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .info {
            margin-bottom: 5px;
        }

        .info table {
            width: 100%;
        }

        .info td {
            padding: 2px 0;
            vertical-align: top;
        }

        .info td:first-child {
            width: 70px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .items td {
            padding: 2px 0;
            font-size: 12px;
        }

        .items td:nth-child(2) {
            text-align: right;
        }

        .totals {
            margin-top: 5px;
            width: 100%;
        }

        .totals td {
            font-weight: bold;
            padding: 3px 0;
        }

        .totals td:first-child {
            text-align: left;
        }

        .totals td:last-child {
            text-align: right;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 11px;
        }

        .print-button-container {
            text-align: center;
            margin-top: 20px;
        }

        .print-button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        @media print {
            .print-button-container {
                display: none;
            }

            body {
                padding: 0;
                background: none;
            }

            .receipt {
                box-shadow: none;
                padding: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>

{{-- STRUK NOTA --}}
<div class="receipt">
    {{-- HEADER --}}
    <div class="header">
        <img src="{{ asset('images/logo-dashboard2.png') }}" alt="Logo Apotek">
        <div class="title">
            <h1>Apotek Cahaya Dua</h1>
            <p>Jl. Pasukan Ronggolawe No.9</p>
            <p>Wonosobo Timur, Jawa Tengah</p>
        </div>
    </div>

    <div class="separator"></div>

    {{-- INFO TRANSAKSI --}}
    <div class="info">
        <table>
            <tr>
                <td><strong>Tanggal</strong></td>
                <td>: {{ \Carbon\Carbon::parse($transaction->date)->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td><strong>Pelanggan</strong></td>
                <td>: {{ $transaction->customer->name }}</td>
            </tr>
        </table>
    </div>

    <div class="separator"></div>

    {{-- DAFTAR PRODUK --}}
    <table class="items">
        <tbody>
            @foreach ($transaction->details as $detail)
                <tr>
                    <td colspan="2">{{ $detail->product->name }}</td>
                </tr>
                <tr>
                    <td>{{ $detail->quantity }} x Rp{{ number_format($detail->price, 0, '', '.') }}</td>
                    <td>Rp{{ number_format($detail->total, 0, '', '.') }}</td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>
            @endforeach
        </tbody>
    </table>

    <div class="separator"></div>

    {{-- TOTAL --}}
    <table class="totals">
        <tr>
            <td>Total</td>
            <td>Rp{{ number_format($transaction->details->sum('total'), 0, '', '.') }}</td>
        </tr>
    </table>

    <div class="separator"></div>

    {{-- FOOTER --}}
    <div class="footer">
        <p>-- Terima Kasih --</p>
        <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
    </div>
</div>

{{-- BUTTON CETAK --}}
<div class="print-button-container">
    <button class="print-button" onclick="window.print()">Cetak Nota</button>
</div>

</body>
</html>
