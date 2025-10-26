<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')
                  ->constrained('attendances')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->text('correction_content');
            $table->text('reason');
            $table->string('status', 20);
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
        Schema::dropIfExists('attendance_correction_requests');
    }
}
