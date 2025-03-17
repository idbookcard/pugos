
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\InvoiceService;

class InvoiceRequest extends FormRequest
{
    /**
     * 确定用户是否有权提出此请求
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // 已登录用户可以申请发票
    }

    /**
     * 获取适用于请求的验证规则
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|string|max:100',
            'invoice_type' => 'required|in:regular,vat',
            'amount' => 'required|numeric|min:1',
            'email' => 'required|email',
        ];
        
        // 如果是增值税发票，必须提供税号
        if ($this->input('invoice_type') === 'vat') {
            $rules['tax_number'] = 'required|string|max:30';
        }
        
        return $rules;
    }

    /**
     * 配置验证器实例
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 检查可开票金额是否足够
            $invoiceService = app(InvoiceService::class);
            $invoiceableAmount = $invoiceService->calculateInvoiceableAmount(auth()->id());
            
            if ($this->input('amount') > $invoiceableAmount) {
                $validator->errors()->add('amount', "可开票金额不足，当前可开票金额: {$invoiceableAmount}元");
            }
        });
    }

    /**
     * 获取已定义验证规则的自定义错误消息
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => '请输入发票抬头',
            'title.max' => '发票抬头不能超过100个字符',
            'invoice_type.required' => '请选择发票类型',
            'invoice_type.in' => '不支持的发票类型',
            'amount.required' => '请输入开票金额',
            'amount.numeric' => '开票金额必须是数字',
            'amount.min' => '开票金额必须大于等于1元',
            'email.required' => '请输入接收电子发票的邮箱',
            'email.email' => '请输入有效的邮箱地址',
            'tax_number.required' => '增值税发票必须提供税号',
            'tax_number.max' => '税号不能超过30个字符',
        ];
    }
}