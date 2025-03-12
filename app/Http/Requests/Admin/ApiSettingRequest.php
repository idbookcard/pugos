<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApiSettingRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'api_key' => 'required|string',
            'api_secret' => 'nullable|string',
            'api_url' => 'required|url',
            'settings' => 'nullable|json',
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
            'name.required' => 'API名称不能为空',
            'name.max' => 'API名称不能超过255个字符',
            'api_key.required' => 'API密钥不能为空',
            'api_url.required' => 'API URL不能为空',
            'api_url.url' => '请输入有效的URL地址',
            'settings.json' => '设置必须是有效的JSON格式',
        ];
    }
}