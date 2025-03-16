<?php

// app/Http/Controllers/Admin/ReportController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('master');     }
    
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|max:50',
            'report_data' => 'required|json',
            'source' => 'nullable|string|max:50',
            'placed_at' => 'nullable|date',
        ]);
        
        $report = OrderReport::create([
            'order_id' => $order->id,
            'status' => $request->status,
            'report_data' => $request->report_data,
            'source' => $request->source ?? 'manual',
            'placed_at' => $request->placed_at ?? now(),
        ]);
        
        // If this is the first report and the order is still pending, update to processing
        if ($order->reports()->count() == 1 && $order->status == 'pending') {
            $oldStatus = $order->status;
            $order->status = 'processing';
            $order->save();
            
            // Log status change
            $order->statusLogs()->create([
                'old_status' => $oldStatus,
                'new_status' => 'processing',
                'notes' => 'Automatically updated to processing after first report added',
                'created_by' => Auth::id(),
            ]);
        }
        
        return redirect()->route('master.orders.show', $order)
            ->with('success', 'Report added successfully.');
    }
    
    public function destroy(OrderReport $report)
    {
        $order = $report->order;
        
        $report->delete();
        
        return redirect()->route('master.orders.show', $order)
            ->with('success', 'Report deleted successfully.');
    }
}