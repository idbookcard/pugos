@component('mail::message')
# 订单状态更新通知

尊敬的 {{ $order->user->name }}，

您的订单状态已更新：

@component('mail::table')
| 项目 | 详情 |
| ---- | ---- |
| 订单号 | {{ $order->order_number }} |
| 套餐 | {{ $order->package->name ?? '未关联套餐' }} |
| 原状态 | {{ translateStatus($oldStatus) }} |
| 新状态 | {{ translateStatus($newStatus) }} |
@endcomponent

@if($notes)
**更新说明：**
{{ $notes }}
@endif

@if($newStatus == 'completed')
您的订单已完成！感谢您的信任，如有任何疑问，请随时联系我们的客服团队。
@elseif($newStatus == 'processing')
我们已开始处理您的订单，请耐心等待。您可以随时登录您的账户查看订单进度。
@elseif($newStatus == 'canceled' || $newStatus == 'rejected')
很遗憾您的订单已{{ $newStatus == 'canceled' ? '取消' : '被拒绝' }}。如有任何疑问，请联系我们的客服团队。
@endif

@component('mail::button', ['url' => route('orders.show', $order->id)])
查看订单详情
@endcomponent

感谢您的支持！

此致,<br>
{{ config('app.name') }} 团队
@endcomponent

@php
function translateStatus($status) {
    $statusMap = [
        'pending' => '待处理',
        'processing' => '处理中',
        'completed' => '已完成',
        'canceled' => '已取消',
        'rejected' => '已拒绝',
        'refunded' => '已退款',
    ];
    
    return $statusMap[$status] ?? $status;
}
@endphp