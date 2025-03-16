<!-- resources/views/admin/packages/index.blade.php -->
@extends('master.layouts.master')

@section('title', '套餐管理')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">套餐管理</h1>
        <div class="d-flex gap-2">
            <form id="sync-form" action="{{ route('master.api-settings.sync-products') }}" method="POST">
                @csrf
            </form>
            <button 
                onclick="event.preventDefault(); if(confirm('确定要同步API产品吗？')) document.getElementById('sync-form').submit();" 
                class="btn btn-primary d-flex align-items-center"
            >
                <i class="bi bi-arrow-repeat me-2"></i>
                同步API产品
            </button>
            
            <a href="{{ route('master.packages.create') }}" class="btn btn-success d-flex align-items-center">
                <i class="bi bi-plus-lg me-2"></i>
                添加新套餐
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">套餐列表</h6>
            <p class="text-muted small mb-0">管理所有可售卖的产品套餐</p>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="packages-table" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>套餐名称</th>
                            <th>分类</th>
                            <th>类型</th>
                            <th>价格</th>
                            <th width="80">排序</th>
                            <th width="80">状态</th>
                            <th width="120" class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $package)
                            <tr class="{{ $package->is_featured ? 'table-primary bg-opacity-25' : '' }}">
                                <td class="align-middle">{{ $package->id }}</td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-medium">{{ $package->name }}</div>
                                            @if($package->name_en)
                                                <div class="small text-muted">{{ $package->name_en }}</div>
                                            @endif
                                        </div>
                                        @if($package->is_featured)
                                            <span class="badge bg-warning text-dark ms-2">推荐</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="align-middle">{{ $package->category->name ?? 'N/A' }}</td>
                                <td class="align-middle">
                                    @if($package->package_type == 'single')
                                        <span class="badge bg-primary">单项套餐</span>
                                    @elseif($package->package_type == 'monthly')
                                        <span class="badge bg-success">包月套餐</span>
                                    @elseif($package->package_type == 'third_party')
                                        <span class="badge bg-info">第三方API</span>
                                    @elseif($package->package_type == 'guest_post')
                                        <span class="badge bg-secondary">Guest Post</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <div class="fw-medium">¥{{ number_format($package->price, 7) }}</div>
                                    @if($package->original_price && $package->original_price > $package->price)
                                        <div class="small text-muted text-decoration-line-through">¥{{ number_format($package->original_price, 7) }}</div>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $package->sort_order }}</td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <i class="bi {{ $package->active ? 'bi-circle-fill text-success' : 'bi-circle text-secondary' }} me-2 small"></i>
                                        <span class="{{ $package->active ? 'text-success' : 'text-secondary' }}">
                                            {{ $package->active ? '启用' : '禁用' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="align-middle text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('master.packages.edit', $package->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="confirmDelete({{ $package->id }}, '{{ $package->name }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        
                                        <form id="delete-form-{{ $package->id }}" action="{{ route('master.packages.destroy', $package->id) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-box text-muted mb-3" style="font-size: 3rem;"></i>
                                        <p class="text-muted">暂无套餐数据</p>
                                        <a href="{{ route('master.packages.create') }}" class="btn btn-primary mt-2">
                                            <i class="bi bi-plus-lg me-1"></i> 添加新套餐
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-end mt-3">
                {{ $packages->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- 删除确认模态框 -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">确认删除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>确定要删除套餐 <span id="packageName" class="fw-bold"></span> 吗？此操作不可逆。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">确认删除</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentPackageId = null;
    
    function confirmDelete(packageId, packageName) {
        currentPackageId = packageId;
        document.getElementById('packageName').textContent = packageName;
        
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (currentPackageId) {
                document.getElementById(`delete-form-${currentPackageId}`).submit();
            }
        });
        
        // 数据表格初始化 (如果使用DataTables插件)
        // $('#packages-table').DataTable();
    });
</script>
@endpush