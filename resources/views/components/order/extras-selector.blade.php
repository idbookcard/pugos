{{-- resources/views/components/order/extras-selector.blade.php --}}
@if(isset($package->available_extras) && !empty($package->available_extras))
<div class="extras-section mt-4">
    <h4 class="text-lg font-semibold mb-2">额外选项</h4>
    <p class="text-gray-600 mb-3">选择额外服务以增强您的订单效果</p>
    
    <div class="extras-list grid grid-cols-1 gap-3">
        @foreach($package->available_extras as $extra)
            <div class="extra-item border rounded-lg p-3 hover:bg-gray-50 transition">
                <div class="flex items-start">
                    <div class="flex-1">
                        <div class="flex items-center">
                            @if($extra['is_multiple'] ?? false)
                                <input type="checkbox" name="extras[{{ $extra['id'] }}]" id="extra-{{ $extra['id'] }}" 
                                       class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" 
                                       value="1">
                            @else
                                <input type="radio" name="extras_selection" id="extra-{{ $extra['id'] }}" 
                                       class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" 
                                       value="{{ $extra['id'] }}">
                            @endif
                            <label for="extra-{{ $extra['id'] }}" class="ml-2 block text-sm font-medium text-gray-700">
                                {{ $extra['name'] ?? $extra['code'] }}
                            </label>
                            <span class="ml-auto text-indigo-600 font-semibold">
                                +{{ number_format($extra['price'] * 7.4 / 100 * 1.5, 2) }} 元
                            </span>
                        </div>
                        
                        @if(!empty($extra['code']))
                            <p class="text-xs text-gray-500 mt-1 ml-6">{{ $extra['code'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 额外选项价格计算
    const basePrice = parseFloat('{{ $package->price }}');
    let totalPrice = basePrice;
    
    const priceDisplay = document.getElementById('total-price-display');
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="extras"]');
    const radioButtons = document.querySelectorAll('input[type="radio"][name="extras_selection"]');
    
    // 复选框（多选）处理
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateTotalPrice);
    });
    
    // 单选按钮处理
    radioButtons.forEach(radio => {
        radio.addEventListener('change', updateTotalPrice);
    });
    
    function updateTotalPrice() {
        totalPrice = basePrice;
        
        // 计算多选额外选项
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const extraId = checkbox.id.replace('extra-', '');
                const extraPrice = parseFloat(getExtraPrice(extraId));
                totalPrice += extraPrice;
            }
        });
        
        // 计算单选额外选项
        const selectedRadio = document.querySelector('input[name="extras_selection"]:checked');
        if (selectedRadio) {
            const extraId = selectedRadio.value;
            const extraPrice = parseFloat(getExtraPrice(extraId));
            totalPrice += extraPrice;
        }
        
        // 更新价格显示
        if (priceDisplay) {
            priceDisplay.textContent = totalPrice.toFixed(2) + ' 元';
        }
    }
    
    function getExtraPrice(extraId) {
        // 从额外选项数据中获取价格
        const extras = @json($package->available_extras);
        const extra = extras.find(e => e.id == extraId);
        return extra ? (extra.price * 7.4 / 100 * 1.5) : 0;
    }
});
</script>
@endif