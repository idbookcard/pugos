<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // 获取推荐套餐
        $featuredPackages = Package::where('active', 1)
            ->where('is_featured', 1)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();
            
        return view('home', compact('featuredPackages'));
    }
}
