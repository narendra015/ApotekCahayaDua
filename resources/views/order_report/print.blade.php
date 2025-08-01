<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Laporan Data Pembelian Apotek Cahaya Dua. Menampilkan data pembelian periode tertentu secara lengkap dan terstruktur.">
    <meta name="author" content="Apotek Cahaya Dua">
    <title>Laporan Data Pembelian - Apotek Cahaya Dua</title>
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

        /* Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 10px;
        }
        th {
            background-color: #6861ce;
            color: #ffffff;
            text-align: center; /* Teks berada di tengah */
        }
        .total-row td {
            font-weight: bold;
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

        /* Custom Alignment for Total Pembelian */
        .total-row {
            font-weight: bold;
        }
        .total-row td {
            text-align: center;
        }
        .total-row td:first-child {
            text-align: center;
            padding-left: 15px;
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

    <!-- Judul Laporan -->
    <div style="text-align: center; margin-bottom: 20px;">
        <h3>Laporan Data Pembelian</h3>
        <h3>
            {{ \Carbon\Carbon::parse($startDate)->locale('id')->isoFormat('D MMMM YYYY') }}
            -
            {{ \Carbon\Carbon::parse($endDate)->locale('id')->isoFormat('D MMMM YYYY') }}
        </h3>        
    </div>

    <!-- Tabel Data Pembelian -->
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Pemasok</th>
                <th>Produk</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $totalAmount = 0;
            @endphp
            @foreach ($orders as $order)
                @foreach ($order->orderDetails as $detail)
                    <tr>
                        <td align="center">{{ $no++ }}</td>
                        <td align="center">{{ \Carbon\Carbon::parse($order->order_date)->locale('id')->isoFormat('D MMMM YYYY') }}</td>
                        <td align="center">{{ $order->supplier->name }}</td>
                        <td align="center">{{ $detail->product->name }}</td>
                        <td>{{ 'Rp ' . number_format($detail->price, 0, '', '.') }}</td>
                        <td align="center">{{ $detail->quantity }}</td>
                        <td>{{ 'Rp ' . number_format($detail->total, 0, '', '.') }}</td>
                    </tr>
                    @php
                        $totalAmount += $detail->total;
                    @endphp
                @endforeach
            @endforeach

            <!-- Total Pembelian -->
            @if ($totalAmount > 0)
            <tr class="total-row">
                <td colspan="6">Total Pembelian</td>
                <td>{{ 'Rp ' . number_format($totalAmount, 0, '', '.') }}</td>
            </tr>
            @endif
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
