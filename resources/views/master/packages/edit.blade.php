{{-- resources/views/admin/packages/edit.blade.php --}}
@extends('master.layouts.master')

@section('title', '编辑套餐')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">编辑套餐</h1>
        <div>
            <a href="{{ route('admin.packages.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm mr-2">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> 返回套餐列表
            </a>
            <a href="{{ route('admin.packages.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> 添加新套餐
            </a>
        </div>
    </div>

    <!-- 编辑套餐表单 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">套餐信息</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">操作:</div>
                    <a class="dropdown-item" href="#" onclick="confirmDelete()">
                        <i class="fas fa-trash fa-sm fa-fw mr-2 text-danger"></i> 删除套餐
                    </a>
                    <a class="dropdown-item" href="{{ route('packages.show', $package->slug) }}" target="_blank">
                        <i class="fas fa-external-link-alt fa-sm fa-fw mr-2 text-primary"></i> 前台查看
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.packages.update', $package->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">套餐名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $package->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name_en">英文名称</label>
                            <input type="text" class="form-control @error('name_en') is-invalid @enderror" id="name_en" name="name_en" value="{{ old('name_en', $package->name_en) }}">
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
                                    <option value="{{ $id }}" {{ old('category_id', $package->category_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
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
                                <option value="single" {{ old('package_type', $package->package_type) == 'single' ? 'selected' : '' }}>单项套餐</option>
                                <option value="monthly" {{ old('package_type', $package->package_type) == 'monthly' ? 'selected' : '' }}>包月套餐</option>
                                <option value="third_party" {{ old('package_type', $package->package_type) == 'third_party' ? 'selected' : '' }}>第三方API</option>
                                <option value="guest_post" {{ old('package_type', $package->package_type) == 'guest_post' ? 'selected' : '' }}>Guest Post</option>
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
                                <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $package->price) }}" required>
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
                                <input type="number" step="0.01" min="0" class="form-control @error('original_price') is-invalid @enderror" id="original_price" name="original_price" value="{{ old('original_price', $package->original_price) }}">
                                @error('original_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">如果设置了原价，将显示为打折促销</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="delivery_days">交付天数 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" class="form-control @error('delivery_days') is-invalid @enderror" id="delivery_days" name="delivery_days" value="{{ old('delivery_days', $package->delivery_days) }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">天</span>
                                </div>
                                @error('delivery_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">完成外链交付的预计天数</small>
                        </div>
                    </div>
                </div>
                
                <!-- 第三方API特定字段 -->
                <div id="third_party_fields" class="row {{ old('package_type', $package->package_type) == 'third_party' ? '' : 'd-none' }}">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 第三方API配置信息
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="third_party_id">API服务ID</label>
                            <input type="text" class="form-control @error('third_party_id') is-invalid @enderror" id="third_party_id" name="third_party_id" value="{{ old('third_party_id', $package->third_party_id) }}">
                            @error('third_party_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">第三方API服务的唯一标识符</small>
                        </div>
                    </div>
                </div>
                
                <!-- Guest Post特定字段 -->
                <div id="guest_post_fields" class="row {{ old('package_type', $package->package_type) == 'guest_post' ? '' : 'd-none' }}">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Guest Post配置信息
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="guest_post_da">域名权重 (DA)</label>
                            <input type="number" min="0" max="100" class="form-control @error('guest_post_da') is-invalid @enderror" id="guest_post_da" name="guest_post_da" value="{{ old('guest_post_da', $package->guest_post_da) }}">
                            @error('guest_post_da')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">网站的Domain Authority值(0-100)</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description">套餐描述</label>
                            <textarea class="form-control summernote @error('description') is-invalid @enderror" id="description" name="description">{{ old('description', $package->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description_zh">中文描述</label>
                            <textarea class="form-control summernote @error('description_zh') is-invalid @enderror" id="description_zh" name="description_zh">{{ old('description_zh', $package->description_zh) }}</textarea>
                            @error('description_zh')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">如果不填写，将使用默认描述</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="features">套餐特性</label>
                            <textarea class="form-control @error('features') is-invalid @enderror" id="features" name="features" rows="5" placeholder="每行一个特性">{{ $featuresText }}</textarea>
                            @error('features')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">每行输入一个特性，将显示为列表项</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sort_order">排序顺序</label>
                            <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $package->sort_order) }}">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">数字越小，排序越靠前</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="custom-control custom-switch mt-4">
                                <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $package->is_featured) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_featured">推荐套餐</label>
                            </div>
                            <small class="form-text text-muted">选中后将在前台显示为推荐套餐</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="custom-control custom-switch mt-4">
                                <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" {{ old('active', $package->active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="active">启用套餐</label>
                            </div>
                            <small class="form-text text-muted">取消选中将在前台隐藏此套餐</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> 保存套餐
                    </button>
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> 取消
                    </a>
                </div>
            </form>
            
            <!-- 删除套餐的表单 -->
            <form id="delete-form" action="{{ route('admin.packages.destroy', $package->id) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
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
        
        // 监听套餐类型变化，显示/隐藏相应字段
        $('#package_type').change(function() {
            const packageType = $(this).val();
            
            // 隐藏所有特定字段
            $('#third_party_fields, #guest_post_fields').addClass('d-none');
            
            // 根据选择的类型显示相应字段
            if (packageType === 'third_party') {
                $('#third_party_fields').removeClass('d-none');
            } else if (packageType === 'guest_post') {
                $('#guest_post_fields').removeClass('d-none');
            }
        });
    });
    
    // 确认删除
    function confirmDelete() {
        if (confirm('确定要删除这个套餐吗？此操作不可逆！')) {
            document.getElementById('delete-form').submit();
        }
    }
</script>
@endsection