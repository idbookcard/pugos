@extends('layouts.app')

@section('title', '创建Guest Post订单 - ' . $package->name)

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
                    <h4 class="mb-0">创建Guest Post订单</h4>
                </div>
                
                <div class="card-body">
                    <form id="orderForm" action="{{ route('orders.store') }}" method="POST" enctype="multipart/form-data" x-data="guestPostOrderForm()">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $package->id }}">
                        <input type="hidden" name="order_type" value="guest_post">
                        
                        <!-- 套餐信息 -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $package->name }}</h5>
                                    <p class="text-muted mb-0">{{ $package->category->name }} • {{ $package->delivery_days }}天交付 • DA {{ $package->guest_post_da }}+ 网站</p>
                                </div>
                                <div class="ms-auto">
                                    <span class="fs-5 fw-bold text-primary">¥{{ number_format($package->price, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- 目标链接 -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">目标链接信息</h5>
                            
                            <div class="mb-3">
                                <label for="target_url" class="form-label">目标网址 <span class="text-danger">*</span></label>
                                <input type="url" class="form-control @error('target_url') is-invalid @enderror" 
                                       id="target_url" name="target_url" value="{{ old('target_url') }}" required
                                       placeholder="https://example.com/your-page">
                                @error('target_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">输入您希望建立外链的网页URL</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="keywords" class="form-label">锚文本关键词 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('keywords') is-invalid @enderror" 
                                       id="keywords" name="keywords" value="{{ old('keywords') }}" required
                                       placeholder="主要关键词, 次要关键词">
                                @error('keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">输入您希望作为锚文本的关键词，多个关键词请用英文逗号分隔</div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- 文章内容 -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">文章内容</h5>
                            
                            <div class="mb-3">
                                <label for="article-editor" class="form-label">文章内容 <span class="text-danger">*</span></label>
                                
                                <!-- TinyMCE Editor Container -->
                                <div class="border rounded">
                                    <div id="article-editor" style="min-height: 400px;"></div>
                                </div>
                                
                                <!-- Hidden textarea to store the content for form submission -->
                                <textarea id="article" name="article" style="display: none;">{{ old('article') }}</textarea>
                                
                                @error('article')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                
                                <!-- Word count display -->
                                <div class="d-flex justify-content-between mt-2">
                                    <div class="form-text">
                                        请提供高质量的原创文章内容，我们将发布到DA {{ $package->guest_post_da }}+ 的网站上。
                                        文章长度应至少{{ $package->guest_post_da >= 50 ? '1000' : '800' }}字。
                                    </div>
                                    <div class="form-text" x-ref="wordCountDisplay">
                                        <span x-text="wordCount"></span> 字
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <h6 class="alert-heading fw-bold"><i class="bi bi-info-circle-fill me-2"></i>文章要求</h6>
                                <ul class="mb-0 ps-3">
                                    <li>100% 原创内容，会通过多种工具检测重复率</li>
                                    <li>内容必须与您的目标网站主题相关</li>
                                    <li>不得包含垃圾信息、黑帽SEO内容或违反法律的内容</li>
                                    <li>文章将由我们的团队审核，如有需要可能会略作调整</li>
                                    <li>最终发布的文章会包含1-2个指向您目标URL的锚文本链接</li>
                                </ul>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- 额外说明 -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">额外说明</h5>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">额外说明或要求</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">如有任何特殊需求或说明，请在此处告诉我们</div>
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
                                <button type="submit" class="btn btn-primary btn-lg" id="submit-btn" x-bind:disabled="!isSubmittable">
                                    <span x-show="wordCount < 5">请输入至少5个字的文章内容</span>
                                    <span x-show="wordCount >= 5 && totalPrice > {{ $balance }}">余额不足，请先充值</span>
                                    <span x-show="wordCount >= 5 && totalPrice <= {{ $balance }}">提交订单</span>
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
            
            <!-- Guest Post套餐说明卡片 -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Guest Post说明</h5>
                </div>
                <div class="card-body">
                    <p class="small mb-3">Guest Post是一种高效的外链建设方式：</p>
                    <ul class="small mb-0">
                        <li class="mb-2">在DA {{ $package->guest_post_da }}+ 的高质量网站上发布文章</li>
                        <li class="mb-2">包含指向您网站的锚文本链接</li>
                        <li class="mb-2">提升您网站的权威性和关键词排名</li>
                        <li class="mb-2">获得相关行业的流量引导</li>
                        <li>永久链接，长期有效提升SEO效果</li>
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
<!-- 引入 TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    function guestPostOrderForm() {
        return {
            basePrice: {{ $package->price }},
            extrasPrice: 0,
            totalPrice: {{ $package->price }},
            wordCount: 0,
            isSubmittable: false,
            
            init() {
                this.updateTotalPrice();
                this.initEditor();
            },
            
            initEditor() {
                // 初始化 TinyMCE 编辑器
                tinymce.init({
                    selector: '#article-editor',
                    plugins: 'lists link image table code help wordcount autoresize',
                    toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | link image | table | code',
                    menubar: false,
                    height: 400,
                    branding: false,
                    promotion: false,
                    setup: (editor) => {
                        // 加载编辑器时，如果有旧内容则填充
                        editor.on('init', () => {
                            const oldContent = document.getElementById('article').value;
                            if (oldContent) {
                                editor.setContent(oldContent);
                                this.updateWordCount(oldContent);
                            }
                        });
                        
                        // 内容变更时更新字数统计并检查是否可提交
                        editor.on('input keyup', () => {
                            const content = editor.getContent();
                            document.getElementById('article').value = content;
                            this.updateWordCount(content);
                            this.checkSubmittable();
                        });
                        
                        // 提交表单前确保内容已同步
                        document.getElementById('orderForm').addEventListener('submit', () => {
                            document.getElementById('article').value = editor.getContent();
                        });
                    },
                    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial; font-size: 14px; }',
                    
                    // 安全设置
                    valid_elements: 'p,br,h1,h2,h3,h4,h5,h6,strong,em,u,strike,ol,ul,li,a[href|target],blockquote,pre,code',
                    extended_valid_elements: 'a[href|rel|target|title]',
                    invalid_elements: 'script,iframe,object,embed',
                    convert_urls: false,
                    relative_urls: false,
                    remove_script_host: false
                });
            },
            
            updateWordCount(content) {
                // 移除 HTML 标签并计算字数
                const textOnly = content.replace(/<[^>]*>?/gm, '');
                const words = textOnly.trim().replace(/\s+/g, ' ').split(' ');
                const chineseCharCount = (textOnly.match(/[\u4e00-\u9fa5]/g) || []).length;
                
                // 中文按字符计数，英文按词计数
                this.wordCount = chineseCharCount + words.filter(word => !/[\u4e00-\u9fa5]/.test(word)).length;
            },
            // 在 guestPostOrderForm() 或 monthlyOrderForm() 等函数中
checkSubmittable() {
    @auth
    // 将 balance 转换为浮点数确保比较准确
    const balance = parseFloat({{ $balance }});
    const totalPrice = parseFloat(this.totalPrice);
    this.isSubmittable = this.wordCount >= 5 && totalPrice <= balance;
    
    // 添加日志调试
    console.log('Balance:', balance, 'Total Price:', totalPrice, 'Can Submit:', this.isSubmittable);
    @else
    this.isSubmittable = this.wordCount >= 5;
    @endauth
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
                
                // 检查是否可提交
                this.checkSubmittable();
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
    
    .tox-statusbar__branding {
        display: none !important;
    }
    
    /* 禁用状态的按钮样式 */
    #submit-btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }
</style>
@endsection