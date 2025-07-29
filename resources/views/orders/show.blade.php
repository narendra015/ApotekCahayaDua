<x-app-layout>
    <x-page-title>Detail Pembelian</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <h3>Pembelian #{{ $order->id }}</h3>
        <table class="table table-borderless w-auto">
            <tr>
                <th>Supplier</th>
                <td>: {{ $order->supplier->name }}</td>
            </tr>
            <tr>
                <th>Tanggal Pembelian</th>
                <td>: {{ \Carbon\Carbon::parse($order->order_date)->locale('id')->isoFormat('D MMMM YYYY') }}</td>
            </tr>
            <tr>
                <th>Total Harga</th>
                <td>: Rp {{ number_format($order->total_amount, 0, '', '.') }}</td>
            </tr>
        </table>

        <h4>Detail Pembelian</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->orderDetails as $detail)
                    <tr>
                        <td>{{ $detail->product->name }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>Rp {{ number_format($detail->price, 0, '', '.') }}</td>
                        <td>Rp {{ number_format($detail->total, 0, '', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div>
        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Kembali ke Daftar Pembelian</a>
        <a class="btn btn-warning py-2 px-3" target="_blank" href="{{ route('orders.print', $order->id) }}">
            <i class="ti ti-printer me-2"></i> Cetak Pembelian
        </a>               
    </div>
</x-app-layout>
