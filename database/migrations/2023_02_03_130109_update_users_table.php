<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('token');
            $table->integer('connection_id');
            $table->enum('user_status', ['Offline', 'Online']);
            $table->string('user_image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('token')->default('Null');
            $table->dropColumn('connection_id')->default('Null');
            $table->dropColumn('user_status')->default('Null');
            $table->dropColumn('user_image')->default('Null');
        });
    }
};
