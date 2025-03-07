<footer class="footer bg-dark text-light mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5 class="mb-3">{{ config('app.name', 'SEO外链服务平台') }}</h5>
                <p>专业的SEO外链服务，助您的网站在谷歌搜索中获得更好的排名。提供多样化的外链解决方案，包括月度套餐、单项服务和高质量软文发布。</p>
                <div class="d-flex mt-3">
                    <a href="#" class="text-light me-3"><i class="bi bi-facebook fs-4"></i></a>
                    <a href="#" class="text-light me-3"><i class="bi bi-twitter-x fs-4"></i></a>
                    <a href="#" class="text-light me-3"><i class="bi bi-linkedin fs-4"></i></a>
                </div>
            </div>
            <div class="col-md-3 offset-md-1">
                <h5 class="mb-3">快速链接</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('home') }}" class="text-light text-decoration-none"><i class="bi bi-chevron-right me-1"></i>首页</a></li>
                    <li class="mb-2"><a href="{{ route('packages.monthly') }}" class="text-light text-decoration-none"><i class="bi bi-chevron-right me-1"></i>月度套餐</a></li>
                    <li class="mb-2"><a href="{{ route('packages.single') }}" class="text-light text-decoration-none"><i class="bi bi-chevron-right me-1"></i>单项套餐</a></li>
                    <li class="mb-2"><a href="{{ route('packages.guest-post') }}" class="text-light text-decoration-none"><i class="bi bi-chevron-right me-1"></i>软文外链</a></li>
                    <li class="mb-2"><a href="#" class="text-light text-decoration-none"><i class="bi bi-chevron-right me-1"></i>帮助中心</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5 class="mb-3">联系我们</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="bi bi-geo-alt-fill me-2"></i>
                        上海市浦东新区张江高科技园区博云路2号
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-envelope-fill me-2"></i>
                        <a href="mailto:info@example.com" class="text-light text-decoration-none">info@example.com</a>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-telephone-fill me-2"></i>
                        <a href="tel:+86-21-12345678" class="text-light text-decoration-none">+86-21-12345678</a>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-clock-fill me-2"></i>
                        工作时间: 周一至周五 9:00-18:00
                    </li>
                </ul>
            </div>
        </div>
        <hr class="my-4 bg-light">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'SEO外链服务平台') }}. 版权所有</p>
            </div>
            <div class="col-md-6 text-md-end">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item"><a href="#" class="text-light text-decoration-none">隐私政策</a></li>
                    <li class="list-inline-item"><span class="text-muted mx-2">|</span></li>
                    <li class="list-inline-item"><a href="#" class="text-light text-decoration-none">服务条款</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>