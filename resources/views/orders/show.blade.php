<x-app-layout>
    <x-page-title>Detail Pembelian</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <h3>Pembelian #{{ $order->id }}</h3>
        <p><strong>Supplier:</strong> {{ $order->supplier->name }}</p>
        @php
            \Carbon\Carbon::setLocale('id');
        @endphp
        <p><strong>Tanggal Pembelian:</strong> {{ \Carbon\Carbon::parse($order->order_date)->translatedFormat('j F Y') }}</p>
        <p><strong>Total Harga:</strong> Rp {{ number_format($order->total_amount, 0, '', '.') }}</p>

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
