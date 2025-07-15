<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 40px;
            margin-right: 7px;
            vertical-align: middle;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
            vertical-align: middle;
        }

        .header h4 {
            margin: 5px 0 0 0;
            font-weight: normal;
            font-size: 14px;
        }

        .double-line {
            border-top: 3px solid black;
            border-bottom: 1px solid black;
            margin: 10px 0 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            text-align: center; 
        }

        th {
            background-color: #eee;
        }

        .total-row td {
            font-weight: bold;
        }

    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <img src="{{ asset('images/logo-dashboard2.png') }}" alt="Logo Apotek">
        <h1>Apotek Cahaya Dua</h1>
        <h4>
            Jl. Pasukan Ronggolawe No.9, Wonosobo Timur, Wonosobo Tim.,<br>
            Kec. Wonosobo, Kabupaten Wonosobo, Jawa Tengah 56311
        </h4>
    </div>

    <div class="double-line"></div>

    {{-- Info Transaksi --}}
    <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaction->date)->translatedFormat('d F Y') }}</p>
    <p><strong>Pelanggan:</strong> {{ $transaction->customer->name }}</p>

    {{-- Tabel Produk --}}
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->details as $detail)
                <tr>
                    <td>{{ $detail->product->name }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>Rp {{ number_format($detail->price, 0, '', '.') }}</td>
                    <td>Rp {{ number_format($detail->total, 0, '', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3">Total</td>
                <td>Rp {{ number_format($transaction->details->sum('total'), 0, '', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Auto Print --}}
    <script>
        window.print();
    </script>

</body>
</html>
