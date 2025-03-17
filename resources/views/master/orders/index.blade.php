@extends('master.layouts.master')

@section('title', '订单管理')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">订单管理</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}">控制台</a></li>
        <li class="breadcrumb-item active">订单管理</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                订单列表
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter me-1"></i> 筛选
                </button>
                <a href="{{ route('master.orders.batch-sync-api') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-sync me-1"></i> 批量同步API状态
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- 筛选条件提示 -->
            @if(request()->has('status') || request()->has('service_type') || request()->has('search'))
            <div class="alert alert-info mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        当前筛选条件: 
                        @if(request()->has('status'))
                            <span class="badge bg-secondary">状态: {{ request()->input('status') }}</span>
                        @endif
                        @if(request()->has('service_type'))
                            <span class="badge bg-secondary">类型: {{ request()->input('service_type') }}</span>
                        @endif
                        @if(request()->has('search'))
                            <span class="badge bg-secondary">搜索: {{ request()->input('search') }}</span>
                        @endif
                    </div>
                    <a href="{{ route('master.orders.index') }}" class="btn btn-sm btn-outline-secondary">清除筛选</a>
                </div>
            </div>
            @endif
            
            <!-- 订单列表 -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>订单号</th>
                            <th>用户</th>
                            <th>套餐</th>
                            <th>类型</th>
                            <th>金额</th>
                            <th>状态</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td>{{ $order->package->name ?? '未关联套餐' }}</td>
                            <td>
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
                            </td>
                            <td>¥{{ number_format($order->total_amount, 2) }}</td>
                            <td>
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
                            </td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('master.orders.show', $order->id) }}" class="btn btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!in_array($order->status, ['completed', 'canceled', 'refunded']))
                                    <a href="{{ route('master.orders.edit', $order->id) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">暂无数据</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- 分页 -->
            {{ $orders->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- 筛选模态框 -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">筛选订单</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('master.orders.index') }}" method="GET">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">订单状态</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">全部</option>
                            @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                @switch($status)
                                    @case('pending')待处理@break
                                    @case('processing')处理中@break
                                    @case('completed')已完成@break
                                    @case('canceled')已取消@break
                                    @case('rejected')已拒绝@break
                                    @case('refunded')已退款@break
                                    @default{{ $status }}
                                @endswitch
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="service_type" class="form-label">订单类型</label>
                        <select class="form-select" id="service_type" name="service_type">
                            <option value="">全部</option>
                            @foreach($serviceTypes as $type)
                            <option value="{{ $type }}" {{ request('service_type') == $type ? 'selected' : '' }}>
                                @switch($type)
                                    @case('package')单项套餐@break
                                    @case('monthly')包月套餐@break
                                    @case('external')第三方API@break
                                    @case('guest_post')Guest Post@break
                                    @default{{ $type }}
                                @endswitch
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="search" class="form-label">搜索</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="订单号/网址/用户名/邮箱">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                    <button type="submit" class="btn btn-primary">应用筛选</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection