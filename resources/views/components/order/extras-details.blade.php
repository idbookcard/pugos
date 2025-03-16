{{-- resources/views/components/order/extras-details.blade.php --}}
@props(['order'])

@if(!empty($order->selected_extras) || !empty($order->extra_data))
    <div class="order-extras bg-white rounded-lg shadow-sm overflow-hidden mt-4">
        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b">
            <h3 class="text-lg leading-6 font-medium text-gray-900">额外选项详情</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">订单中包含的额外服务和选项</p>
        </div>
        
        <div class="px-4 py-5 sm:p-6">
            @php
                // 解析额外选项数据
                $selectedExtras = !empty($order->selected_extras) 
                    ? (is_array($order->selected_extras) ? $order->selected_extras : json_decode($order->selected_extras, true))
                    : [];
                    
                // 如果没有预处理的选项数据，尝试从extra_data解析
                if (empty($selectedExtras) && !empty($order->extra_data)) {
                    $extraData = is_array($order->extra_data) ? $order->extra_data : json_decode($order->extra_data, true);
                    
                    // 检查是否有可用的额外选项
                    if ($order->package && !empty($order->package->available_extras)) {
                        // 处理多选
                        if (isset($extraData['extras']) && is_array($extraData['extras'])) {
                            foreach ($extraData['extras'] as $extraId => $value) {
                                foreach ($order->package->available_extras as $extra) {
                                    if ($extra['id'] == $extraId) {
                                        $selectedExtras[] = $extra;
                                        break;
                                    }
                                }
                            }
                        }
                        
                        // 处理单选
                        if (isset($extraData['extras_selection']) && !empty($extraData['extras_selection'])) {
                            foreach ($order->package->available_extras as $extra) {
                                if ($extra['id'] == $extraData['extras_selection']) {
                                    $selectedExtras[] = $extra;
                                    break;
                                }
                            }
                        }
                    }
                }
            @endphp
            
            @if(!empty($selectedExtras))
                <div class="space-y-4">
                    @foreach($selectedExtras as $extra)
                        <div class="extra-item p-3 border rounded-md bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-700">
                                        {{ $extra['name'] ?? ($extra['code'] ?? '未命名选项') }}
                                    </h4>
                                    
                                    @if(!empty($extra['code']))
                                        <div class="mt-1 text-xs text-gray-500">代码: {{ $extra['code'] }}</div>
                                    @endif
                                </div>
                                
                                <div class="text-sm font-medium text-primary-600">
                                    @if(isset($extra['price']))
                                        +{{ number_format(floatval($extra['price']), 2) }} 元
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    <div class="pt-3 border-t mt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">额外选项总价:</span>
                            <span class="text-base font-medium text-primary-600">{{ $order->formatted_extras_amount ?? '计算中...' }}</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-4 text-gray-500">此订单没有选择额外选项</div>
            @endif
        </div>
    </div>
@endif