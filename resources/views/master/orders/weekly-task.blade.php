@extends('master.layouts.master')

@section('title', '周任务详情')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">周任务详情</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}">控制台</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.orders.index') }}">订单管理</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.orders.show', $order->id) }}">订单详情</a></li>
        <li class="breadcrumb-item active">第{{ $weeklyTask->week_number }}周任务</li>
    </ol>
    
    <div class="row">
        <div class="col-md-8">
            <!-- 任务基本信息卡片 -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i> 任务信息
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">订单号：</span>
                                <span>{{ $order->order_number }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                            <span class="text-muted">周次：</span>
                                <span class="fw-bold">第{{ $weeklyTask->week_number }}周</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">创建时间：</span>
                                <span>{{ $weeklyTask->created_at->format('Y-m-d H:i:s') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">状态：</span>
                                <span>
                                    @switch($weeklyTask->status)
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
                                            <span class="badge bg-secondary">{{ $weeklyTask->status }}</span>
                                    @endswitch
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">目标网址：</span>
                                <a href="{{ $weeklyTask->target_url }}" target="_blank">{{ $weeklyTask->target_url }}</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">关键词：</span>
                                <span>{{ $weeklyTask->keywords }}</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($weeklyTask->description)
                    <div class="mt-3">
                        <h6 class="text-muted">描述：</h6>
                        <div class="border rounded p-3 bg-light">
                            {{ $weeklyTask->description }}
                        </div>
                    </div>
                    @endif
                    
                    @if($weeklyTask->completed_at)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">完成时间：</span>
                                <span>{{ $weeklyTask->completed_at->format('Y-m-d H:i:s') }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- 工单信息卡片 -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-ticket-alt me-1"></i> 工单信息
    </div>
    <div class="card-body">
        @if($weeklyTask->work_order_number)
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">工单号：</span>
                        <span class="fw-bold">{{ $weeklyTask->work_order_number }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">创建时间：</span>
                        <span>{{ $weeklyTask->work_order_created_at ? $weeklyTask->work_order_created_at->format('Y-m-d H:i:s') : '未记录' }}</span>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">工单状态：</span>
                        <span>
                            @switch($weeklyTask->work_order_status)
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
                                    <span class="badge bg-secondary">{{ $weeklyTask->work_order_status ?? '未设置' }}</span>
                            @endswitch
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">负责人：</span>
                        <span>{{ $weeklyTask->work_order_assignee ?? '未分配' }}</span>
                    </div>
                </div>
            </div>
            
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateWorkOrderModal">
                <i class="fas fa-edit me-1"></i> 更新工单信息
            </button>
        @else
            <div class="alert alert-info mb-3">
                尚未创建工单
            </div>
            
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWorkOrderModal">
                <i class="fas fa-plus me-1"></i> 创建工单
            </button>
        @endif
    </div>
</div>

            <!-- 报告列表 -->
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
                                        <h6 class="mb-1">报告</h6>
                                        <small>{{ $report->created_at->format('Y-m-d H:i:s') }}</small>
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
        
        <div class="col-md-4">
            <!-- 任务操作卡片 -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-cogs me-1"></i> 任务操作
                </div>
                <div class="card-body">
                    <form action="{{ route('master.orders.update-weekly-task-status', [$order->id, $weeklyTask->week_number]) }}" method="POST" class="mb-3">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">更改任务状态</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" {{ $weeklyTask->status == 'pending' ? 'selected' : '' }}>待处理</option>
                                <option value="in_progress" {{ $weeklyTask->status == 'in_progress' ? 'selected' : '' }}>处理中</option>
                                <option value="completed" {{ $weeklyTask->status == 'completed' ? 'selected' : '' }}>已完成</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">状态变更备注</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                        
                        @if($weeklyTask->status != 'completed')
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="mark_order_completed" name="mark_order_completed" value="1">
                            <label class="form-check-label" for="mark_order_completed">
                                如所有周都已完成，标记整个订单为已完成
                            </label>
                        </div>
                        @endif
                        
                        <button type="submit" class="btn btn-primary w-100">更新状态</button>
                    </form>
                    
                    <hr>
                    
                    <!-- 上传报告按钮 -->
                    <button type="button" class="btn btn-success w-100 mb-3" data-bs-toggle="modal" data-bs-target="#uploadReportModal">
                        <i class="fas fa-upload me-1"></i> 上传周报告
                    </button>
                    
                    <!-- 返回按钮 -->
                    <a href="{{ route('master.orders.show', $order->id) }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left me-1"></i> 返回订单详情
                    </a>
                </div>
            </div>
            
            <!-- 订单信息简介 -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-shopping-cart me-1"></i> 订单信息
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">订单号：</span>
                        <span>{{ $order->order_number }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">套餐：</span>
                        <span>{{ $order->package->name ?? '未关联套餐' }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">用户：</span>
                        <span>{{ $order->user->name }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">金额：</span>
                        <span class="fw-bold">¥{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">订单状态：</span>
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
                    
                    <div class="mt-3">
                        <a href="{{ route('master.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary w-100">
                            查看完整订单
                        </a>
                    </div>
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
                <h5 class="modal-title" id="uploadReportModalLabel">上传第{{ $weeklyTask->week_number }}周报告</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('master.orders.upload-report', $order->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="week_number" value="{{ $weeklyTask->week_number }}">
                
                <div class="modal-body">
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
                            上传报告后标记本周任务为已完成
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

<!-- 创建工单模态框 -->
<div class="modal fade" id="createWorkOrderModal" tabindex="-1" aria-labelledby="createWorkOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createWorkOrderModalLabel">创建工单</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('master.orders.create-work-order', [$order->id, $weeklyTask->week_number]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="work_order_number" class="form-label">工单号 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="work_order_number" name="work_order_number" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="work_order_status" class="form-label">工单状态</label>
                        <select class="form-select" id="work_order_status" name="work_order_status">
                            <option value="pending">待处理</option>
                            <option value="in_progress">处理中</option>
                            <option value="completed">已完成</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="work_order_assignee" class="form-label">负责人</label>
                        <input type="text" class="form-control" id="work_order_assignee" name="work_order_assignee">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">创建</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 更新工单模态框 -->
<div class="modal fade" id="updateWorkOrderModal" tabindex="-1" aria-labelledby="updateWorkOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateWorkOrderModalLabel">更新工单信息</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('master.orders.update-work-order', [$order->id, $weeklyTask->week_number]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="update_work_order_number" class="form-label">工单号 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="update_work_order_number" name="work_order_number" value="{{ $weeklyTask->work_order_number }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="update_work_order_status" class="form-label">工单状态</label>
                        <select class="form-select" id="update_work_order_status" name="work_order_status">
                            <option value="pending" {{ $weeklyTask->work_order_status == 'pending' ? 'selected' : '' }}>待处理</option>
                            <option value="in_progress" {{ $weeklyTask->work_order_status == 'in_progress' ? 'selected' : '' }}>处理中</option>
                            <option value="completed" {{ $weeklyTask->work_order_status == 'completed' ? 'selected' : '' }}>已完成</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="update_work_order_assignee" class="form-label">负责人</label>
                        <input type="text" class="form-control" id="update_work_order_assignee" name="work_order_assignee" value="{{ $weeklyTask->work_order_assignee }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">更新</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection