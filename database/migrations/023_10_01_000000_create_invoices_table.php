<?php
// database/migrations/2023_10_01_000000_create_invoices_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->enum('invoice_type', ['regular', 'vat'])->default('regular');
            $table->string('title');
            $table->string('tax_number')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('email');
            $table->text('address')->nullable();
            $table->text('bank_info')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'sent'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}