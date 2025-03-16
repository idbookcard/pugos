@extends('layouts.app')

@section('title', '加密货币支付')

@section('content')
<div class="bg-white py-6">
    <div class="container mx-auto px-4">
        <div class="max-w-lg mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <h1 class="text-2xl font-bold">加密货币支付</h1>
                        <div>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                {{ $crypto_type }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="text-center my-8">
                        <div class="inline-block p-2 bg-white border border-gray-200 rounded-lg">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ $wallet_address }}" alt="钱包地址二维码" class="w-64 h-64">
                        </div>
                        <p class="mt-3 text-lg font-medium text-gray-900">请支付：{{ $crypto_amount }} {{ $crypto_type }}</p>
                        <p class="text-sm text-gray-500">≈ ¥{{ number_format($amount, 2) }}</p>
                        
                        <div class="mt-4">
                            <p class="text-sm text-gray-700 mb-1">钱包地址：</p>
                            <div class="flex items-center justify-center">
                                <input type="text" value="{{ $wallet_address }}" id="wallet-address" class="block w-full px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded-md bg-gray-50" readonly>
                                <button type="button" class="ml-2 p-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-md transition duration-300" onclick="copyAddress()">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </button>
                            </div>
                            <p id="copy-status" class="text-xs text-green-600 mt-1 hidden">已复制到剪贴板</p>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-5 mt-5">
                        <div class="text-center">
                            <div class="text-sm text-gray-500 mb-2">支付状态：
                                <span id="payment-status" class="font-medium text-yellow-600">等待转账确认</span>
                            </div>
                            <div id="countdown" class="mb-4">
                                <span class="text-sm text-gray-500">钱包地址有效期：</span>
                                <span id="timer" class="text-sm font-medium">120:00</span>
                            </div>
                            
                            <div class="flex space-x-4">
                                <a href="{{ route('wallet.index') }}" class="flex-1 py-2 px-4 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded-md transition duration-300">
                                    返回钱包
                                </a>
                                <button type="button" id="check-payment" class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition duration-300">
                                    已完成转账
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
                <div class="p-6">
                    <h2 class="text-lg font-semibold mb-4">支付说明</h2>
                    
                    <div class="space-y-4 text-sm text-gray-600">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            <p>请在2小时内完成支付，超时钱包地址将失效。</p>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            <p>支付成功后，系统将在区块链交易确认后（约10-30分钟）自动更新余额。</p>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            <p>请确保使用正确的{{ $crypto_type }}网络进行转账，否则可能导致资金丢失。</p>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            <p class="text-red-600 font-medium">注意：请务必转账确切的金额，避免四舍五入或修改金额。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const transactionId = {{ $transaction_id }};
        const paymentStatusEl = document.getElementById('payment-status');
        const checkPaymentBtn = document.getElementById('check-payment');
        const timerEl = document.getElementById('timer');
        
        let paid = false;
        let timeLeft = 7200; // 2小时倒计时
        
       // 定时检查支付状态
const statusChecker = setInterval(checkPaymentStatus, 30000); // 每30秒检查一次

// 开始倒计时
const countdown = setInterval(updateCountdown, 1000);

// 手动检查支付状态
checkPaymentBtn.addEventListener('click', function() {
    checkPaymentStatus();
});

// 检查支付状态
function checkPaymentStatus() {
    fetch(`{{ route('wallet.check-payment', '') }}/${transactionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.paid) {
                paid = true;
                paymentStatusEl.textContent = '支付成功';
                paymentStatusEl.classList.remove('text-yellow-600');
                paymentStatusEl.classList.add('text-green-600');
                
                clearInterval(statusChecker);
                clearInterval(countdown);
                
                // 延迟2秒后跳转到钱包页面
                setTimeout(() => {
                    window.location.href = '{{ route('wallet.index') }}';
                }, 2000);
            }
        })
        .catch(error => {
            console.error('检查支付状态出错:', error);
        });
}

// 更新倒计时
function updateCountdown() {
    if (timeLeft <= 0 || paid) {
        clearInterval(countdown);
        if (!paid) {
            timerEl.textContent = '已超时';
            timerEl.classList.add('text-red-600');
        }
        return;
    }
    
    timeLeft--;
    const hours = Math.floor(timeLeft / 3600);
    const minutes = Math.floor((timeLeft % 3600) / 60);
    const seconds = timeLeft % 60;
    timerEl.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

// 复制钱包地址到剪贴板
function copyAddress() {
    const addressInput = document.getElementById('wallet-address');
    const copyStatus = document.getElementById('copy-status');
    
    addressInput.select();
    document.execCommand('copy');
    
    copyStatus.classList.remove('hidden');
    setTimeout(() => {
        copyStatus.classList.add('hidden');
    }, 2000);
}
</script>
@endpush
@endsection