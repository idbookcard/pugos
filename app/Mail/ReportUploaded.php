<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportUploaded extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $report;
    public $weekNumber;
    public $reportData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order, OrderReport $report)
    {
        $this->order = $order;
        $this->report = $report;
        $this->reportData = json_decode($report->report_data, true);
        $this->weekNumber = $this->reportData['week_number'] ?? null;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = '订单报告上传通知 - ' . $this->order->order_number;
        
        if ($this->weekNumber && $this->order->service_type === 'monthly') {
            $subject = '第' . $this->weekNumber . '周报告上传通知 - ' . $this->order->order_number;
        }
        
        $mail = $this->subject($subject)
                     ->markdown('emails.orders.report-uploaded');
        
        // 如果报告包含附件，添加附件到邮件
        if (isset($this->reportData['file_path'])) {
            $mail->attach(storage_path('app/public/' . $this->reportData['file_path']), [
                'as' => $this->reportData['file_name'] ?? '订单报告.pdf',
            ]);
        }
        
        return $mail;
    }
}