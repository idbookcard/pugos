<?php

// app/Services/PaymentService.php
namespace App\Services;

use App\Models\Transaction;
use App\Models\CryptoPayment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentService
{
    protected $wechatPayService;
    protected $alipayService;
    
    public function __construct(WechatPayService $wechatPayService, AlipayService $alipayService)
    {
        $this->wechatPayService = $wechatPayService;
        $this->alipayService = $alipayService;
    }
    
    /**
     * Process deposit request and return payment information
     *
     * @param array $data
     * @param \App\Models\User $user
     * @return array
     */
    public function processDeposit($data, $user)
    {
        // Validate common parameters
        if (!isset($data['amount']) || !isset($data['payment_method'])) {
            throw new \InvalidArgumentException('Amount and payment method are required');
        }
        
        $amount = (float) $data['amount'];
        $method = $data['payment_method'];
        
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero');
        }
        
        // Generate reference ID for this transaction
        $referenceId = 'D' . date('YmdHis') . strtoupper(Str::random(6));
        
        // Create transaction record
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'transaction_type' => 'deposit',
            'amount' => $amount,
            'payment_method' => $method,
            'status' => 'pending',
            'reference_id' => $referenceId,
            'notes' => "Deposit via {$method}"
        ]);
        
        // Process based on payment method
        switch ($method) {
            case 'wechat':
                return $this->processWechatDeposit($transaction, $data);
            
            case 'alipay':
                return $this->processAlipayDeposit($transaction, $data);
            
            case 'crypto':
                return $this->processCryptoDeposit($transaction, $data);
            
            default:
                throw new \InvalidArgumentException("Unsupported payment method: {$method}");
        }
    }
    
    /**
     * Process WeChat Pay deposit
     *
     * @param Transaction $transaction
     * @param array $data
     * @return array
     */
    protected function processWechatDeposit(Transaction $transaction, $data)
    {
        try {
            // Generate payment data using WeChat Pay service
            $paymentData = $this->wechatPayService->generatePayment([
                'out_trade_no' => $transaction->reference_id,
                'total_fee' => $transaction->amount * 100, // Convert to cents
                'body' => 'Deposit to account',
                'notify_url' => route('api.webhooks.wechat')
            ]);
            
            // Update transaction with payment details
            $transaction->payment_details = [
                'qr_code' => $paymentData['code_url'] ?? null,
                'prepay_id' => $paymentData['prepay_id'] ?? null,
                'generated_at' => Carbon::now()->toDateTimeString()
            ];
            $transaction->save();
            
            return [
                'success' => true,
                'transaction_id' => $transaction->id,
                'qr_code' => $paymentData['code_url'] ?? null,
                'amount' => $transaction->amount,
                'reference' => $transaction->reference_id,
                'expires_at' => Carbon::now()->addHours(2)->toDateTimeString()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to process WeChat payment', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
            
            // Update transaction status
            $transaction->status = 'failed';
            $transaction->notes .= ' | Error: ' . $e->getMessage();
            $transaction->save();
            
            throw $e;
        }
    }
    
    /**
     * Process Alipay deposit
     *
     * @param Transaction $transaction
     * @param array $data
     * @return array
     */
    protected function processAlipayDeposit(Transaction $transaction, $data)
    {
        try {
            // Generate payment data using Alipay service
            $paymentData = $this->alipayService->generatePayment([
                'out_trade_no' => $transaction->reference_id,
                'total_amount' => $transaction->amount,
                'subject' => 'Deposit to account',
                'notify_url' => route('api.webhooks.alipay')
            ]);
            
            // Update transaction with payment details
            $transaction->payment_details = [
                'qr_code' => $paymentData['qr_code'] ?? null,
                'payment_url' => $paymentData['payment_url'] ?? null,
                'generated_at' => Carbon::now()->toDateTimeString()
            ];
            $transaction->save();
            
            return [
                'success' => true,
                'transaction_id' => $transaction->id,
                'qr_code' => $paymentData['qr_code'] ?? null,
                'payment_url' => $paymentData['payment_url'] ?? null,
                'amount' => $transaction->amount,
                'reference' => $transaction->reference_id,
                'expires_at' => Carbon::now()->addHours(2)->toDateTimeString()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to process Alipay payment', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
            
            // Update transaction status
            $transaction->status = 'failed';
            $transaction->notes .= ' | Error: ' . $e->getMessage();
            $transaction->save();
            
            throw $e;
        }
    }
    
    /**
     * Process cryptocurrency deposit
     *
     * @param Transaction $transaction
     * @param array $data
     * @return array
     */
    protected function processCryptoDeposit(Transaction $transaction, $data)
    {
        // Validate crypto parameters
        if (!isset($data['crypto_currency'])) {
            throw new \InvalidArgumentException('Cryptocurrency type is required');
        }
        
        $currency = $data['crypto_currency'];
        $network = $data['crypto_network'] ?? $this->getDefaultNetwork($currency);
        
        // Create a unique wallet address (in a real-world scenario, this would call a crypto payment gateway API)
        $walletAddress = $this->generateWalletAddress($currency, $network);
        
        // Calculate crypto amount based on current exchange rate
        $cryptoAmount = $this->calculateCryptoAmount($transaction->amount, $currency);
        
        // Create crypto payment record
        $cryptoPayment = CryptoPayment::create([
            'user_id' => $transaction->user_id,
            'transaction_id' => $transaction->id,
            'currency' => $currency,
            'network' => $network,
            'amount' => $cryptoAmount,
            'amount_usd' => $transaction->amount,
            'wallet_address' => $walletAddress,
            'status' => 'pending',
            'expires_at' => Carbon::now()->addHours(2)
        ]);
        
        // Update transaction with payment details
        $transaction->payment_details = [
            'crypto_payment_id' => $cryptoPayment->id,
            'currency' => $currency,
            'network' => $network,
            'wallet_address' => $walletAddress,
            'crypto_amount' => $cryptoAmount,
            'generated_at' => Carbon::now()->toDateTimeString()
        ];
        $transaction->save();
        
        return [
            'success' => true,
            'transaction_id' => $transaction->id,
            'crypto_payment_id' => $cryptoPayment->id,
            'currency' => $currency,
            'network' => $network,
            'amount' => $transaction->amount,
            'crypto_amount' => $cryptoAmount,
            'wallet_address' => $walletAddress,
            'reference' => $transaction->reference_id,
            'expires_at' => Carbon::now()->addHours(2)->toDateTimeString()
        ];
    }
    
    /**
     * Get default network for cryptocurrency
     *
     * @param string $currency
     * @return string
     */
    protected function getDefaultNetwork($currency)
    {
        $networks = [
            'BTC' => 'Bitcoin',
            'ETH' => 'ERC20',
            'USDT' => 'TRC20'
        ];
        
        return $networks[$currency] ?? 'unknown';
    }
    
    /**
     * Generate wallet address for cryptocurrency
     * In a real implementation, this would call a crypto payment gateway API
     *
     * @param string $currency
     * @param string $network
     * @return string
     */
    protected function generateWalletAddress($currency, $network)
    {
        // Simulate generating a wallet address
        // In production, this would integrate with a cryptocurrency payment processor
        return strtolower($currency) . '-' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $network)) . '-' . Str::random(24);
    }
    
    /**
     * Calculate cryptocurrency amount based on fiat amount
     * In a real implementation, this would call an exchange rate API
     *
     * @param float $fiatAmount
     * @param string $currency
     * @return float
     */
    protected function calculateCryptoAmount($fiatAmount, $currency)
    {
        // Sample exchange rates (in production, fetch from an API)
        $rates = [
            'BTC' => 60000.00,   // 1 BTC = $60,000
            'ETH' => 3000.00,    // 1 ETH = $3,000
            'USDT' => 1.00       // 1 USDT = $1 (stable)
        ];
        
        $rate = $rates[$currency] ?? 1.00;
        
        // Calculate amount with 8 decimal precision for cryptocurrencies
        return round($fiatAmount / $rate, 8);
    }
}