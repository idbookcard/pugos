{{-- resources/views/components/order/price-display.blade.php --}}
@props(['order'])

<div class="order-price">
    <div class="font-medium text-gray-900">
        {{ number_format($order->total_amount, 2) }} 元
    </div>
    
    @php
        // 检查是否有额外选项
        $hasExtras = !empty($order->selected_extras) || 
                    (!empty($order->extra_data) && $order->extra_data != '[]' && $order->extra_data != 'null');
                    
        // 计算额外选项价格
        $extrasAmount = 0;
        if (!empty($order->selected_extras)) {
            $selectedExtras = is_array($order->selected_extras) 
                            ? $order->selected_extras 
                            : json_decode($order->selected_extras, true);
                            
            if (is_array($selectedExtras)) {
                foreach ($selectedExtras as $extra) {
                    if (isset($extra['price'])) {
                        $extrasAmount += floatval($extra['price']);
                    }
                }
            }
        }
        
        // 计算基础价格
        $baseAmount = $order->total_amount - $extrasAmount;
    @endphp
    
    @if($hasExtras && $extrasAmount > 0)
        <div class="text-xs text-gray-500 mt-1">
            <div>基础: {{ number_format($baseAmount, 2) }} 元</div>
            <div>额外: +{{ number_format($extrasAmount, 2) }} 元</div>
        </div>
    @endif
</div>