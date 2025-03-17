<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
    <div class="p-6">
        <div class="flex justify-between items-start">
            <h3 class="text-xl font-semibold text-gray-900">{{ $package->name }}</h3>
            @if($package->is_featured)
                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">热门</span>
            @endif
        </div>
        
        <div class="mt-2">
            <span class="text-2xl font-bold text-gray-900">¥{{ $package->price }}</span>
            @if($package->original_price && $package->original_price > $package->price)
                <span class="text-sm line-through text-gray-500 ml-2">¥{{ $package->original_price }}</span>
            @endif
        </div>
        
        <p class="mt-2 text-gray-600 text-sm">{{ Str::limit($package->description, 100) }}</p>
        
        <div class="mt-4">
            <h4 class="text-sm font-medium text-gray-900">包含特性：</h4>
            <ul class="mt-2 space-y-1">
            @foreach(is_array($package->features) ? $package->features : json_decode($package->features, true) ?? [] as $feature)
            <li class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-1.5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        {{ $feature }}
                    </li>
                @endforeach
            </ul>
        </div>
        
        <div class="mt-6 flex justify-between items-center">
            <span class="text-sm text-gray-500">交付时间: {{ $package->delivery_days }}天</span>
            <a href="{{ route('packages.show', $package->slug) }}" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                查看详情
            </a>
        </div>
    </div>
</div>