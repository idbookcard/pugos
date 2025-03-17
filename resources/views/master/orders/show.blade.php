@extends('master.layouts.master')

@section('title', '订单详情')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">订单详情</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}">控制台</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.orders.index') }}">订单管理</a></li>
        <li class="breadcrumb-item active">订单详情</li>
    </ol>
    
    <div class="row mb-4">
        <div class="col-md-8">
            <!-- 订单基本信息卡片 -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-info-circle me-1"></i> 基本信息</span>
                    
                    <div class="btn-group btn-group-sm">
                        @if($order->service_type === 'external' || ($order->package && $order->package->third_party_id))
                            @if(!$order->apiOrder)
                                <a href="{{ route('master.orders.submit-to-api', $order->id) }}" class="btn btn-success">
                                    <i class="fas fa-upload me-1"></i> 提交到API
                                </a>
                            @else
                                <a href="{{ route('master.orders.sync-api-status', $order->id) }}" class="btn btn-info">
                                    <i class="fas fa-sync me-1"></i> 同步API状态
                                </a>
                            @endif
                        @endif
                        
                        @if(!in_array($order->status, ['completed', 'canceled', 'refunded']))
                            <a href="{{ route('master.orders.edit', $order->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i> 编辑
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">订单号：</span>
                                <span class="fw-bold">{{ $order->order_number }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">下单时间：</span>
                                <span>{{ $order->created_at->format('Y-m-d H:i:s') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                            <span class="text-muted">用户：</span>
                                <span>{{ $order->user->name }} ({{ $order->user->email }})</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">金额：</span>
                                <span class="text-primary fw-bold">¥{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">套餐：</span>
                                <span>{{ $order->package->name ?? '未关联套餐' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">类型：</span>
                                <span>
                                    @switch($order->service_type)
                                        @case('package')
                                            <span class="badge bg-primary">单项套餐</span>
                                            @break
                                        @case('monthly')
                                            <span class="badge bg-success">包月套餐</span>
                                            @break
                                        @case('external')
                                            <span class="badge bg-info">第三方API</span>
                                            @break
                                        @case('guest_post')
                                            <span class="badge bg-warning">Guest Post</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $order->service_type }}</span>
                                    @endswitch
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">状态：</span>
                                <span>
                                    @switch($order->status)
                                        @case('pending')
                                            <span class="badge bg-secondary">待处理</span>
                                            @break
                                        @case('processing')
                                            <span class="badge bg-primary">处理中</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-success">已完成</span>
                                            @break
                                        @case('canceled')
                                            <span class="badge bg-danger">已取消</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge bg-warning">已拒绝</span>
                                            @break
                                        @case('refunded')
                                            <span class="badge bg-info">已退款</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $order->status }}</span>
                                    @endswitch
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">支付状态：</span>
                                <span>
                                    @switch($order->payment_status)
                                        @case('paid')
                                            <span class="badge bg-success">已支付</span>
                                            @break
                                        @case('unpaid')
                                            <span class="badge bg-warning">未支付</span>
                                            @break
                                        @case('refunded')
                                            <span class="badge bg-info">已退款</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $order->payment_status }}</span>
                                    @endswitch
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    @if($order->service_type !== 'monthly')
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">目标网址：</span>
                                <a href="{{ $order->target_url }}" target="_blank">{{ $order->target_url }}</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">关键词：</span>
                                <span>{{ $order->keywords }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($order->article)
                    <div class="mt-3">
                        <h6 class="text-muted">文章内容：</h6>
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($order->article)) !!}
                        </div>
                    </div>
                    @endif
                    
                    @if($order->extra_data)
                    <div class="mt-3">
                        <h6 class="text-muted">额外数据：</h6>
                        <div class="border rounded p-3 bg-light">
                            <pre>{{ json_encode(json_decode($order->extra_data), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- 包月订单详细信息 -->
            @if($order->service_type === 'monthly' && isset($order->monthlyDetail))
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-building me-1"></i> 企业信息
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">公司名称：</span>
                                <span>{{ $order->monthlyDetail->company_name }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">网站：</span>
                                <a href="{{ $order->monthlyDetail->website }}" target="_blank">{{ $order->monthlyDetail->website }}</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">联系人：</span>
                                <span>{{ $order->monthlyDetail->contact_name }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">联系邮箱：</span>
                                <span>{{ $order->monthlyDetail->contact_email }}</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($order->monthlyDetail->phone)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">电话：</span>
                                <span>{{ $order->monthlyDetail->phone }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">行业：</span>
                                <span>{{ $order->monthlyDetail->industry ?? '未提供' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($order->monthlyDetail->address || $order->monthlyDetail->business_hours)
                    <div class="row mb-3">
                        @if($order->monthlyDetail->address)
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">地址：</span>
                                <span>{{ $order->monthlyDetail->address }}</span>
                            </div>
                        </div>
                        @endif
                        
                        @if($order->monthlyDetail->business_hours)
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">营业时间：</span>
                                <span>{{ $order->monthlyDetail->business_hours }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                    
                    <div class="mt-3">
                        <h6 class="text-muted">企业描述：</h6>
                        <div class="border rounded p-3 bg-light">
                            {{ $order->monthlyDetail->description }}
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <h6 class="text-muted">主要服务/关键词：</h6>
                        <div class="border rounded p-3 bg-light">
                            {{ $order->monthlyDetail->services_keywords }}
                        </div>
                    </div>
                    
                    @if($order->monthlyDetail->social_media)
                    <div class="mt-3">
                        <h6 class="text-muted">社交媒体：</h6>
                        <div class="border rounded p-3 bg-light">
                            {{ $order->monthlyDetail->social_media }}
                        </div>
                    </div>
                    @endif
                    
                    @if($order->monthlyDetail->article_file_path)
                    <div class="mt-3">
                        <h6 class="text-muted">上传的文章：</h6>
                        <div class="border rounded p-3 bg-light">
                            <a href="{{ Storage::url($order->monthlyDetail->article_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> 下载文章
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- 包月订单每周任务 -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tasks me-1"></i> 每周任务
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table class="table table-bordered">
    <thead>
        <tr>
            <th>周次</th>
            <th>目标网址</th>
            <th>关键词</th>
            <th>状态</th>
            <th>工单号</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->weeklyTasks as $task)
        <tr>
            <td>第{{ $task->week_number }}周</td>
            <td>
                <a href="{{ $task->target_url }}" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">
                    {{ $task->target_url }}
                </a>
            </td>
            <td>{{ $task->keywords }}</td>
            <td>
                @switch($task->status)
                    @case('pending')
                        <span class="badge bg-secondary">待处理</span>
                        @break
                    @case('in_progress')
                        <span class="badge bg-primary">处理中</span>
                        @break
                    @case('completed')
                        <span class="badge bg-success">已完成</span>
                        @break
                    @default
                        <span class="badge bg-secondary">{{ $task->status }}</span>
                @endswitch
            </td>
            <td>
                @if($task->work_order_number)
                    <span class="badge bg-info">{{ $task->work_order_number }}</span>
                @else
                    <span class="text-muted">未创建</span>
                @endif
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('master.orders.view-weekly-task', [$order->id, $task->week_number]) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadReportModal" data-week="{{ $task->week_number }}">
                        <i class="fas fa-upload"></i>
                    </button>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- API订单信息 -->
            @if($order->apiOrder)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-code me-1"></i> API订单信息
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">API订单ID：</span>
                                <span>{{ $order->apiOrder->api_order_id ?? '未提交' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">API状态：</span>
                                <span class="badge bg-info">{{ $order->apiOrder->api_status ?? '未知' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">提交时间：</span>
                                <span>{{ $order->apiOrder->submitted_at ? $order->apiOrder->submitted_at->format('Y-m-d H:i:s') : '未提交' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">完成时间：</span>
                                <span>{{ $order->apiOrder->completed_at ? $order->apiOrder->completed_at->format('Y-m-d H:i:s') : '未完成' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($order->apiOrder->api_response)
                    <div class="mt-3">
                        <h6 class="text-muted">API响应：</h6>
                        <div class="border rounded p-3 bg-light">
                            <pre>{{ json_encode(json_decode($order->apiOrder->api_response), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            <!-- 状态日志 -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-history me-1"></i> 状态日志
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>时间</th>
                                    <th>原状态</th>
                                    <th>新状态</th>
                                    <th>备注</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($statusLogs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        @if($log->old_status)
                                            @if(strpos($log->old_status, '周') !== false)
                                                {{ $log->old_status }}
                                            @else
                                                @switch($log->old_status)
                                                    @case('pending')
                                                        <span class="badge bg-secondary">待处理</span>
                                                        @break
                                                    @case('processing')
                                                        <span class="badge bg-primary">处理中</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-success">已完成</span>
                                                        @break
                                                    @case('canceled')
                                                        <span class="badge bg-danger">已取消</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge bg-warning">已拒绝</span>
                                                        @break
                                                    @case('refunded')
                                                        <span class="badge bg-info">已退款</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $log->old_status }}</span>
                                                @endswitch
                                            @endif
                                        @else
                                            <span class="text-muted">新建</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(strpos($log->new_status, '周') !== false)
                                            {{ $log->new_status }}
                                        @else
                                            @switch($log->new_status)
                                                @case('pending')
                                                    <span class="badge bg-secondary">待处理</span>
                                                    @break
                                                @case('processing')
                                                    <span class="badge bg-primary">处理中</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-success">已完成</span>
                                                    @break
                                                @case('canceled')
                                                    <span class="badge bg-danger">已取消</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge bg-warning">已拒绝</span>
                                                    @break
                                                @case('refunded')
                                                    <span class="badge bg-info">已退款</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $log->new_status }}</span>
                                            @endswitch
                                        @endif
                                    </td>
                                    <td>{{ $log->notes }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">暂无状态变更记录</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- 订单操作卡片 -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-cogs me-1"></i> 订单操作
                </div>
                <div class="card-body">
                    @if(!in_array($order->status, ['completed', 'canceled', 'refunded']))
                        <form action="{{ route('master.orders.update', $order->id) }}" method="POST" class="mb-3">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">更改订单状态</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>待处理</option>
                                    <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>处理中</option>
                                    <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>已完成</option>
                                    <option value="canceled" {{ $order->status == 'canceled' ? 'selected' : '' }}>已取消</option>
                                    <option value="rejected" {{ $order->status == 'rejected' ? 'selected' : '' }}>已拒绝</option>
                                    <option value="refunded" {{ $order->status == 'refunded' ? 'selected' : '' }}>已退款</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status_notes" class="form-label">状态变更备注</label>
                                <textarea class="form-control" id="status_notes" name="status_notes" rows="2"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">更新状态</button>
                        </form>
                        
                        <hr>
                    @endif
                    
                    <!-- 上传报告按钮 -->
                    @if($order->service_type !== 'monthly')
                        <button type="button" class="btn btn-success w-100 mb-3" data-bs-toggle="modal" data-bs-target="#uploadReportModal">
                            <i class="fas fa-upload me-1"></i> 上传报告
                        </button>
                    @endif
                    
                    <!-- 其他操作按钮 -->
                    @if($order->service_type === 'external' || ($order->package && $order->package->third_party_id))
                        @if(!$order->apiOrder)
                            <a href="{{ route('master.orders.submit-to-api', $order->id) }}" class="btn btn-info w-100 mb-3">
                                <i class="fas fa-paper-plane me-1"></i> 提交到API
                            </a>
                        @else
                            <a href="{{ route('master.orders.sync-api-status', $order->id) }}" class="btn btn-warning w-100 mb-3">
                                <i class="fas fa-sync me-1"></i> 同步API状态
                            </a>
                        @endif
                    @endif
                </div>
            </div>
            
            <!-- 报告列表卡片 -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-alt me-1"></i> 报告列表
                </div>
                <div class="card-body">
                    @if(count($reports) > 0)
                        <div class="list-group">
                            @foreach($reports as $report)
                                @php
                                    $reportData = json_decode($report->report_data, true);
                                @endphp
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <h6 class="mb-1">
                                            @if(isset($reportData['week_number']))
                                                <span class="badge bg-info">第{{ $reportData['week_number'] }}周</span>
                                            @endif
                                            报告
                                        </h6>
                                        <small>{{ $report->created_at->format('Y-m-d') }}</small>
                                    </div>
                                    
                                    @if(isset($reportData['file_name']))
                                        <p class="mb-1 text-truncate">{{ $reportData['file_name'] }}</p>
                                    @endif
                                    
                                    @if(isset($reportData['notes']))
                                        <small class="text-muted">{{ $reportData['notes'] }}</small>
                                    @endif
                                    
                                    <div class="mt-2">
                                        <a href="{{ Storage::url($reportData['file_path']) }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-download me-1"></i> 下载
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            暂无报告上传
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 上传报告模态框 -->
<div class="modal fade" id="uploadReportModal" tabindex="-1" aria-labelledby="uploadReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadReportModalLabel">上传报告</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('master.orders.upload-report', $order->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    @if($order->service_type === 'monthly')
                        <div class="mb-3">
                            <label for="week_number" class="form-label">选择周次</label>
                            <select class="form-select" id="week_number" name="week_number" required>
                                @foreach($order->weeklyTasks as $task)
                                    <option value="{{ $task->week_number }}">第{{ $task->week_number }}周</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="report_file" class="form-label">报告文件</label>
                        <input type="file" class="form-control" id="report_file" name="report_file" required>
                        <div class="form-text">支持PDF、Word、Excel、CSV、TXT和ZIP格式，最大10MB</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="report_notes" class="form-label">报告备注</label>
                        <textarea class="form-control" id="report_notes" name="report_notes" rows="3"></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="mark_completed" name="mark_completed" value="1">
                        <label class="form-check-label" for="mark_completed">
                            @if($order->service_type === 'monthly')
                                上传报告后如所有周都已完成，则标记整个订单为已完成
                            @else
                                上传报告后标记订单为已完成
                            @endif
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">上传</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // 设置上传报告模态框的周次
    document.addEventListener('DOMContentLoaded', function() {
        const uploadReportModal = document.getElementById('uploadReportModal');
        if (uploadReportModal) {
            uploadReportModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const weekNumber = button.getAttribute('data-week');
                
                const weekSelect = this.querySelector('#week_number');
                if (weekSelect && weekNumber) {
                    weekSelect.value = weekNumber;
                }
            });
        }
    });
</script>
@endsection