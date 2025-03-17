@extends('master.layouts.master') {{-- 继承后台布局 --}}

@section('title', 'API 设置管理')

@section('content')
{{-- CSRF Token Meta --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid py-4">
    {{-- 无刷新API测试的样式 --}}
    <style>
        @keyframes spinner {
            to { transform: rotate(360deg); }
        }
        .spin {
            animation: spinner 1s linear infinite;
        }
        .toast-container {
            z-index: 1056;
        }
    </style>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-gear-fill text-primary me-2"></i>API 设置管理
                        </h5>
                        <a href="{{ route('master.api-settings.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>添加 API
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    {{-- 成功 & 失败提示 --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- API 设置表格 --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="rounded-start">#</th>
                                    <th>API 名称</th>
                                    <th>URL</th>
                                    <th>密钥</th>
                                    <th>状态</th>
                                    <th class="rounded-end text-end">操作</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                @foreach($apiSettings as $api)
                                    <tr>
                                        <td>{{ $api->id }}</td>
                                        <td class="fw-medium">{{ $api->name }}</td>
                                        <td>
                                            <a href="{{ $api->api_url }}" target="_blank" class="text-decoration-none d-inline-flex align-items-center">
                                                <span class="text-truncate d-inline-block" style="max-width: 300px;">{{ $api->api_url }}</span>
                                                <i class="bi bi-box-arrow-up-right ms-1 text-muted small"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted api-key-text">********</span>
                                                <button class="btn btn-sm btn-outline-secondary ms-2 toggle-key-btn" 
                                                        onclick="toggleKey(this, '{{ $api->api_key }}')" 
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top" 
                                                        title="显示/隐藏密钥">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            @if($api->active ?? true)
                                                <span class="badge rounded-pill bg-success">
                                                    <i class="bi bi-check-circle-fill me-1"></i>启用
                                                </span>
                                            @else
                                                <span class="badge rounded-pill bg-danger">
                                                    <i class="bi bi-x-circle-fill me-1"></i>禁用
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-sm btn-info test-api-btn" data-api-url="{{ route('master.api-settings.test', $api->id) }}">
    <i class="bi bi-plug-fill me-1"></i><span class="btn-text">测试</span>
</button>
                                                <a href="{{ route('master.api-settings.edit', $api->id) }}" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil-fill me-1"></i>编辑
                                                </a>
                                                <form action="{{ route('master.api-settings.destroy', $api->id) }}" method="POST" style="display:inline-block;" 
                                                      onsubmit="return confirm('确定要删除这个 API 设置吗？');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash-fill me-1"></i>删除
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- 无数据提示 --}}
                    @if($apiSettings->isEmpty())
                        <div class="text-center text-muted my-5">
                            <i class="bi bi-inbox-fill fs-1 mb-3 d-block"></i>
                            <p>暂无 API 设置数据</p>
                            <a href="{{ route('master.api-settings.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i>添加 API
                            </a>
                        </div>
                    @endif

                    {{-- 分页 --}}
                    <div class="d-flex justify-content-center mt-4">
                        {{ $apiSettings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 初始化工具提示
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // 初始化无刷新测试按钮
        initApiTestButtons();
    });

    // 显示/隐藏 API Key
    function toggleKey(button, key) {
        const span = button.closest('div').querySelector('.api-key-text');
        const icon = button.querySelector('i');
        
        if (span.textContent === '********') {
            span.textContent = key;
            span.classList.add('user-select-all', 'fw-medium', 'text-dark');
            span.classList.remove('text-muted');
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            span.textContent = '********';
            span.classList.remove('user-select-all', 'fw-medium', 'text-dark');
            span.classList.add('text-muted');
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
    
    // API 无刷新测试功能
    function initApiTestButtons() {
        const testButtons = document.querySelectorAll('.test-api-btn');
        
        testButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // 阻止默认行为，确保不会刷新页面
                e.preventDefault();
                
                const apiUrl = this.getAttribute('data-api-url');
                const btnText = this.querySelector('.btn-text');
                const btnIcon = this.querySelector('i');
                const originalText = btnText.textContent;
                const originalClass = btnIcon.className;
                
                // 设置按钮为加载状态
                btnText.textContent = '测试中...';
                btnIcon.className = 'bi bi-arrow-repeat me-1 spin';
                this.disabled = true;
                
                // 创建 Toast 通知容器 (如果不存在)
                if (!document.getElementById('toast-container')) {
                    const toastContainer = document.createElement('div');
                    toastContainer.id = 'toast-container';
                    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                    document.body.appendChild(toastContainer);
                }
                
                // 发送 AJAX 请求
                fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('网络响应错误');
                    }
                    return response.json();
                })
                .then(data => {
                    // 测试完成，恢复按钮状态
                    btnText.textContent = originalText;
                    btnIcon.className = originalClass;
                    this.disabled = false;
                    
                    // 显示 Toast 通知
                    showToast(data.success ? 'success' : 'danger', 
                              data.success ? '连接测试成功' : '连接测试失败', 
                              data.message);
                })
                .catch(error => {
                    // 错误处理
                    btnText.textContent = originalText;
                    btnIcon.className = originalClass;
                    this.disabled = false;
                    
                    // 显示错误通知
                    showToast('danger', '连接测试失败', error.message);
                    console.error('API 测试错误:', error);
                });
            });
        });
    }
    
    // 显示 Toast 通知
    function showToast(type, title, message) {
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-${type} text-white">
                    <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2"></i>
                    <strong class="me-auto">${title}</strong>
                    <small>刚刚</small>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        const toastContainer = document.getElementById('toast-container');
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            animation: true,
            autohide: true,
            delay: 5000
        });
        
        toast.show();
        
        // 自动移除已关闭的 toast
        toastElement.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }
</script>
@endsection