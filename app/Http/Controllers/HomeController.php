<?php
// app/Http/Controllers/HomeController.php
namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $package = Package::where('active', true)
            ->orderBy('sort_order')
            ->take(6)
            ->get();
      
        $featured  = Package::where('is_featured', true)
            ->where('active', true)
            ->orderBy('sort_order')
            ->take(6)
            ->get();
            
        $singlePackages = Package::where('package_type', 'single')
            ->where('active', true)
            ->orderBy('sort_order')
            ->take(3)
            ->get();
            
        $monthlyPackages = Package::where('package_type', 'monthly')
            ->where('active', true)
            ->orderBy('sort_order')
            ->take(3)
            ->get();
           
        return view('home.index', compact('package','featured', 'singlePackages', 'monthlyPackages'));
    }
}