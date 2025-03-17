<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailToThirdPartyApiSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('third_party_api_settings', function (Blueprint $table) {
            $table->string('email')->nullable()->after('api_secret')->comment('API账户关联的邮箱');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('third_party_api_settings', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
}