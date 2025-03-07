<?php
// app/Http/Controllers/Admin/InvoiceController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }
    
    public function index(Request $request)
    {
        $query = Invoice::with('user');
        
        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('tax_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('type') && $request->type != '') {
            $query->where('invoice_type', $request->type);
        }
        
        // Sort by creation date, newest first
        $query->orderBy('created_at', 'desc');
        
        $invoices = $query->paginate(15);
        
        // Get counts for each status
        $statusCounts = [
            'pending' => Invoice::where('status', 'pending')->count(),
            'approved' => Invoice::where('status', 'approved')->count(),
            'rejected' => Invoice::where('status', 'rejected')->count(),
            'sent' => Invoice::where('status', 'sent')->count(),
        ];
        
        return view('admin.invoices.index', compact('invoices', 'statusCounts'));
    }
    
    public function show(Invoice $invoice)
    {
        $invoice->load('user');
        return view('admin.invoices.show', compact('invoice'));
    }
    
    public function approve(Invoice $invoice)
    {
        // Only pending invoices can be approved
        if ($invoice->status !== 'pending') {
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('error', 'Only pending invoices can be approved.');
        }
        
        $invoice->status = 'approved';
        $invoice->save();
        
        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice approved successfully. Now you can generate and send the invoice.');
    }
    
    public function reject(Request $request, Invoice $invoice)
    {
        // Validate the request
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        // Only pending invoices can be rejected
        if ($invoice->status !== 'pending') {
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('error', 'Only pending invoices can be rejected.');
        }
        
        $invoice->status = 'rejected';
        $invoice->rejection_reason = $request->rejection_reason;
        $invoice->save();
        
        // Notify the user via email
        try {
            Mail::to($invoice->email)->send(new InvoiceMail($invoice, 'rejected'));
        } catch (\Exception $e) {
            // Log the error but continue
            \Log::error('Failed to send invoice rejection email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice rejected successfully and user has been notified.');
    }
    
    public function uploadInvoice(Request $request, Invoice $invoice)
    {
        // Validate the request
        $request->validate([
            'invoice_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);
        
        // Only approved invoices can have files uploaded
        if ($invoice->status !== 'approved') {
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('error', 'Only approved invoices can have files uploaded.');
        }
        
        // Upload the file
        $path = $request->file('invoice_file')->store('invoices');
        
        // Update the invoice
        $invoice->file_path = $path;
        $invoice->status = 'sent';
        $invoice->sent_at = now();
        $invoice->save();
        
        // Notify the user via email
        try {
            Mail::to($invoice->email)->send(new InvoiceMail($invoice, 'sent'));
        } catch (\Exception $e) {
            // Log the error but continue
            \Log::error('Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice file uploaded successfully and user has been notified.');
    }
    
    public function viewInvoice(Invoice $invoice)
    {
        // Check if invoice has a file
        if (!$invoice->file_path) {
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('error', 'No invoice file has been uploaded yet.');
        }
        
        // Return the file for viewing
        return response()->file(storage_path('app/' . $invoice->file_path));
    }
    
    public function destroy(Invoice $invoice)
    {
        // Delete the file if it exists
        if ($invoice->file_path && Storage::exists($invoice->file_path)) {
            Storage::delete($invoice->file_path);
        }
        
        $invoice->delete();
        
        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }
}