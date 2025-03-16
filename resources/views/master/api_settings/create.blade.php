@extends('master.layouts.master') {{-- 继承后台布局 --}}

@section('title', '添加 API 设置')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-plus-circle-fill text-primary me-2"></i>添加 API 设置
                        </h5>
                        <a href="{{ route('master.api-settings.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>返回列表
                        </a>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    {{-- 错误提示 --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>表单验证错误
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <ul class="mb-0 mt-2 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- 表单 --}}
                    <form action="{{ route('master.api-settings.store') }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        
                        <div class="row g-4">
                            {{-- API 名称 --}}
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" placeholder="API名称" 
                                           value="{{ old('name') }}" required>
                                    <label for="name">API 名称 <span class="text-danger">*</span></label>
                                    <div class="form-text">请输入第三方 API 服务的名称，例如：SEOeStore</div>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            {{-- API URL --}}
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="url" class="form-control @error('api_url') is-invalid @enderror" 
                                           id="api_url" name="api_url" placeholder="API URL" 
                                           value="{{ old('api_url') }}" required>
                                    <label for="api_url">API URL <span class="text-danger">*</span></label>
                                    <div class="form-text">第三方 API 的基础 URL，例如：https://panel.seoestore.net/api/v1</div>
                                    @error('api_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            {{-- API Key --}}
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('api_key') is-invalid @enderror" 
                                           id="api_key" name="api_key" placeholder="API Key" 
                                           value="{{ old('api_key') }}" required>
                                    <label for="api_key">API Key <span class="text-danger">*</span></label>
                                    <div class="form-text">第三方 API 的访问密钥</div>
                                    @error('api_key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            {{-- API Secret (可选) --}}
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('api_secret') is-invalid @enderror" 
                                           id="api_secret" name="api_secret" placeholder="API Secret" 
                                           value="{{ old('api_secret') }}">
                                    <label for="api_secret">API Secret</label>
                                    <div class="form-text">第三方 API 的密钥（如果需要）</div>
                                    @error('api_secret')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            {{-- API Email --}}
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" placeholder="API Email" 
                                           value="{{ old('email') }}">
                                    <label for="email">API Email <span class="text-danger">*</span></label>
                                    <div class="form-text">API 账户关联的邮箱地址</div>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            
                            {{-- API 设置 (JSON) --}}
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="settings" class="form-label">高级设置 (JSON)</label>
                                    <textarea class="form-control @error('settings') is-invalid @enderror" 
                                              id="settings" name="settings" rows="5" 
                                              placeholder='{"additional_config": true, "timeout": 30}'
                                              aria-describedby="settingsHelp">{{ old('settings') }}</textarea>
                                    <div class="form-text" id="settingsHelp">可选的额外设置，使用 JSON 格式，可以留空</div>
                                    @error('settings')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            {{-- 活动状态 --}}
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="active" name="active" value="1" 
                                          {{ old('active', '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">启用此 API</label>
                                </div>
                            </div>
                            
                            {{-- 提交按钮 --}}
                            <div class="col-12 mt-4">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary" 
                                            onclick="window.location.href='{{ route('master.api-settings.index') }}'">
                                        <i class="bi bi-x-lg me-1"></i>取消
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1"></i>保存 API 设置
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 表单验证
    (function() {
        'use strict';
        
        document.addEventListener('DOMContentLoaded', function() {
            // 获取所有需要验证的表单
            var forms = document.querySelectorAll('.needs-validation');
            
            // 阻止表单提交并进行验证
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
            
            // JSON 格式验证（允许为空）
            const settingsField = document.getElementById('settings');
            settingsField.addEventListener('blur', function() {
                const value = this.value.trim();
                if (value !== '') {
                    try {
                        JSON.parse(value);
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } catch (e) {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                        if (!this.nextElementSibling.classList.contains('invalid-feedback')) {
                            const feedback = document.createElement('div');
                            feedback.classList.add('invalid-feedback');
                            feedback.textContent = 'JSON 格式无效';
                            this.after(feedback);
                        }
                    }
                } else {
                    // 为空时清除所有验证状态，因为空值是有效的
                    this.classList.remove('is-invalid');
                    this.classList.remove('is-valid');
                }
            });
        });
    })();
</script>
@endsection