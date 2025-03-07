// resources/views/customer/wallet/index.blade.php
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h2 class="mb-4">{{ __('My Wallet') }}</h2>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <h5 class="card-title">{{ __('Current Balance') }}</h5>
                            <h2 class="text-primary">{{ $user->formatted_balance }}</h2>
                            <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#depositModal">
                                {{ __('Add Funds') }}
                            </button>
                        </div>
                    </div>
                    
                    @if(count($pendingCryptoPayments) > 0)
                    <div class="card mb-4">
                        <div class="card-header">{{ __('Pending Crypto Payments') }}</div>
                        <div class="card-body">
                            @foreach($pendingCryptoPayments as $payment)
                            <div class="mb-3 p-3 border rounded">
                                <h6>{{ $payment->amount }} {{ $payment->currency }}</h6>
                                <p class="mb-1">
                                    <strong>{{ __('Network') }}:</strong> {{ $payment->network }}
                                </p>
                                <p class="mb-1">
                                    <strong>{{ __('Address') }}:</strong> 
                                    <small class="text-break">{{ $payment->wallet_address }}</small>
                                </p>
                                <p class="mb-0">
                                    <strong>{{ __('Expires') }}:</strong> 
                                    {{ $payment->expires_at->format('Y-m-d H:i') }}
                                </p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">{{ __('Transaction History') }}</div>
                        <div class="card-body">
                            @if(count($transactions) > 0)
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Method') }}</th>
                                            <th>{{ __('Reference') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                            <td>{{ ucfirst($transaction->transaction_type) }}</td>
                                            <td>{{ ucfirst($transaction->payment_method) }}</td>
                                            <td>{{ $transaction->reference_id }}</td>
                                            <td class="{{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $transaction->amount > 0 ? '+' : '' }}@price($transaction->amount)
                                            </td>
                                            <td>@statusBadge($transaction->status)</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center">
                                {{ $transactions->links() }}
                            </div>
                            @else
                            <p class="text-center">{{ __('No transactions found.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deposit Modal -->
<div class="modal fade" id="depositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('customer.wallet.deposit') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="depositModalLabel">{{ __('Add Funds to Wallet') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">{{ __('Amount') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" class="form-control" id="amount" name="amount" min="10" step="1" required>
                        </div>
                        <div class="form-text">{{ __('Minimum deposit amount: ¥10') }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('Payment Method') }}</label>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="methodWechat" value="wechat" checked>
                                <label class="form-check-label" for="methodWechat">
                                    <i class="fab fa-weixin text-success"></i> {{ __('WeChat Pay') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="methodAlipay" value="alipay">
                                <label class="form-check-label" for="methodAlipay">
                                    <i class="fab fa-alipay text-primary"></i> {{ __('Alipay') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="methodCrypto" value="crypto">
                                <label class="form-check-label" for="methodCrypto">
                                    <i class="fab fa-bitcoin text-warning"></i> {{ __('Cryptocurrency') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div id="cryptoOptions" class="mb-3 d-none">
                        <label for="crypto_currency" class="form-label">{{ __('Select Cryptocurrency') }}</label>
                        <select class="form-select" id="crypto_currency" name="crypto_currency">
                            <option value="USDT">Tether (USDT)</option>
                            <option value="BTC">Bitcoin (BTC)</option>
                            <option value="ETH">Ethereum (ETH)</option>
                        </select>
                        
                        <div class="mt-2">
                            <label for="crypto_network" class="form-label">{{ __('Network') }}</label>
                            <select class="form-select" id="crypto_network" name="crypto_network">
                                <option value="TRC20">TRON (TRC20)</option>
                                <option value="ERC20">Ethereum (ERC20)</option>
                                <option value="BEP20">Binance Smart Chain (BEP20)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Proceed to Payment') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const methodCrypto = document.getElementById('methodCrypto');
        const cryptoOptions = document.getElementById('cryptoOptions');
        
        // Show/hide crypto options based on payment method selection
        document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.value === 'crypto') {
                    cryptoOptions.classList.remove('d-none');
                } else {
                    cryptoOptions.classList.add('d-none');
                }
            });
        });
        
        // Update network options based on selected cryptocurrency
        const cryptoCurrency = document.getElementById('crypto_currency');
        const cryptoNetwork = document.getElementById('crypto_network');
        
        cryptoCurrency.addEventListener('change', function() {
            // Clear current options
            cryptoNetwork.innerHTML = '';
            
            // Add appropriate network options
            if (this.value === 'USDT') {
                addOption(cryptoNetwork, 'TRC20', 'TRON (TRC20)');
                addOption(cryptoNetwork, 'ERC20', 'Ethereum (ERC20)');
                addOption(cryptoNetwork, 'BEP20', 'Binance Smart Chain (BEP20)');
            } else if (this.value === 'BTC') {
                addOption(cryptoNetwork, 'Bitcoin', 'Bitcoin Network');
            } else if (this.value === 'ETH') {
                addOption(cryptoNetwork, 'ERC20', 'Ethereum Network');
            }
        });
        
        function addOption(select, value, text) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = text;
            select.appendChild(option);
        }
    });
</script>
@endpush
@endsection