<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * 确定用户是否有权提出此请求
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // 表示已登录的用户可以提交此请求
    }

    /**
     * 获取适用于请求的验证规则
     *
     * @return array
     */
    public function rules()
    {
        return [
            'package_id' => 'required|exists:packages,id,active,1',
            'target_url' => 'required|url',
            'keywords' => 'nullable|string|max:500',
            'article' => 'nullable|string|max:50000',
            'extras' => 'nullable|array',
            'quantity' => 'nullable|integer|min:1',
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
            'package_id.required' => '请选择一个有效的套餐',
            'package_id.exists' => '所选套餐不存在或已停用',
            'target_url.required' => '目标URL不能为空',
            'target_url.url' => '请输入一个有效的URL地址',
            'keywords.max' => '关键词不能超过500个字符',
            'article.max' => '文章内容不能超过50000个字符',
            'quantity.min' => '数量必须大于0',
        ];
    }
}
