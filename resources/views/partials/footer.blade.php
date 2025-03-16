<footer class="bg-dark text-white py-5">
  <div class="container">
    <div class="row gy-4">
      <!-- About Us Column -->
      <div class="col-lg-3 col-md-6">
        <h5 class="fw-bold mb-3">关于我们</h5>
        <p class="text-secondary small">
          我们是专业的SEO外链服务提供商，致力于帮助网站提升搜索引擎排名，增加曝光率和流量。
        </p>
        <div class="mt-3">
          <a href="#" class="text-secondary me-3">
            <i class="bi bi-github fs-5"></i>
          </a>
          <a href="#" class="text-secondary me-3">
            <i class="bi bi-facebook fs-5"></i>
          </a>
          <a href="#" class="text-secondary">
            <i class="bi bi-instagram fs-5"></i>
          </a>
        </div>
      </div>

      <!-- Quick Links Column -->
      <div class="col-lg-3 col-md-6">
        <h5 class="fw-bold mb-3">快速链接</h5>
        <ul class="list-unstyled">
          <li class="mb-2">
            <a href="{{ route('home') }}" class="text-secondary text-decoration-none">
              <i class="bi bi-chevron-right small me-1"></i>首页
            </a>
          </li>
          <li class="mb-2">
            <a href="{{ route('packages.index') }}" class="text-secondary text-decoration-none">
              <i class="bi bi-chevron-right small me-1"></i>外链套餐
            </a>
          </li>
          <li class="mb-2">
            <a href="#" class="text-secondary text-decoration-none">
              <i class="bi bi-chevron-right small me-1"></i>成功案例
            </a>
          </li>
          <li class="mb-2">
            <a href="#" class="text-secondary text-decoration-none">
              <i class="bi bi-chevron-right small me-1"></i>常见问题
            </a>
          </li>
          <li class="mb-2">
            <a href="#" class="text-secondary text-decoration-none">
              <i class="bi bi-chevron-right small me-1"></i>联系我们
            </a>
          </li>
        </ul>
      </div>

      <!-- Contact Us Column -->
      <div class="col-lg-3 col-md-6">
        <h5 class="fw-bold mb-3">联系我们</h5>
        <ul class="list-unstyled">
          <li class="mb-3 d-flex">
            <i class="bi bi-envelope text-secondary me-2"></i>
            <span class="text-secondary">support@seoservice.com</span>
          </li>
          <li class="mb-3 d-flex">
            <i class="bi bi-telephone text-secondary me-2"></i>
            <span class="text-secondary">+86 123 4567 8901</span>
          </li>
          <li class="mb-3 d-flex">
            <i class="bi bi-geo-alt text-secondary me-2"></i>
            <span class="text-secondary">北京市朝阳区123号</span>
          </li>
        </ul>
      </div>

      <!-- Customer Support Column -->
      <div class="col-lg-3 col-md-6">
        <h5 class="fw-bold mb-3">客户支持</h5>
        <div class="bg-secondary bg-opacity-25 p-3 rounded">
          <p class="small text-light mb-3">扫描下方二维码，添加客服微信</p>
          <div class="bg-white p-2 rounded" style="width: 100px; height: 100px; margin: 0 auto;">
            <!-- Replace with actual QR code image -->
            <i class="bi bi-qr-code text-secondary d-flex justify-content-center align-items-center h-100" style="font-size: 4rem;"></i>
          </div>
          <p class="text-center text-secondary small mt-2">工作时间: 9:00-18:00</p>
        </div>
      </div>
    </div>

    <!-- Footer Bottom -->
    <div class="border-top border-secondary mt-4 pt-4 text-center small text-secondary">
      <p>© {{ date('Y') }} SEO外链服务. 保留所有权利.</p>
      <div class="mt-2">
        <a href="#" class="text-secondary text-decoration-none mx-2">服务条款</a>
        <a href="#" class="text-secondary text-decoration-none mx-2">隐私政策</a>
        <a href="#" class="text-secondary text-decoration-none mx-2">退款政策</a>
      </div>
    </div>
  </div>
</footer>