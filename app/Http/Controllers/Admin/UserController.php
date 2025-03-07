<?php
// app/Http/Controllers/Admin/UserController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }
    
    public function index()
    {
        $users = User::with('profile')->paginate(15);
        return view('admin.users.index', compact('users'));
    }
    
    public function create()
    {
        return view('admin.users.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'balance' => 'nullable|numeric|min:0',
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'balance' => $request->balance ?? 0.00,
        ]);
        
        UserProfile::create([
            'user_id' => $user->id,
            'company' => $request->company,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);
        
        if ($request->balance > 0) {
            Transaction::create([
                'user_id' => $user->id,
                'transaction_type' => 'adjustment',
                'amount' => $request->balance,
                'status' => 'completed',
                'notes' => 'Initial balance set by admin',
            ]);
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }
    
    public function edit(User $user)
    {
        $user->load('profile');
        return view('admin.users.edit', compact('user'));
    }
    
    public function update(Request $request, User $user)
    {
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
                'password' => 'required|string|min:8',
            ]);
            
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }
    
    public function adjustBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|not_in:0',
            'notes' => 'required|string',
        ]);
        
        $user->balance += $request->amount;
        $user->save();
        
        Transaction::create([
            'user_id' => $user->id,
            'transaction_type' => 'adjustment',
            'amount' => $request->amount,
            'status' => 'completed',
            'notes' => $request->notes,
        ]);
        
        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'Balance adjusted successfully.');
    }
    
    public function destroy(User $user)
    {
        // Check if user has orders before deletion
        if ($user->orders()->count() > 0) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete user with orders.');
        }
        
        $user->profile()->delete();
        $user->transactions()->delete();
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}