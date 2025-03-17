<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\Payments\PaymentFactory;
use App\Services\AccountService;
use Illuminate\Http\Request;
use App\Http\Requests\DepositRequest;

class WalletController extends Controller
{
    protected $accountService;
    
    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
        $this->middleware('auth');
    }
    
    /**
     * 显示钱包页面
     */
    public function index()
    {
        $user = auth()->user();
        
        // 获取最近的交易记录
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // 获取钱包余额变动记录
        $walletTransactions = \App\Models\WalletTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('wallet.index', compact('user', 'transactions', 'walletTransactions'));
    }
    
    /**
     * 显示充值页面
     */
    public function deposit()
    {
        return view('wallet.deposit');
    }
    
    /**
     * 处理充值请求
     */
    public function processDeposit(DepositRequest $request)
    {
        $amount = $request->input('amount');
        $paymentMethod = $request->input('payment_method');
        
        try {
            // 获取支付服务
            $paymentService = PaymentFactory::create($paymentMethod);
            
            // 创建支付
            $result = $paymentService->createPayment($amount, auth()->id(), '账户充值');
            
            if (!$result['success']) {
                return back()->withInput()
                    ->with('error', $result['message'] ?? '创建支付失败');
            }
            
            // 根据支付方式返回不同的视图
            if ($paymentMethod == 'wechat' || $paymentMethod == 'alipay') {
                return view('wallet.qrcode', [
                    'transaction_id' => $result['transaction_id'],
                    'amount' => $amount,
                    'qrcode' => $result['qrcode'],
                    'payment_method' => $paymentMethod
                ]);
            } elseif ($paymentMethod == 'crypto') {
                return view('wallet.crypto', [
                    'transaction_id' => $result['transaction_id'],
                    'wallet_address' => $result['wallet_address'],
                    'crypto_amount' => $result['crypto_amount'],
                    'crypto_type' => $result['crypto_type'],
                    'amount' => $amount,
                    'expires_at' => $result['expires_at']
                ]);
            }
            
            return back()->with('error', '不支持的支付方式');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', '处理充值请求失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 检查支付状态
     */
    public function checkPaymentStatus($transactionId)
    {
        $transaction = Transaction::where('id', $transactionId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        $paymentMethod = $transaction->payment_method;
        
        try {
            // 获取支付服务
            $paymentService = PaymentFactory::create($paymentMethod);
            
            // 查询支付状态
            $result = $paymentService->queryPayment($transactionId);
            
            return response()->json([
                'success' => $result['success'],
                'paid' => $result['paid'] ?? false,
                'status' => $transaction->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}