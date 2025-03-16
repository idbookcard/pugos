{{-- resources/views/admin/packages/create.blade.php --}}
@extends('master.layouts.master')

@section('title', '创建套餐')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">创建套餐</h1>
        <a href="{{ route('master.packages.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> 返回套餐列表
        </a>
    </div>

    <!-- 创建套餐表单 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">套餐信息</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('master.packages.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">套餐名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name_en">英文名称</label>
                            <input type="text" class="form-control @error('name_en') is-invalid @enderror" id="name_en" name="name_en" value="{{ old('name_en') }}">
                            @error('name_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">请输入套餐的英文名称，用于生成URL友好的别名</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category_id">所属分类 <span class="text-danger">*</span></label>
                            <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                <option value="">请选择分类</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="package_type">套餐类型 <span class="text-danger">*</span></label>
                            <select class="form-control @error('package_type') is-invalid @enderror" id="package_type" name="package_type" required>
                                <option value="single" {{ old('package_type') == 'single' ? 'selected' : '' }}>单项套餐</option>
                                <option value="monthly" {{ old('package_type') == 'monthly' ? 'selected' : '' }}>包月套餐</option>
                                <option value="third_party" {{ old('package_type') == 'third_party' ? 'selected' : '' }}>第三方</option>
                                <option value="guest_post" {{ old('package_type') == 'guest_post' ? 'selected' : '' }}>Guest Post</option>
                            </select>
                            @error('package_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="price">价格 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">¥</span>
                                </div>
                                <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="original_price">原价</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">¥</span>
                                </div>
                                <input type="number" step="0.01" class="form-control @error('original_price') is-invalid @enderror" id="original_price" name="original_price" value="{{ old('original_price') }}">
                                @error('original_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">如果有折扣，请填写原价，否则留空</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="delivery_days">交付天数 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('delivery_days') is-invalid @enderror" id="delivery_days" name="delivery_days" value="{{ old('delivery_days', 7) }}" required>
                            @error('delivery_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- 第三方API相关字段 -->
                <div id="third-party-fields" class="row mt-3" style="display: none;">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 以下信息仅适用于第三方API类型的套餐
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="third_party_id">第三方服务ID</label>
                            <input type="text" class="form-control @error('third_party_id') is-invalid @enderror" id="third_party_id" name="third_party_id" value="{{ old('third_party_id') }}">
                            @error('third_party_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">请输入第三方服务的唯一标识符</small>
                        </div>
                    </div>
                </div>
                
                <!-- Guest Post相关字段 -->
                <div id="guest-post-fields" class="row mt-3" style="display: none;">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 以下信息仅适用于Guest Post类型的套餐
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="guest_post_da">域名权威度(DA)</label>
                            <input type="number" min="0" max="100" class="form-control @error('guest_post_da') is-invalid @enderror" id="guest_post_da" name="guest_post_da" value="{{ old('guest_post_da') }}">
                            @error('guest_post_da')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">请输入Guest Post网站的域名权威度(0-100)</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description">中文描述</label>
                            <textarea class="form-control summernote @error('description') is-invalid @enderror" id="description" name="description">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description_zh">英文描述</label>
                            <textarea class="form-control summernote @error('description_zh') is-invalid @enderror" id="description_zh" name="description_zh">{{ old('description_zh') }}</textarea>
                            @error('description_zh')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="features">特性列表</label>
                            <textarea class="form-control @error('features') is-invalid @enderror" id="features" name="features" rows="5" placeholder="每行一个特性">{{ old('features') }}</textarea>
                            @error('features')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">每行输入一个特性，这些特性将以列表形式显示在产品页面</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sort_order">排序</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">数字越小排序越靠前</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox mt-4">
                                <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_featured">设为推荐套餐</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox mt-4">
                                <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" {{ old('active', 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="active">立即启用</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 保存套餐
                        </button>
                        <a href="{{ route('master.packages.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> 取消
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-zh-CN.min.js"></script>
<script>
    $(document).ready(function() {
        // 初始化富文本编辑器
        $('.summernote').summernote({
            height: 200,
            lang: 'zh-CN',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
        
        // 套餐类型切换时显示或隐藏相关字段
        $('#package_type').change(function() {
            const type = $(this).val();
            
            // 隐藏所有特定类型字段
            $('#third-party-fields, #guest-post-fields').hide();
            
            // 根据选择的类型显示相应字段
            if (type === 'third_party') {
                $('#third-party-fields').show();
            } else if (type === 'guest_post') {
                $('#guest-post-fields').show();
            }
        });
        
        // 页面加载时触发一次，确保根据初始值显示正确的字段
        $('#package_type').trigger('change');
    });
</script>
@endsection