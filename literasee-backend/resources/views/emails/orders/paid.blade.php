{{-- resources/views/emails/orders/paid.blade.php --}}

<x-mail::message>
# Halo, {{ $order->user->name }}

Terima kasih! Pembayaran untuk pesanan **#{{ $order->order_number }}** telah kami terima.

<x-mail::table>
| Buku | Qty | Harga |
|:-----|:---:|:------|
@foreach($order->items as $item)
| {{ $item->book_title }} | {{ $item->quantity }} | Rp {{ number_format($item->price, 0, ',', '.') }} |
@endforeach
| **Total** | | **Rp {{ number_format($order->total_amount, 0, ',', '.') }}** |
</x-mail::table>

<x-mail::button :url="config('app.url')">
Lihat Pesanan
</x-mail::button>

Salam,<br>
{{ config('app.name') }}
</x-mail::message>