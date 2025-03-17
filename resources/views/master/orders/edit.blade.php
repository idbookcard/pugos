@extends('master.layouts.master')

@section('title', '编辑订单')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">编辑订单</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}">控制台</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.orders.index') }}">订单管理</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.orders.show', $order->id) }}">订单详情</a></li>
        <li class="breadcrumb-item active">编辑订单</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i> 编辑订单 #{{ $order->order_number }}
        </div>
        <div class="card-body">
            <form action="{{ route('master.orders.update', $order->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="status" class="form-label">订单状态</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>待处理</option>
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>处理中</option>
                            <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>已完成</option>
                            <option value="canceled" {{ $order->status == 'canceled' ? 'selected' : '' }}>已取消</option>
                            <option value="rejected" {{ $order->status == 'rejected' ? 'selected' : '' }}>已拒绝</option>
                            <option value="refunded" {{ $order->status == 'refunded' ? 'selected' : '' }}>已退款</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="status_notes" class="form-label">状态变更备注</label>
                        <input type="text" class="form-control" id="status_notes" name="status_notes" placeholder="可选">
                    </div>
                </div>
                
                @if($order->service_type !== 'monthly')
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="target_url" class="form-label">目标网址</label>
                        <input type="url" class="form-control @error('target_url') is-invalid @enderror" id="target_url" name="target_url" value="{{ old('target_url', $order->target_url) }}" required>
                        @error('target_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="keywords" class="form-label">关键词</label>
                        <input type="text" class="form-control @error('keywords') is-invalid @enderror" id="keywords" name="keywords" value="{{ old('keywords', $order->keywords) }}">
                        @error('keywords')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                @endif
                
                @if($order->service_type === 'guest_post' || $order->service_type === 'package')
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="article" class="form-label">文章内容</label>
                        <textarea class="form-control @error('article') is-invalid @enderror" id="article" name="article" rows="10">{{ old('article', $order->article) }}</textarea>
                        @error('article')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                @endif
                
                @if($order->service_type === 'monthly')
                <!-- 包月订单每周任务编辑 -->
                <h5 class="mt-4 mb-3">每周任务状态</h5>
                
                <div class="table-responsive mb-3">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>周次</th>
                                <th>目标网址</th>
                                <th>状态</th>
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
                                <td>
                                    <select class="form-select form-select-sm" name="weekly_tasks[{{ $task->week_number }}][status]">
                                        <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>待处理</option>
                                        <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>处理中</option>
                                        <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>已完成</option>
                                    </select>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                
                @if($order->service_type === 'external')
                <!-- 额外数据 (针对API订单) -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="extra_data_quantity" class="form-label">数量</label>
                        <input type="number" class="form-control" id="extra_data_quantity" name="extra_data[quantity]" value="{{ json_decode($order->extra_data, true)['quantity'] ?? 1 }}" min="1">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="extra_data_notes" class="form-label">备注</label>
                        <input type="text" class="form-control" id="extra_data_notes" name="extra_data[notes]" value="{{ json_decode($order->extra_data, true)['notes'] ?? '' }}">
                    </div>
                </div>
                @endif
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('master.orders.show', $order->id) }}" class="btn btn-secondary">取消</a>
                    <button type="submit" class="btn btn-primary">保存更改</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($order->service_type === 'guest_post' || $order->service_type === 'package')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#article',
        plugins: 'lists link code',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | link | code',
        menubar: false,
        height: 400
    });
</script>
@endif
@endsection