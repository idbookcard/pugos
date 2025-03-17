@extends('layouts.app')

@section('title', '支付二维码')

@section('content')
<div class="bg-white py-6">
    <div class="container mx-auto px-4">
        <div class="max-w-lg mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <h1 class="text-2xl font-bold">扫码支付</h1>
                        <div>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                {{ $payment_method == 'wechat' ? '微信支付' : '支付宝' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="text-center my-8">
                        <div class="inline-block p-2 bg-white border border-gray-200 rounded-lg">
                            <img src="{{ $qrcode }}" alt="支付二维码" class="w-64 h-64">
                        </div>
                        <p class="mt-3 text-lg font-medium text-gray-900">请支付：¥{{ number_format($amount, 2) }}</p>
                        <p class="text-sm text-gray-500">请使用{{ $payment_method == 'wechat' ? '微信' : '支付宝' }}扫描二维码完成支付</p>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-5 mt-5">
                        <div class="text-center">
                            <div class="text-sm text-gray-500 mb-2">支付状态：
                                <span id="payment-status" class="font-medium text-yellow-600">等待支付</span>
                            </div>
                            <div id="countdown" class="mb-4">
                                <span class="text-sm text-gray-500">二维码有效期：</span>
                                <span id="timer" class="text-sm font-medium">05:00</span>
                            </div>
                            
                            <div class="flex space-x-4">
                                <a href="{{ route('wallet.index') }}" class="flex-1 py-2 px-4 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded-md transition duration-300">
                                    返回钱包
                                </a>
                                <button type="button" id="check-payment" class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition duration-300">
                                    已完成支付
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
                            <p>请在5分钟内完成支付，超时二维码将失效。</p>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            <p>支付成功后，余额将自动充值到您的账户。</p>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            <p>如支付成功后长时间未到账，请联系客服处理。</p>
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
        let timeLeft = 300; // 5分钟倒计时
        
        // 定时检查支付状态
        const statusChecker = setInterval(checkPaymentStatus, 5000);
        
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
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
    });
</script>
@endpush
@endsection