{{-- resources/views/master/extras/edit.blade.php --}}
@extends('master.layouts.master')

@section('title', '编辑额外选项')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">编辑额外选项</h3>
                </div>
                
                <form action="{{ route('master.extras.update', $extra->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="form-group">
                            <label for="extra_id">API ID</label>
                            <input type="text" class="form-control" id="extra_id" value="{{ $extra->extra_id }}" readonly disabled>
                            <small class="form-text text-muted">API中的选项ID，不可修改</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="code">代码 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code" name="code" value="{{ old('code', $extra->code) }}" required>
                            @error('code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="name">名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $extra->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="name_zh">中文名称</label>
                            <input type="text" class="form-control @error('name_zh') is-invalid @enderror" 
                                   id="name_zh" name="name_zh" value="{{ old('name_zh', $extra->name_zh) }}">
                            @error('name_zh')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">如不填写，将默认使用名称</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">价格 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" value="{{ old('price', $extra->price) }}" step="0.01" min="0" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">美元</span>
                                </div>
                            </div>
                            @error('price')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">API中的原始价格（美元），系统会自动转换为人民币</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_multiple" name="is_multiple" value="1" 
                                       {{ old('is_multiple', $extra->is_multiple) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_multiple">支持多选</label>
                            </div>
                            <small class="form-text text-muted">开启后用户可以同时选择多个此类选项</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" 
                                       {{ old('active', $extra->active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="active">启用</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">更新</button>
                        <a href="{{ route('master.extras.index') }}" class="btn btn-default">取消</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection