<!DOCTYPE html>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Print Order - Pesanan #{{ $order->id }}</title>
    <style type="text/css">
    /* Header */
        .header {
            position: relative;
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
            margin-top: 10px;
            margin-bottom: 20px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 8px 10px;
            text-align: center;
        }

        th {
            background-color: #f1f1f1;
        }

        .total-row td {
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h3 {
            margin: 5px 0;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            text-align: right;
            font-size: 14px;
            line-height: 1.5;
        }
        .username {
            margin-right: 35px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <img src="{{ asset('images/logo-dashboard2.png') }}" alt="Logo Apotek">
        <h1>Apotek Cahaya Dua</h1>
        <h4>Jl. Pasukan Ronggolawe No.9, Wonosobo Timur, Wonosobo Tim.,<br>
            Kec. Wonosobo, Kabupaten Wonosobo, Jawa Tengah 56311</h4>
    </div>

    <div class="double-line"></div>
    <div>
        <h4>Detail Pesanan #{{ $order->id }}</h4>
        <p><strong>Supplier:</strong> {{ $order->supplier->name }}</p>
        <p><strong>Tanggal Pesanan:</strong> {{ \Carbon\Carbon::parse($order->order_date)->locale('id')->isoFormat('D MMMM YYYY') }}</p>
        <p><strong>Total Harga:</strong> Rp {{ number_format($order->total_amount, 0, '', '.') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($order->orderDetails as $detail)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $detail->product->name }}</td>
                <td>{{ $detail->quantity }}</td>
                <td align="right">{{ 'Rp ' . number_format($detail->price, 0, '', '.') }}</td>
                <td align="right">{{ 'Rp ' . number_format($detail->total, 0, '', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        Wonosobo, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') }}<br><br>
        <div style="margin-top: 40px; text-align: right;">
            <div style="border-top: 1px solid #000; width: 200px; margin-left: auto; margin-right: 0;"></div>
            <strong class="username">{{ Auth::user()->name }}</strong>
        </div>
    </div>
    
    {{-- Auto Print --}}
    <script>
        window.print();
    </script>

</body>

</html>
