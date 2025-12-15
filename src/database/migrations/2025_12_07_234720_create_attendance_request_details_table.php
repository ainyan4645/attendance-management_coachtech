<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRequestDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_request_id')
                ->constrained()
                ->onDelete('cascade');
            $table->string('field');    // 修正項目名（例: clock_in, clock_out, break_start_1, break_end_1, remark）
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
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
        Schema::dropIfExists('attendance_request_details');
    }
}
