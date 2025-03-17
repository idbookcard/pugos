
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
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
            'category_id' => 'required|exists:package_categories,id',
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_zh' => 'nullable|string',
            'features' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'delivery_days' => 'required|integer|min:1',
            'package_type' => 'required|in:monthly,single,third_party,guest_post',
            'is_featured' => 'boolean',
            'active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'third_party_id' => 'nullable|string|max:255',
            'guest_post_da' => 'nullable|integer|min:0|max:100',
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
            'category_id.required' => '请选择产品分类',
            'category_id.exists' => '所选分类不存在',
            'name.required' => '产品名称不能为空',
            'name.max' => '产品名称不能超过255个字符',
            'price.required' => '价格不能为空',
            'price.numeric' => '价格必须是数字',
            'price.min' => '价格不能小于0',
            'delivery_days.required' => '交付天数不能为空',
            'delivery_days.integer' => '交付天数必须是整数',
            'delivery_days.min' => '交付天数不能小于1',
            'package_type.required' => '请选择套餐类型',
            'package_type.in' => '不支持的套餐类型',
        ];
    }
}
