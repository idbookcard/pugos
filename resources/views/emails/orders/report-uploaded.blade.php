@component('mail::message')
# 订单报告上传通知

尊敬的 {{ $order->user->name }}，

@if($weekNumber && $order->service_type === 'monthly')
您的包月订单 **第{{ $weekNumber }}周** 的服务报告已上传！
@else
您的订单服务报告已上传！
@endif

@component('mail::table')
| 项目 | 详情 |
| ---- | ---- |
| 订单号 | {{ $order->order_number }} |
| 套餐 | {{ $order->package->name ?? '未关联套餐' }} |
| 报告时间 | {{ $report->created_at->format('Y-m-d H:i:s') }} |
@endcomponent

@if(isset($reportData['notes']) && $reportData['notes'])
**报告说明：**
{{ $reportData['notes'] }}
@endif

@if(isset($reportData['file_path']))
我们已将报告文件作为附件发送给您，您也可以通过点击下方按钮登录账户查看更多详情。
@endif

@component('mail::button', ['url' => route('orders.show', $order->id)])
查看订单详情
@endcomponent

感谢您的支持！

此致,<br>
{{ config('app.name') }} 团队
@endcomponent