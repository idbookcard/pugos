<?php
// app/Http/Controllers/Customer/InvoiceController.php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $user = Auth::user();
        $invoices = Invoice::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Calculate invoice-eligible amount (sum of deposit transactions)
        $eligibleAmount = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'deposit')
            ->where('status', 'completed')
            ->sum('amount');
            
        // Calculate already invoiced amount
        $invoicedAmount = Invoice::where('user_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->sum('amount');
            
        // Calculate available amount for invoicing
        $availableAmount = max(0, $eligibleAmount - $invoicedAmount);
        
        return view('customer.invoices.index', compact('invoices', 'eligibleAmount', 'invoicedAmount', 'availableAmount'));
    }
    
    public function create()
    {
        $user = Auth::user();
        
        // Calculate invoice-eligible amount
        $eligibleAmount = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'deposit')
            ->where('status', 'completed')
            ->sum('amount');
            
        // Calculate already invoiced amount
        $invoicedAmount = Invoice::where('user_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->sum('amount');
            
        // Calculate available amount for invoicing
        $availableAmount = max(0, $eligibleAmount - $invoicedAmount);
        
        if ($availableAmount <= 0) {
            return redirect()->route('customer.invoices.index')
                ->with('error', 'You have no eligible deposits available for invoicing.');
        }
        
        return view('customer.invoices.create', compact('availableAmount', 'user'));
    }
    
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Validate request
        $request->validate([
            'invoice_type' => 'required|in:regular,vat',
            'title' => 'required|string|max:255',
            'tax_number' => $request->invoice_type == 'vat' ? 'required|string|max:50' : 'nullable|string|max:50',
            'amount' => 'required|numeric|min:1',
            'email' => 'required|email|max:255',
            'address' => $request->invoice_type == 'vat' ? 'required|string|max:500' : 'nullable|string|max:500',
            'bank_info' => $request->invoice_type == 'vat' ? 'required|string|max:500' : 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Check available amount
        $eligibleAmount = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'deposit')
            ->where('status', 'completed')
            ->sum('amount');
            
        $invoicedAmount = Invoice::where('user_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->sum('amount');
            
        $availableAmount = max(0, $eligibleAmount - $invoicedAmount);
        
        if ($request->amount > $availableAmount) {
            return redirect()->route('customer.invoices.create')
                ->with('error', "You can only request an invoice for up to {$availableAmount} units.")
                ->withInput();
        }
        
        // Create invoice
        $invoice = Invoice::create([
            'user_id' => $user->id,
            'invoice_number' => 'INV-' . date('Y') . '-' . strtoupper(Str::random(6)),
            'invoice_type' => $request->invoice_type,
            'title' => $request->title,
            'tax_number' => $request->tax_number,
            'amount' => $request->amount,
            'email' => $request->email,
            'address' => $request->address,
            'bank_info' => $request->bank_info,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);
        
        return redirect()->route('customer.invoices.index')
            ->with('success', 'Invoice request submitted successfully. Your request is pending approval.');
    }
    
    public function show(Invoice $invoice)
    {
        // Security check: ensure the invoice belongs to the authenticated user
        if ($invoice->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('customer.invoices.show', compact('invoice'));
    }
    
    public function download(Invoice $invoice)
    {
        // Security check: ensure the invoice belongs to the authenticated user
        if ($invoice->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if invoice has a file
        if (!$invoice->file_path) {
            return redirect()->route('customer.invoices.show', $invoice)
                ->with('error', 'No invoice file is available for download yet.');
        }
        
        // Return the file for download
        return response()->download(storage_path('app/' . $invoice->file_path));
    }
}