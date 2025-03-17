<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    /**
     * 确定用户是否有权提出此请求
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // 已登录用户都可以充值
    }

    /**
     * 获取适用于请求的验证规则
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:wechat,alipay,crypto',
        ];
        
        // 如果支付方式是加密货币，需要选择货币类型
        if ($this->input('payment_method') === 'crypto') {
            $rules['crypto_type'] = 'required|in:USDT,BTC,ETH';
        }
        
        return $rules;
    }

    /**
     * 获取已定义验证规则的自定义错误消息
     *
     * @return array
     */
    public function messages()
    {
        return [
            'amount.required' => '请输入充值金额',
            'amount.numeric' => '充值金额必须是数字',
            'amount.min' => '充值金额必须大于等于1元',
            'payment_method.required' => '请选择支付方式',
            'payment_method.in' => '不支持的支付方式',
            'crypto_type.required' => '请选择加密货币类型',
            'crypto_type.in' => '不支持的加密货币类型',
        ];
    }
}

