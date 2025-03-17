<header class="bg-white shadow-sm">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="font-bold text-xl text-blue-600">SEO外链服务</a>
                
                <nav class="hidden md:flex ml-10 space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-600' : '' }}">
                        首页
                    </a>
                    <a href="{{ route('packages.index') }}" class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('packages.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                        外链套餐
                    </a>
                    <a href="#" class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        成功案例
                    </a>
                    <a href="#" class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        常见问题
                    </a>
                </nav>
            </div>
            
            <div class="flex items-center">
                @auth
                    <a href="{{ route('wallet.index') }}" class="text-gray-700 hover:text-blue-600 flex items-center mr-4">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        <span>¥{{ number_format(auth()->user()->balance + auth()->user()->gift_balance, 2) }}</span>
                    </a>
                    
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="text-gray-700 hover:text-blue-600 flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span>{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                            <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">我的订单</a>
                            <a href="{{ route('wallet.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">我的钱包</a>
                            <a href="{{ route('invoices.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">发票管理</a>
                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">个人设置</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">退出登录</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        登录
                    </a>
                    <a href="{{ route('register') }}" class="ml-4 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        注册
                    </a>
                @endauth
            </div>
            
            <div class="md:hidden flex items-center">
                <button x-data="{}" @click="$dispatch('toggle-mobile-menu')" type="button" class="text-gray-500 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- 移动端菜单 -->
        <div x-data="{ open: false }" x-show="open" @toggle-mobile-menu.window="open = !open" class="md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-600' : 'text-gray-900 hover:text-blue-600' }}">
                    首页
                </a>
                <a href="{{ route('packages.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('packages.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-900 hover:text-blue-600' }}">
                    外链套餐
                </a>
                <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:text-blue-600">
                    成功案例
                </a>
                <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:text-blue-600">
                    常见问题
                </a>
                
                @auth
                    <div class="border-t border-gray-200 my-2 pt-2">
                        <a href="{{ route('orders.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:text-blue-600">
                            我的订单
                        </a>
                        <a href="{{ route('wallet.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:text-blue-600">
                            我的钱包 (¥{{ number_format(auth()->user()->balance + auth()->user()->gift_balance, 2) }})
                        </a>
                        <a href="{{ route('invoices.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:text-blue-600">
                            发票管理
                        </a>
                        <a href="{{ route('profile') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:text-blue-600">
                            个人设置
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:text-blue-600">
                                退出登录
                            </button>
                        </form>
                    </div>
                @else
                    <div class="border-t border-gray-200 my-2 pt-2">
                        <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:text-blue-600">
                            登录
                        </a>
                        <a href="{{ route('register') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:text-blue-600">
                            注册
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</header>