{{-- resources/views/master/extras/index.blade.php --}}
@extends('master.layouts.master')

@section('title', '额外选项管理')

@section('content')
<div class="container-fluid py-4">
    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">额外选项管理</h1>
        <div class="btn-group">
            <a href="{{ route('master.extras.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> 添加选项
            </a>
            <a href="{{ route('master.extras.import.form') }}" class="btn btn-outline-primary">
                <i class="bi bi-upload me-1"></i> 导入选项
            </a>
            <a href="{{ route('master.extras.sync') }}" class="btn btn-outline-primary"
               onclick="return confirm('确定要从API同步额外选项数据吗？')">
                <i class="bi bi-cloud-download me-1"></i> 同步选项
            </a>
        </div>
    </div>

    <!-- 提示消息 -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- 搜索和筛选 -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('master.extras.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="搜索名称或代码..." name="search" value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="is_multiple">
                        <option value="">所有类型</option>
                        <option value="1" {{ request('is_multiple') == '1' ? 'selected' : '' }}>多选</option>
                        <option value="0" {{ request('is_multiple') == '0' ? 'selected' : '' }}>单选</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="active">
                        <option value="">所有状态</option>
                        <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>启用</option>
                        <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>禁用</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">应用筛选</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('master.extras.index') }}" class="btn btn-outline-secondary w-100">重置</a>
                </div>
            </form>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">额外选项列表</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">API ID</th>
                            <th scope="col">代码</th>
                            <th scope="col">名称</th>
                            <th scope="col">中文名称</th>
                            <th scope="col">价格 ($)</th>
                            <th scope="col">价格 (¥)</th>
                            <th scope="col">多选</th>
                            <th scope="col">状态</th>
                            <th scope="col" class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($extras as $extra)
                            <tr>
                                <td>{{ $extra->id }}</td>
                                <td>{{ $extra->extra_id }}</td>
                                <td><code>{{ $extra->code }}</code></td>
                                <td>{{ $extra->name }}</td>
                                <td>{{ $extra->name_zh }}</td>
                                <td>${{ number_format($extra->price, 2) }}</td>
                                <td>¥{{ number_format($extra->price * 7.4 / 100 * 1.5, 2) }}</td>
                                <td>
                                    @if($extra->is_multiple)
                                        <span class="badge bg-success">是</span>
                                    @else
                                        <span class="badge bg-secondary">否</span>
                                    @endif
                                </td>
                                <td>
                                    @if($extra->active)
                                        <span class="badge bg-success">启用</span>
                                    @else
                                        <span class="badge bg-danger">禁用</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('master.extras.edit', $extra->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <form action="{{ route('master.extras.destroy', $extra->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('确定要删除该选项吗？')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                        <p class="mt-3 mb-0">暂无额外选项数据</p>
                                        <div class="mt-3">
                                            <a href="{{ route('master.extras.sync') }}" class="btn btn-primary btn-sm">
                                                <i class="bi bi-cloud-download me-1"></i> 从API同步选项
                                            </a>
                                            <a href="{{ route('master.extras.create') }}" class="btn btn-outline-primary btn-sm ms-2">
                                                <i class="bi bi-plus-lg me-1"></i> 手动添加选项
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($extras->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        显示 {{ $extras->firstItem() ?? 0 }} 到 {{ $extras->lastItem() ?? 0 }} 条，共 {{ $extras->total() }} 条
                    </div>
                    <div>
                        {{ $extras->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    // 使用Bootstrap 5 Tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endsection