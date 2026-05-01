<div class="space-y-3 p-4">
    @forelse ($order->items as $item)
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <span class="font-medium text-gray-800">
                {{ $item->product?->name ?? 'منتج محذوف' }}
            </span>
            <span class="text-sm text-gray-500">
                الكمية: {{ $item->quantity }}
            </span>
            <span class="text-sm font-semibold text-rose-600">
                {{ $item->price }} د.ع
            </span>
        </div>
    @empty
        <p class="text-center text-gray-400 py-4">لا توجد منتجات في هذا الطلب</p>
    @endforelse

    {{-- المجموع --}}
    @if($order->items->count() > 0)
        <div class="flex justify-between pt-3 font-bold">
            <span>المجموع الكلي</span>
            <span class="text-rose-600">{{ $order->total_price }} د.ع</span>
        </div>
    @endif
</div>