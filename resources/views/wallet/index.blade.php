@extends('layouts.app')

@section('title', '我的钱包')

@section('content')
<div class="bg-white py-6">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap -mx-4">
            <div class="w-full lg:w-2/3 px-4">
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="p-6">
                        <h1 class="text-2xl font-bold mb-6">我的钱包</h1>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-blue-50 rounded-lg p-6 border border-blue-100">
                                <h2 class="text-lg font-semibold text-blue-800 mb-2">账户余额</h2>
                                <div class="flex items-end">
                                    <span class="text-3xl font-bold text-blue-600">¥{{ number_format(auth()->user()->balance, 2) }}</span>
                                    @if(auth()->user()->gift_balance > 0)
                                    <span class="text-sm text-blue-500 ml-2 mb-1">+ ¥{{ number_format(auth()->user()->gift_balance, 2) }} (赠送)</span>
                                    @endif
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('wallet.deposit') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition duration-300">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        充值
                                    </a>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-800 mb-2">账户总览</h2>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">累计充值</span>
                                        <span class="font-medium">¥{{ number_format($totalRecharge, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">累计消费</span>
                                        <span class="font-medium">¥{{ number_format($totalConsumption, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">已开票金额</span>
                                        <span class="font-medium">¥{{ number_format($invoicedAmount, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">可开票金额</span>
                                        <span class="font-medium">¥{{ number_format($invoiceableAmount, 2) }}</span>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition duration-300">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        申请发票
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8">
                            <h2 class="text-lg font-semibold mb-4">最近交易</h2>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                交易时间
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                类型
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                金额
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                余额
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                描述
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse ($walletTransactions as $transaction)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $transaction->created_at->format('Y-m-d H:i:s') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($transaction->type == 'recharge') 
                                                            bg-green-100 text-green-800
                                                        @elseif($transaction->type == 'consumption')
                                                            bg-red-100 text-red-800
                                                        @elseif($transaction->type == 'refund')
                                                            bg-blue-100 text-blue-800
                                                        @elseif($transaction->type == 'gift')
                                                            bg-purple-100 text-purple-800
                                                        @endif
                                                    ">
                                                        {{ $transaction->type_name }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm 
    @if($transaction->amount > 0)
        text-green-600 font-medium
    @else
        text-red-600
    @endif
">
    {{ $transaction->amount_display }}
</td>
<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
    {{ number_format($transaction->after_balance, 2) }}
</td>
<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
    {{ $transaction->description }}
    @if($transaction->related_description)
    <a href="#" class="text-blue-600 hover:text-blue-900">{{ $transaction->related_description }}</a>
    @endif
</td>
</tr>
@empty
<tr>
<td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
    暂无交易记录
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="mt-4">
    {{ $walletTransactions->links() }}
</div>
</div>
</div>
</div>

<div class="w-full lg:w-1/3 px-4">
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4">充值方式</h2>
            
            <div class="space-y-4">
                <a href="{{ route('wallet.deposit', ['payment_method' => 'wechat']) }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-2 rounded">
                            <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" fill="currentColor"><path d="M417.707 608.485c-30.292 0-54.857-24.716-54.857-55.079 0-30.395 24.562-55.077 54.857-55.077 30.293 0 54.858 24.685 54.858 55.077 0 30.363-24.566 55.079-54.858 55.079zM646.315 498.331c30.292 0 54.86 24.685 54.86 55.077 0 30.363-24.568 55.079-54.86 55.079-30.293 0-54.857-24.716-54.857-55.079-.001-30.395 24.563-55.077 54.857-55.077z"/><path d="M735.783 387.291c0-133.101-133.387-241.63-283.327-241.63-149.95 0-283.327 108.529-283.327 241.63 0 133.1 133.375 241.598 283.327 241.598 33.274 0 66.805-8.341 100.082-16.682l91.682 50.097-25.024-83.214c66.805-50.16 116.589-108.466 116.589-191.796h-.001zM514.273 472.233c-12.117 0-25.025-12.114-25.025-25.028 0-12.117 12.908-25.024 25.025-25.024 16.908 0 29.571 12.909 29.571 25.024-.001 12.913-12.664 25.028-29.571 25.028zm137.893 0c-12.116 0-25.024-12.114-25.024-25.028 0-12.117 12.909-25.024 25.024-25.024 16.908 0 29.571 12.909 29.571 25.024 0 12.913-12.661 25.028-29.571 25.028z"/><path d="M855.704 613.104c0-83.366-83.366-150.106-175.047-150.106-99.957 0-175.049 66.742-175.049 150.106 0 83.366 75.089 150.105 175.049 150.105 20.912 0 41.795-8.342 62.649-16.684l58.438 33.368-16.684-58.438c41.795-33.398 70.647-66.742 70.647-108.351h-.003zm-233.429-25.024c-8.342 0-16.684-8.343-16.684-16.684 0-8.343 8.341-16.684 16.684-16.684 12.503 0 20.912 8.34 20.912 16.684.001 8.341-8.407 16.684-20.912 16.684zm116.588 0c-8.34 0-16.682-8.343-16.682-16.684 0-8.343 8.342-16.684 16.682-16.684 12.504 0 20.913 8.34 20.913 16.684.001 8.341-8.408 16.684-20.913 16.684z"/></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-medium text-gray-900">微信支付</h3>
                            <p class="text-sm text-gray-500">使用微信扫码支付，即时到账</p>
                        </div>
                    </div>
                </a>
                
                <a href="{{ route('wallet.deposit', ['payment_method' => 'alipay']) }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-2 rounded">
                            <svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" fill="currentColor"><path d="M417.707 608.485c-30.292 0-54.857-24.716-54.857-55.079 0-30.395 24.562-55.077 54.857-55.077 30.293 0 54.858 24.685 54.858 55.077 0 30.363-24.566 55.079-54.858 55.079zM646.315 498.331c30.292 0 54.86 24.685 54.86 55.077 0 30.363-24.568 55.079-54.86 55.079-30.293 0-54.857-24.716-54.857-55.079-.001-30.395 24.563-55.077 54.857-55.077z"/><path d="M735.783 387.291c0-133.101-133.387-241.63-283.327-241.63-149.95 0-283.327 108.529-283.327 241.63 0 133.1 133.375 241.598 283.327 241.598 33.274 0 66.805-8.341 100.082-16.682l91.682 50.097-25.024-83.214c66.805-50.16 116.589-108.466 116.589-191.796h-.001zM514.273 472.233c-12.117 0-25.025-12.114-25.025-25.028 0-12.117 12.908-25.024 25.025-25.024 16.908 0 29.571 12.909 29.571 25.024-.001 12.913-12.664 25.028-29.571 25.028zm137.893 0c-12.116 0-25.024-12.114-25.024-25.028 0-12.117 12.909-25.024 25.024-25.024 16.908 0 29.571 12.909 29.571 25.024 0 12.913-12.661 25.028-29.571 25.028z"/><path d="M855.704 613.104c0-83.366-83.366-150.106-175.047-150.106-99.957 0-175.049 66.742-175.049 150.106 0 83.366 75.089 150.105 175.049 150.105 20.912 0 41.795-8.342 62.649-16.684l58.438 33.368-16.684-58.438c41.795-33.398 70.647-66.742 70.647-108.351h-.003zm-233.429-25.024c-8.342 0-16.684-8.343-16.684-16.684 0-8.343 8.341-16.684 16.684-16.684 12.503 0 20.912 8.34 20.912 16.684.001 8.341-8.407 16.684-20.912 16.684zm116.588 0c-8.34 0-16.682-8.343-16.682-16.684 0-8.343 8.342-16.684 16.682-16.684 12.504 0 20.913 8.34 20.913 16.684.001 8.341-8.408 16.684-20.913 16.684z"/></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-medium text-gray-900">支付宝</h3>
                            <p class="text-sm text-gray-500">使用支付宝扫码支付，即时到账</p>
                        </div>
                    </div>
                </a>
                
                <a href="{{ route('wallet.deposit', ['payment_method' => 'crypto']) }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300">
                    <div class="flex items-center">
                        <div class="bg-yellow-100 p-2 rounded">
                            <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.944 17.97L4.58 13.62 11.943 24l7.37-10.38-7.372 4.35h.003zM12.056 0L4.69 12.223l7.365 4.354 7.365-4.35L12.056 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-medium text-gray-900">加密货币</h3>
                            <p class="text-sm text-gray-500">支持USDT、BTC、ETH等加密货币支付</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4">常见问题</h2>
            
            <div class="space-y-4">
                <div>
                    <h3 class="font-medium text-gray-900 mb-1">充值后多久到账？</h3>
                    <p class="text-sm text-gray-600">使用微信支付和支付宝充值一般即时到账，加密货币需要等待网络确认，一般10-30分钟。</p>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-900 mb-1">如何申请发票？</h3>
                    <p class="text-sm text-gray-600">在"发票管理"页面申请发票，仅充值金额可开具发票，赠送金额不可开发票。</p>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-900 mb-1">账户余额有效期？</h3>
                    <p class="text-sm text-gray-600">账户余额长期有效，没有过期时间。</p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
@endsection