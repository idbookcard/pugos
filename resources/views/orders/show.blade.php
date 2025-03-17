@extends('layouts.app')

@section('title', '订单详情 - ' . $order->order_number)

@section('content')
<div class="bg-white py-6">
    <div class="container mx-auto px-4">
        <nav class="flex mb-5" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600">
                        首页
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('orders.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 md:ml-2">
                            订单列表
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-gray-500 md:ml-2">订单详情</span>
                    </div>
                </li>
            </ol>
        </nav>
        
        <div class="flex flex-wrap -mx-4">
            <div class="w-full lg:w-2/3 px-4">
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h1 class="text-2xl font-bold">订单详情</h1>
                                <p class="text-gray-500 mt-1">订单号: {{ $order->order_number }}</p>
                            </div>
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                @if($order->status == 'completed') 
                                    bg-green-100 text-green-800
                                @elseif($order->status == 'processing')
                                    bg-blue-100 text-blue-800
                                @elseif($order->status == 'pending')
                                    bg-yellow-100 text-yellow-800
                                @elseif($order->status == 'canceled')
                                    bg-red-100 text-red-800
                                @else
                                    bg-gray-100 text-gray-800
                                @endif
                            ">
                                @if($order->status == 'completed')
                                    已完成
                                @elseif($order->status == 'processing')
                                    处理中
                                @elseif($order->status == 'pending')
                                    待处理
                                @elseif($order->status == 'canceled')
                                    已取消
                                @elseif($order->status == 'rejected')
                                    已拒绝
                                @else
                                    {{ $order->status }}
                                @endif
                            </span>
                        </div>
                        
                        <div class="border-t border-gray-200 mt-6 pt-6">
                            <div class="mb-6">
                                <h2 class="text-lg font-semibold mb-3">产品信息</h2>
                                <div class="bg-gray-50 rounded-lg p-4 flex items-start">
                                    <div class="bg-blue-100 text-blue-800 p-2 rounded">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="font-semibold text-gray-900">{{ $order->package->name }}</h3>
                                        <p class="text-gray-600 text-sm mt-1">{{ $order->package->description }}</p>
                                        <div class="mt-2 text-sm text-gray-500">
                                            <p>价格: ¥{{ $order->total_amount }}</p>
                                            <p>交付时间: {{ $order->package->delivery_days }}天</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-6">
                                <h2 class="text-lg font-semibold mb-3">订单详情</h2>
                                <div class="space-y-3">
                                    <div>
                                        <span class="text-gray-700 font-medium">目标网址:</span>
                                        <p class="mt-1"><a href="{{ $order->target_url }}" target="_blank" class="text-blue-600 hover:underline">{{ $order->target_url }}</a></p>
                                    </div>
                                    
                                    @if($order->keywords)
                                    <div>
                                        <span class="text-gray-700 font-medium">关键词:</span>
                                        <p class="mt-1">{{ $order->keywords }}</p>
                                    </div>
                                    @endif
                                    
                                    @if($order->article)
                                    <div>
                                        <span class="text-gray-700 font-medium">文章内容:</span>
                                        <div class="mt-1 p-3 bg-gray-50 rounded border border-gray-200 text-sm">
                                            {!! nl2br(e($order->article)) !!}
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @if($order->extra_data)
                                    <div>
                                        <span class="text-gray-700 font-medium">附加选项:</span>
                                        <ul class="mt-1 list-disc pl-5 text-sm">
                                            @foreach(json_decode($order->extra_data, true) ?? [] as $key => $value)
                                            <li>
                                                @if($key == 'dofollow' && $value)
                                                    Dofollow链接
                                                @elseif($key == 'fast_delivery' && $value)
                                                    加急处理
                                                @elseif($key == 'premium_content' && $value)
                                                    高级内容撰写
                                                @else
                                                    {{ $key }}: {{ $value }}
                                                @endif
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                    
                                    @if($order->notes)
                                    <div>
                                        <span class="text-gray-700 font-medium">订单备注:</span>
                                        <p class="mt-1">{{ $order->notes }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            @if($order->status == 'completed' && $report)
                            <div class="mb-6">
                                <h2 class="text-lg font-semibold mb-3">订单报告</h2>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        <h3 class="font-medium text-green-800">订单已完成</h3>
                                    </div>
                                    
                                    @php
                                        $reportData = json_decode($report->report_data, true) ?? [];
                                    @endphp
                                    
                                    @if(isset($reportData['file_path']))
                                    <a href="{{ asset('storage/' . $reportData['file_path']) }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition duration-300" target="_blank">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        下载报告
                                    </a>
                                    @endif
                                    
                                    @if(isset($reportData['notes']))
                                    <div class="mt-3 text-green-700">
                                        <p class="text-sm">{{ $reportData['notes'] }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            @if($order->apiOrder && $order->apiOrder->api_order_id)
                            <div class="mb-6">
                                <h2 class="text-lg font-semibold mb-3">API 订单信息</h2>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <span class="text-gray-700 font-medium mr-2">API订单ID:</span>
                                            <span>{{ $order->apiOrder->api_order_id }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="text-gray-700 font-medium mr-2">API状态:</span>
                                            <span class="px-2 py-1 inline-flex text-xs leading-4 font-semibold rounded-full 
                                                @if($order->apiOrder->api_status == 'completed') 
                                                    bg-green-100 text-green-800
                                                @elseif($order->apiOrder->api_status == 'processing' || $order->apiOrder->api_status == 'submitted')
                                                    bg-blue-100 text-blue-800
                                                @elseif($order->apiOrder->api_status == 'failed')
                                                    bg-red-100 text-red-800
                                                @else
                                                    bg-gray-100 text-gray-800
                                                @endif
                                            ">
                                                {{ $order->apiOrder->api_status ?? '未知' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="text-gray-700 font-medium mr-2">提交时间:</span>
                                            <span>{{ $order->apiOrder->submitted_at ? $order->apiOrder->submitted_at->format('Y-m-d H:i:s') : '未提交' }}</span>
                                        </div>
                                        @if($order->apiOrder->completed_at)
                                        <div class="flex items-center">
                                            <span class="text-gray-700 font-medium mr-2">完成时间:</span>
                                            <span>{{ $order->apiOrder->completed_at->format('Y-m-d H:i:s') }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if(count($statusLogs) > 0)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-3">订单状态日志</h2>
                        <div class="space-y-4">
                            @foreach($statusLogs as $log)
                            <div class="relative pl-8 pb-4 
                                @if(!$loop->last) border-l-2 border-gray-200 @endif
                            ">
                                <div class="absolute top-0 left-0 w-4 h-4 -ml-2 rounded-full bg-blue-500"></div>
                                <div>
                                    <p class="font-medium text-gray-900">
                                        @if($log->old_status == 'pending' && $log->new_status == 'processing')
                                            订单开始处理
                                        @elseif($log->new_status == 'completed')
                                            订单已完成
                                        @elseif($log->new_status == 'canceled')
                                            订单已取消
                                        @elseif($log->new_status == 'rejected')
                                            订单被拒绝
                                        @else
                                            状态从 {{ $log->old_status ?: '创建' }} 变更为 {{ $log->new_status }}
                                        @endif
                                    </p>
                                    @if($log->notes)
                                    <p class="text-sm text-gray-600 mt-1">{{ $log->notes }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500 mt-1">{{ $log->created_at->format('Y-m-d H:i:s') }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="w-full lg:w-1/3 px-4">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4">订单摘要</h2>
                    
                    <div class="border-t border-gray-200 py-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">下单时间</span>
                            <span class="font-medium">{{ $order->created_at->format('Y-m-d H:i:s') }}</span>
                        </div>
                        
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">支付时间</span>
                            <span class="font-medium">{{ $order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : '未支付' }}</span>
                        </div>
                        
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">订单金额</span>
                            <span class="font-medium">¥{{ $order->total_amount }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">预计交付</span>
                            <span class="font-medium">
                                @if($order->status == 'completed')
                                    已交付
                                @elseif($order->paid_at)
                                    {{ $order->paid_at->addDays($order->package->delivery_days)->format('Y-m-d') }}
                                @else
                                    付款后 {{ $order->package->delivery_days }} 天
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        @if($order->status == 'pending')
                        <button type="button" class="w-full py-2 px-4 bg-red-600 hover:bg-red-700 text-white text-center font-medium rounded-md transition duration-300" onclick="confirmCancel()">
                            取消订单
                        </button>
                        <form id="cancel-form" action="{{ route('orders.cancel', $order->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('PATCH')
                        </form>
                        @endif
                        
                        @if($order->status == 'completed')
                        <a href="#" class="block w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white text-center font-medium rounded-md transition duration-300 mb-3">
                            查看报告
                        </a>
                        <a href="{{ route('packages.show', $order->package->slug) }}" class="block w-full py-2 px-4 bg-green-600 hover:bg-green-700 text-white text-center font-medium rounded-md transition duration-300">
                            再次购买
                        </a>
                        @endif
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4">需要帮助？</h2>
                    <p class="text-gray-600 mb-4">如果您对订单有任何疑问，可以联系我们的客服团队。</p>
                    <a href="#" class="block w-full py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-800 text-center font-medium rounded-md transition duration-300">
                        联系客服
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmCancel() {
        if (confirm('确定要取消此订单吗？此操作不可逆。')) {
            document.getElementById('cancel-form').submit();
        }
    }
</script>
@endpush
@endsection