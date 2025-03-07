<?php

// app/Http/Controllers/Api/PaymentWebhookController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\CryptoPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * Handle Alipay payment notifications
     */
    public function alipayNotify(Request $request)
    {
        Log::info('Alipay webhook received', $request->all());
        
        // Validate the incoming request based on Alipay's signature
        if (!$this->validateAlipaySignature($request)) {
            Log::warning('Invalid Alipay signature', [
                'ip' => $request->ip(),
                'request' => $request->all()
            ]);
            return response('fail', 400);
        }
        
        // Get the parameters
        $outTradeNo = $request->input('out_trade_no');
        $tradeStatus = $request->input('trade_status');
        $totalAmount = $request->input('total_amount');
        
        // Only process if payment is successful
        if ($tradeStatus !== 'TRADE_SUCCESS') {
            return response('success'); // Acknowledge but don't process
        }
        
        // Find the transaction
        $transaction = Transaction::where('reference_id', $outTradeNo)
            ->where('payment_method', 'alipay')
            ->where('status', 'pending')
            ->first();
        
        if (!$transaction) {
            Log::warning('Transaction not found in Alipay webhook', [
                'out_trade_no' => $outTradeNo
            ]);
            return response('success'); // Acknowledge but don't process further
        }
        
        // Process the payment
        $this->processPayment($transaction);
        
        return response('success');
    }
    
    /**
     * Handle WeChat Pay payment notifications
     */
    public function wechatNotify(Request $request)
    {
        Log::info('WeChat Pay webhook received', $request->all());
        
        // WeChat sends XML, so need to parse it
        $xml = simplexml_load_string($request->getContent(), 'SimpleXMLElement', LIBXML_NOCDATA);
        $data = json_decode(json_encode($xml), true);
        
        // Validate the incoming request based on WeChat's signature
        if (!$this->validateWechatSignature($data)) {
            Log::warning('Invalid WeChat signature', [
                'ip' => $request->ip(),
                'data' => $data
            ]);
            return response()->xml(['return_code' => 'FAIL', 'return_msg' => 'Invalid signature']);
        }
        
        // Check if payment is successful
        if ($data['result_code'] !== 'SUCCESS' || $data['return_code'] !== 'SUCCESS') {
            return response()->xml(['return_code' => 'SUCCESS', 'return_msg' => 'OK']);
        }
        
        // Find the transaction
        $outTradeNo = $data['out_trade_no'];
        $transaction = Transaction::where('reference_id', $outTradeNo)
            ->where('payment_method', 'wechat')
            ->where('status', 'pending')
            ->first();
        
        if (!$transaction) {
            Log::warning('Transaction not found in WeChat webhook', [
                'out_trade_no' => $outTradeNo
            ]);
            return response()->xml(['return_code' => 'SUCCESS', 'return_msg' => 'OK']);
        }
        
        // Process the payment
        $this->processPayment($transaction);
        
        return response()->xml(['return_code' => 'SUCCESS', 'return_msg' => 'OK']);
    }
    
    /**
     * Handle cryptocurrency payment notifications
     */
    public function cryptoNotify(Request $request)
    {
        Log::info('Crypto webhook received', $request->all());
        
        // Validate the incoming request based on API key
        $apiKey = config('services.crypto.webhook_key');
        if ($request->header('X-API-Key') !== $apiKey) {
            Log::warning('Invalid API key in crypto webhook', [
                'ip' => $request->ip(),
                'request' => $request->all()
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Get the parameters
        $txHash = $request->input('tx_hash');
        $walletAddress = $request->input('address');
        $amount = $request->input('amount');
        $currency = $request->input('currency');
        $confirmations = $request->input('confirmations', 0);
        
        // Find the crypto payment by wallet address
        $cryptoPayment = CryptoPayment::where('wallet_address', $walletAddress)
            ->where('status', 'pending')
            ->first();
        
        if (!$cryptoPayment) {
            Log::warning('Crypto payment not found in webhook', [
                'wallet_address' => $walletAddress
            ]);
            return response()->json(['success' => false, 'message' => 'Payment not found']);
        }
        
        // Check if confirmations are enough (depends on the currency)
        $requiredConfirmations = $this->getRequiredConfirmations($currency);
        if ($confirmations < $requiredConfirmations) {
            // Not enough confirmations yet, just update the record
            $cryptoPayment->tx_hash = $txHash;
            $cryptoPayment->save();
            
            return response()->json(['success' => true, 'status' => 'pending']);
        }
        
        // Check if amount is sufficient (with some tolerance for fees)
        $expectedAmount = $cryptoPayment->amount;
        $tolerance = 0.01; // 1% tolerance
        $minAmount = $expectedAmount * (1 - $tolerance);
        
        if ($amount < $minAmount) {
            Log::warning('Insufficient crypto payment amount', [
                'expected' => $expectedAmount,
                'received' => $amount,
                'wallet_address' => $walletAddress
            ]);
            
            // We still process it, but mark it for review
            $cryptoPayment->tx_hash = $txHash;
            $cryptoPayment->status = 'review';
            $cryptoPayment->save();
            
            return response()->json(['success' => true, 'status' => 'review']);
        }
        
        // Update crypto payment
        $cryptoPayment->tx_hash = $txHash;
        $cryptoPayment->status = 'confirmed';
        $cryptoPayment->confirmed_at = now();
        $cryptoPayment->save();
        
        // Process the associated transaction
        $transaction = Transaction::find($cryptoPayment->transaction_id);
        if ($transaction) {
            $this->processPayment($transaction);
        }
        
        return response()->json(['success' => true, 'status' => 'confirmed']);
    }
    
    /**
     * Process payment by updating transaction and user balance
     */
    private function processPayment(Transaction $transaction)
    {
        // Update transaction
        $transaction->status = 'completed';
        $transaction->save();
        
        // Update user balance
        $user = User::find($transaction->user_id);
        if ($user) {
            $user->balance += $transaction->amount;
            $user->save();
            
            Log::info('User balance updated after payment', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'amount' => $transaction->amount,
                'new_balance' => $user->balance
            ]);
        }
    }
    
    /**
     * Validate Alipay signature
     */
    private function validateAlipaySignature(Request $request)
    {
        // In a real implementation, you would:
        // 1. Sort all parameters alphabetically
        // 2. Create a string by concatenating key=value pairs
        // 3. Verify the signature using Alipay's public key
        
        // This is a simplified placeholder
        return true;
    }
    
    /**
     * Validate WeChat signature
     */
    private function validateWechatSignature(array $data)
    {
        // In a real implementation, you would:
        // 1. Remove the sign field
        // 2. Sort all parameters alphabetically
        // 3. Create a string by concatenating key=value pairs
        // 4. Append the API key
        // 5. Calculate MD5 and compare with the provided sign
        
        // This is a simplified placeholder
        return true;
    }
    
    /**
     * Get required confirmations based on currency
     */
    private function getRequiredConfirmations($currency)
    {
        $confirmations = [
            'BTC' => 3,
            'ETH' => 15,
            'USDT' => 15,
            'default' => 10
        ];
        
        return $confirmations[$currency] ?? $confirmations['default'];
    }
}