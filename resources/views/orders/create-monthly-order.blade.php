<!-- resources/views/orders/create_monthly.blade.php -->
@extends('layouts.app')

@section('title', '创建包月订单 - ' . $package->name)

@section('content')
<div class="container py-5">
    <!-- 返回链接 -->
    <div class="mb-4">
        <a href="{{ route('packages.show', $package->slug) }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> 返回套餐详情
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0">创建包月订单</h4>
                </div>
                
                <div class="card-body">
                    <form id="orderForm" action="{{ route('orders.store') }}" method="POST" enctype="multipart/form-data" x-data="monthlyOrderForm()">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $package->id }}">
                        <input type="hidden" name="order_type" value="monthly">
                        
                        <!-- 套餐信息 -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $package->name }}</h5>
                                    <p class="text-muted mb-0">{{ $package->category->name }} • {{ $package->delivery_days }}天服务周期</p>
                                </div>
                                <div class="ms-auto">
                                    <span class="fs-5 fw-bold text-primary">¥{{ number_format($package->price, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- 企业基本信息 -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">企业基本信息</h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="company_name" class="form-label">企业名称 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                           id="company_name" name="company_name" value="{{ old('company_name') }}" required>
                                    @error('company_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="website" class="form-label">网站 <span class="text-danger">*</span></label>
                                    <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                           id="website" name="website" value="{{ old('website') }}" required
                                           placeholder="https://example.com">
                                    @error('website')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">电话号码</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="contact_email" class="form-label">联系人邮箱 <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                           id="contact_email" name="contact_email" value="{{ old('contact_email', auth()->user()->email ?? '') }}" required>
                                    @error('contact_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="contact_name" class="form-label">联系人姓名 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('contact_name') is-invalid @enderror" 
                                           id="contact_name" name="contact_name" value="{{ old('contact_name', auth()->user()->name ?? '') }}" required>
                                    @error('contact_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="industry" class="form-label">行业类别</label>
                                    <input type="text" class="form-control @error('industry') is-invalid @enderror" 
                                           id="industry" name="industry" value="{{ old('industry') }}">
                                    @error('industry')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-12">
                                    <label for="address" class="form-label">地址</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                           id="address" name="address" value="{{ old('address') }}">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-12">
                                    <label for="business_hours" class="form-label">营业时间</label>
                                    <input type="text" class="form-control @error('business_hours') is-invalid @enderror" 
                                           id="business_hours" name="business_hours" value="{{ old('business_hours') }}"
                                           placeholder="例如：周一至周五 9:00-18:00，周末 10:00-16:00">
                                    @error('business_hours')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-12">
                                    <label for="social_media" class="form-label">社交媒体</label>
                                    <textarea class="form-control @error('social_media') is-invalid @enderror" 
                                              id="social_media" name="social_media" rows="2" 
                                              placeholder="例如：Facebook: facebook.com/yourcompany, Twitter: @yourcompany">{{ old('social_media') }}</textarea>
                                    @error('social_media')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-12">
                                    <label for="description" class="form-label">企业描述 <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">简要描述您的企业业务、产品或服务</div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="keywords" class="form-label">主要服务/关键词 <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('keywords') is-invalid @enderror" 
                                              id="keywords" name="keywords" rows="2" required>{{ old('keywords') }}</textarea>
                                    @error('keywords')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">输入与您业务相关的主要关键词，多个关键词请用英文逗号分隔</div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- 四周工作内容 -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">四周工作内容</h5>
                            <p class="text-muted mb-3">请为每周提供一个目标网址和相关关键词，我们将针对这些内容开展工作</p>
                            
                            <!-- 第一周 -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">第一周</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="week1_url" class="form-label">目标网址 <span class="text-danger">*</span></label>
                                        <input type="url" class="form-control @error('week1_url') is-invalid @enderror" 
                                               id="week1_url" name="week1_url" value="{{ old('week1_url') }}" required
                                               placeholder="https://example.com/page1">
                                        @error('week1_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="week1_keywords" class="form-label">关键词 (3-5个) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('week1_keywords') is-invalid @enderror" 
                                               id="week1_keywords" name="week1_keywords" value="{{ old('week1_keywords') }}" required
                                               placeholder="关键词1, 关键词2, 关键词3">
                                        @error('week1_keywords')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-0">
                                        <label for="week1_description" class="form-label">简要描述</label>
                                        <textarea class="form-control @error('week1_description') is-invalid @enderror" 
                                                  id="week1_description" name="week1_description" rows="2">{{ old('week1_description') }}</textarea>
                                        @error('week1_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 第二周 -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">第二周</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="week2_url" class="form-label">目标网址 <span class="text-danger">*</span></label>
                                        <input type="url" class="form-control @error('week2_url') is-invalid @enderror" 
                                               id="week2_url" name="week2_url" value="{{ old('week2_url') }}" required
                                               placeholder="https://example.com/page2">
                                        @error('week2_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="week2_keywords" class="form-label">关键词 (3-5个) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('week2_keywords') is-invalid @enderror" 
                                               id="week2_keywords" name="week2_keywords" value="{{ old('week2_keywords') }}" required
                                               placeholder="关键词1, 关键词2, 关键词3">
                                        @error('week2_keywords')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-0">
                                        <label for="week2_description" class="form-label">简要描述</label>
                                        <textarea class="form-control @error('week2_description') is-invalid @enderror" 
                                                  id="week2_description" name="week2_description" rows="2">{{ old('week2_description') }}</textarea>
                                        @error('week2_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 第三周 -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">第三周</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="week3_url" class="form-label">目标网址 <span class="text-danger">*</span></label>
                                        <input type="url" class="form-control @error('week3_url') is-invalid @enderror" 
                                               id="week3_url" name="week3_url" value="{{ old('week3_url') }}" required
                                               placeholder="https://example.com/page3">
                                        @error('week3_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="week3_keywords" class="form-label">关键词 (3-5个) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('week3_keywords') is-invalid @enderror" 
                                               id="week3_keywords" name="week3_keywords" value="{{ old('week3_keywords') }}" required
                                               placeholder="关键词1, 关键词2, 关键词3">
                                        @error('week3_keywords')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-0">
                                        <label for="week3_description" class="form-label">简要描述</label>
                                        <textarea class="form-control @error('week3_description') is-invalid @enderror" 
                                                  id="week3_description" name="week3_description" rows="2">{{ old('week3_description') }}</textarea>
                                        @error('week3_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 第四周 -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">第四周</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="week4_url" class="form-label">目标网址 <span class="text-danger">*</span></label>
                                        <input type="url" class="form-control @error('week4_url') is-invalid @enderror" 
                                               id="week4_url" name="week4_url" value="{{ old('week4_url') }}" required
                                               placeholder="https://example.com/page4">
                                        @error('week4_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="week4_keywords" class="form-label">关键词 (3-5个) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('week4_keywords') is-invalid @enderror" 
                                               id="week4_keywords" name="week4_keywords" value="{{ old('week4_keywords') }}" required
                                               placeholder="关键词1, 关键词2, 关键词3">
                                        @error('week4_keywords')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-0">
                                        <label for="week4_description" class="form-label">简要描述</label>
                                        <textarea class="form-control @error('week4_description') is-invalid @enderror" 
                                                  id="week4_description" name="week4_description" rows="2">{{ old('week4_description') }}</textarea>
                                        @error('week4_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- 文件上传 -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">个性化文章上传</h5>
                            
                            <div class="mb-3">
                                <label for="custom_article" class="form-label">Word文档上传</label>
                                <input type="file" class="form-control @error('custom_article') is-invalid @enderror" 
                                       id="custom_article" name="custom_article" accept=".doc,.docx">
                                @error('custom_article')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">如果您有希望我们使用的个性化文章，请上传Word文档（.doc或.docx格式）</div>
                            </div>
                        </div>
                        
                        <!-- 额外选项 -->
                        @if(!empty($package->available_extras))
                        <hr class="my-4">
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">额外选项</h5>
                            <p class="text-muted mb-3">选择额外服务以增强您的订单效果</p>
                            
                            <div class="extras-list">
                                @php
                                    $availableExtras = is_array($package->available_extras) ? 
                                        $package->available_extras : 
                                        json_decode($package->available_extras, true);
                                    
                                    $multipleExtras = array_filter($availableExtras, function($extra) {
                                        return isset($extra['is_multiple']) && $extra['is_multiple'];
                                    });
                                    
                                    $singleExtras = array_filter($availableExtras, function($extra) {
                                        return !isset($extra['is_multiple']) || !$extra['is_multiple'];
                                    });
                                @endphp
                                
                                <!-- 多选额外选项 -->
                                @if(count($multipleExtras) > 0)
                                    <div class="mb-3">
                                        <div class="fw-semibold mb-2">多选服务（可同时选择多项）：</div>
                                        
                                        @foreach($multipleExtras as $extra)
                                            <div class="form-check mb-2 p-0">
                                                <div class="card hover-shadow">
                                                    <div class="card-body py-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" 
                                                                       name="extras[{{ $extra['id'] }}]" 
                                                                       id="extra-{{ $extra['id'] }}" 
                                                                       value="1" 
                                                                       {{ old('extras.'.$extra['id']) ? 'checked' : '' }}
                                                                       @change="updateTotalPrice()">
                                                                <label class="form-check-label" for="extra-{{ $extra['id'] }}">
                                                                    {{ $extra['name'] ?? $extra['code'] }}
                                                                </label>
                                                            </div>
                                                            <div class="ms-auto text-primary fw-semibold">
                                                                +¥{{ number_format(floatval($extra['price']) * 7.4 / 100 * 1.5, 2) }}
                                                            </div>
                                                        </div>
                                                        @if(!empty($extra['code']) && ($extra['code'] != ($extra['name'] ?? '')))
                                                            <small class="text-muted d-block ps-4">{{ $extra['code'] }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                
                                <!-- 单选额外选项 -->
                                @if(count($singleExtras) > 0)
                                    <div class="mb-3">
                                        <div class="fw-semibold mb-2">单选服务（择一选择）：</div>
                                        
                                        <div class="card hover-shadow mb-2">
                                            <div class="card-body py-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" 
                                                           name="extras_selection" 
                                                           id="extras_selection_none" 
                                                           value="" 
                                                           {{ old('extras_selection') === null ? 'checked' : '' }}
                                                           @change="updateTotalPrice()">
                                                    <label class="form-check-label" for="extras_selection_none">
                                                        不需要额外选项
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @foreach($singleExtras as $extra)
                                            <div class="card hover-shadow mb-2">
                                                <div class="card-body py-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" 
                                                                   name="extras_selection" 
                                                                   id="extra-{{ $extra['id'] }}" 
                                                                   value="{{ $extra['id'] }}" 
                                                                   {{ old('extras_selection') == $extra['id'] ? 'checked' : '' }}
                                                                   @change="updateTotalPrice()">
                                                            <label class="form-check-label" for="extra-{{ $extra['id'] }}">
                                                                {{ $extra['name'] ?? $extra['code'] }}
                                                            </label>
                                                        </div>
                                                        <div class="ms-auto text-primary fw-semibold">
                                                            +¥{{ number_format(floatval($extra['price']) * 7.4 / 100 * 1.5, 2) }}
                                                        </div>
                                                    </div>
                                                    @if(!empty($extra['code']) && ($extra['code'] != ($extra['name'] ?? '')))
                                                        <small class="text-muted d-block ps-4">{{ $extra['code'] }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <hr class="my-4">
                        
                        <!-- 提交按钮 -->
                        <div class="d-grid">
                            @auth
                                <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                                    提交订单
                                </button>
                            @else
                                <button type="button" class="btn btn-primary btn-lg" id="login-btn" data-bs-toggle="modal" data-bs-target="#loginModal">
                                    提交订单
                                </button>
                            @endauth
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- 右侧订单摘要 -->
        <div class="col-lg-4">
            <div class="card shadow-sm position-sticky" style="top: 2rem;">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">订单摘要</h5>
                </div>
                
                <div class="card-body">
                    <!-- 套餐基本信息 -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>套餐价格</span>
                            <span>¥{{ number_format($package->price, 2) }}</span>
                        </div>
                    </div>
                    
                    <!-- 额外选项价格（通过Alpine.js动态更新） -->
                    <div class="mb-3" x-show="extrasPrice > 0">
                        <div class="d-flex justify-content-between">
                            <span>额外选项</span>
                            <span x-text="'¥' + extrasPrice.toFixed(2)"></span>
                        </div>
                    </div>
                    
                    <!-- 总价 -->
                    <div class="d-flex justify-content-between fw-bold">
                        <span>总计</span>
                        <span class="fs-5 text-primary" id="total-price-display" x-text="'¥' + totalPrice.toFixed(2)"></span>
                    </div>
                    
                    <!-- 账户余额和登录状态 -->
                    <div class="mt-4">
                        @auth
                            <div class="d-flex justify-content-between">
                                <span>账户余额</span>
                                <span>¥{{ number_format($balance, 2) }}</span>
                            </div>
                            
                            <!-- 余额不足警告 -->
                            <div class="mt-2" x-show="totalPrice > {{ $balance }}">
                                <div class="alert alert-warning py-2 mb-0">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    余额不足，请先<a href="{{ route('wallet.deposit') }}">充值</a>
                                </div>
                            </div>
                            
                            <!-- 余额充足提示 -->
                            <div class="mt-2" x-show="totalPrice <= {{ $balance }}">
                                <div class="alert alert-success py-2 mb-0">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    余额充足，可以直接下单
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info py-2 mb-0">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                请先<a href="{{ route('login') }}?redirect={{ url()->current() }}">登录</a>或<a href="{{ route('register') }}">注册</a>后购买
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
            <!-- 包月套餐说明卡片 -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">包月套餐说明</h5>
                </div>
                <div class="card-body">
                    <p class="small mb-3">包月套餐涵盖四周的持续优化服务：</p>
                    <ul class="small mb-0">
                        <li class="mb-2">每周针对不同目标页面进行优化</li>
                        <li class="mb-2">四周内累计构建高质量外链</li>
                        <li class="mb-2">根据每周关键词目标调整策略</li>
                        <li class="mb-2">包含周报和月度总结报告</li>
                        <li>服务期结束后可选择续订或升级</li>
                    </ul>
                </div>
            </div>
            
            <!-- 客服支持卡片 -->
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">需要帮助？</h5>
                    <p class="mb-3">如果您在填写表单时有任何问题，请联系我们的客服团队。</p>
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-chat-dots-fill me-2"></i> 在线咨询
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 未登录用户的登录提示模态框 -->
@guest
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">需要登录</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>请先登录或注册账户后再提交订单。</p>
                <p>登录后，您可以：</p>
                <ul>
                    <li>管理您的订单和历史记录</li>
                    <li>查看订单进度和报告</li>
                    <li>享受会员折扣和优惠</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <a href="{{ route('register') }}" class="btn btn-outline-primary">注册新账户</a>
                <a href="{{ route('login') }}?redirect={{ url()->current() }}" class="btn btn-primary">立即登录</a>
            </div>
        </div>
    </div>
</div>
@endguest
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function monthlyOrderForm() {
        return {
            basePrice: {{ $package->price }},
            extrasPrice: 0,
            totalPrice: {{ $package->price }},
            
            init() {
                this.updateTotalPrice();
            },
            
            updateTotalPrice() {
                // 获取基础价格
                let base = this.basePrice;
                
                // 计算额外选项价格
                this.extrasPrice = 0;
                
                // 处理多选额外选项
                document.querySelectorAll('input[type="checkbox"][name^="extras"]:checked').forEach(checkbox => {
                    try {
                        // 从UI中提取价格
                        const priceText = checkbox.closest('.card').querySelector('.text-primary').textContent.trim();
                        const price = parseFloat(priceText.replace('+¥', '').replace(',', ''));
                        if (!isNaN(price)) {
                            this.extrasPrice += price;
                        }
                    } catch (e) {
                        console.error('解析多选额外选项价格出错:', e);
                    }
                });
                
                // 处理单选额外选项
                const selectedRadio = document.querySelector('input[type="radio"][name="extras_selection"]:checked');
                if (selectedRadio && selectedRadio.value) {
                    try {
                        // 从UI中提取价格
                        const priceText = selectedRadio.closest('.card').querySelector('.text-primary').textContent.trim();
                        const price = parseFloat(priceText.replace('+¥', '').replace(',', ''));
                        if (!isNaN(price)) {
                            this.extrasPrice += price;
                        }
                    } catch (e) {
                        console.error('解析单选额外选项价格出错:', e);
                    }
                }
                
                // 计算总价 (基础价格 + 额外选项)
                this.totalPrice = base + this.extrasPrice;
                
                // 检查余额是否充足 (仅登录用户)
                @auth
                const submitBtn = document.getElementById('submit-btn');
                if (submitBtn) {
                    if (this.totalPrice > {{ $balance }}) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = '余额不足，请先充值';
                    } else {
                        submitBtn.disabled = false;
                        submitBtn.textContent = '提交订单';
                    }
                }
                @endauth
            }
        }
    }
</script>
@endsection

@section('styles')
<style>
    .hover-shadow {
        transition: all 0.2s ease;
    }
    .hover-shadow:hover {
        box-shadow: 0 .25rem .5rem rgba(0,0,0,.1)!important;
        border-color: #6c757d;
    }
</style>
@endsection