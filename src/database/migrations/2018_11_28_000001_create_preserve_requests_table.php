<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreserveRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preserve_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('preserve_response_id')->nullable()->index();
            $table->string('method')->index();
            $table->string('uri')->index();
            $table->string('query');
            $table->longText('body');
            $table->text('headers');
            $table->timestamps();
            $table->foreign('preserve_response_id')
                  ->references('id')
                  ->on('preserve_responses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('preserve_requests');
    }
}
