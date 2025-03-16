@extends('layouts.app')

@section('title', '申请发票')

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
                        <a href="{{ route('invoices.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 md:ml-2">
                            发票管理
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-gray-500 md:ml-2">申请发票</span>
                    </div>
                </li>
            </ol>
        </nav>
        
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h1 class="text-2xl font-bold mb-6">申请发票</h1>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    当前可开票金额：<span class="font-semibold">¥{{ number_format($invoiceableAmount, 2) }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <form action="{{ route('invoices.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">发票类型 <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="relative bg-white border rounded-lg p-4 flex flex-col cursor-pointer">
                                    <input type="radio" name="invoice_type" value="regular" class="sr-only" {{ old('invoice_type') == 'vat' ? '' : 'checked' }}>
                                    <div class="flex items-center">
                                        <div class="bg-blue-100 p-2 rounded-full">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </div>
                                        <span class="ml-3 font-medium text-gray-900">普通发票</span>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">适用于个人或不需要抵扣进项税的企业</p>
                                    <div class="absolute top-4 right-4 h-5 w-5 text-blue-600 border-2 border-gray-300 rounded-full check-indicator"></div>
                                </label>
                                
                                <label class="relative bg-white border rounded-lg p-4 flex flex-col cursor-pointer">
                                    <input type="radio" name="invoice_type" value="vat" class="sr-only" {{ old('invoice_type') == 'vat' ? 'checked' : '' }}>
                                    <div class="flex items-center">
                                        <div class="bg-purple-100 p-2 rounded-full">
                                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </div>
                                        <span class="ml-3 font-medium text-gray-900">增值税专票</span>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">适用于需要抵扣进项税的企业</p>
                                    <div class="absolute top-4 right-4 h-5 w-5 text-blue-600 border-2 border-gray-300 rounded-full check-indicator"></div>
                                </label>
                            </div>
                            @error('invoice_type')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="title" class="block text-gray-700 font-medium mb-2">发票抬头 <span class="text-red-500">*</span></label>
                            <input type="text" name="title" id="title" value="{{ old('title', $profile->company ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('title')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div id="tax-number-group" class="mb-4 {{ old('invoice_type') == 'vat' ? '' : 'hidden' }}">
                            <label for="tax_number" class="block text-gray-700 font-medium mb-2">税号 <span class="text-red-500">*</span></label>
                            <input type="text" name="tax_number" id="tax_number" value="{{ old('tax_number') }}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" {{ old('invoice_type') == 'vat' ? 'required' : '' }}>
                            <p class="text-sm text-gray-500 mt-1">增值税发票必须提供税号</p>
                            @error('tax_number')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="amount" class="block text-gray-700 font-medium mb-2">开票金额 <span class="text-red-500">*</span></label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">¥</span>
                                </div>
                                <input type="number" name="amount" id="amount" min="1" max="{{ $invoiceableAmount }}" step="0.01" value="{{ old('amount', $invoiceableAmount) }}" class="block w-full pl-7 pr-12 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">不能超过可开票金额 ¥{{ number_format($invoiceableAmount, 2) }}</p>
                            @error('amount')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 font-medium mb-2">接收邮箱 <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <p class="text-sm text-gray-500 mt-1">电子发票将发送到此邮箱</p>
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="notes" class="block text-gray-700 font-medium mb-2">备注</label>
                            <textarea name="notes" id="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white text-center font-medium rounded-md transition duration-300">
                                提交申请
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 处理发票类型选择的样式和逻辑
        const invoiceTypeRadios = document.querySelectorAll('input[name="invoice_type"]');
        const taxNumberGroup = document.getElementById('tax-number-group');
        const taxNumberInput = document.getElementById('tax_number');
        
        // 初始化选中状态的样式
        updateSelectedStyles(invoiceTypeRadios);
        
        invoiceTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                updateSelectedStyles(invoiceTypeRadios);
                
                // 显示/隐藏税号输入框
                if (this.value === 'vat') {
                    taxNumberGroup.classList.remove('hidden');
                    taxNumberInput.setAttribute('required', 'required');
                } else {
                    taxNumberGroup.classList.add('hidden');
                    taxNumberInput.removeAttribute('required');
                }
            });
        });
        
        // 辅助函数：更新选中样式
        function updateSelectedStyles(radios) {
            radios.forEach(radio => {
                const parent = radio.closest('label');
                const indicator = parent.querySelector('.check-indicator');
                
                if (radio.checked) {
                    parent.classList.add('border-blue-500');
                    indicator.classList.add('bg-blue-500');
                    indicator.classList.remove('border-gray-300');
                } else {
                    parent.classList.remove('border-blue-500');
                    indicator.classList.remove('bg-blue-500');
                    indicator.classList.add('border-gray-300');
                }
            });
        }
    });
</script>
@endpush
@endsection