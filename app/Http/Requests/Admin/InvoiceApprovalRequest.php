
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceApprovalRequest extends FormRequest
{
    /**
     * 确定用户是否有权提出此请求
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // 通过中间件已确保只有管理员可访问
    }

    /**
     * 获取适用于请求的验证规则
     *
     * @return array
     */
    public function rules()
    {
        return [
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'remarks' => 'nullable|string|max:500',
        ];
    }

    /**
     * 获取已定义验证规则的自定义错误消息
     *
     * @return array
     */
    public function messages()
    {
        return [
            'invoice_file.file' => '上传的发票必须是文件',
            'invoice_file.mimes' => '发票文件必须是PDF、JPG、JPEG或PNG格式',
            'invoice_file.max' => '发票文件大小不能超过5MB',
            'remarks.max' => '备注不能超过500个字符',
        ];
    }
}