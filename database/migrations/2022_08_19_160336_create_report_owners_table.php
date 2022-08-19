<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId("id_owner")->constrained("users","id")->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId("id_user")->constrained("users","id")->cascadeOnDelete()->cascadeOnUpdate();
            $table->text("report");
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
        Schema::dropIfExists('report_owners');
    }
}
