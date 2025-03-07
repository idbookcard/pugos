<?php

// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $user = Auth::user();
        $recentOrders = $user->orders()->latest()->take(5)->get();
        $recentTransactions = $user->transactions()->latest()->take(5)->get();
        
        return view('dashboard.index', compact('user', 'recentOrders', 'recentTransactions'));
    }
    
    public function profile()
    {
        $user = Auth::user();
        return view('dashboard.profile', compact('user'));
    }
    
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);
        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        
        if ($user->profile) {
            $user->profile->update([
                'company' => $request->company,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
        } else {
            UserProfile::create([
                'user_id' => $user->id,
                'company' => $request->company,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
        }
        
        if ($request->password) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }
        
        return redirect()->route('dashboard.profile')
            ->with('success', 'Profile updated successfully.');
    }
}