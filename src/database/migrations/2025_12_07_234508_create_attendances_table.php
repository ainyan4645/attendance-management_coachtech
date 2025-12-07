<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->dateTime('clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();
            $table->integer('total_break_minutes')->default(0);
            $table->integer('total_work_minutes')->default(0);
            $table->enum('last_request_status', ['none', 'pending', 'approved'])    // 申請があるかどうかのフラグ
                ->default('none');
            $table->text('remark')->nullable(); // 承認後のみ反映される備考

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
        Schema::dropIfExists('attendances');
    }
}
