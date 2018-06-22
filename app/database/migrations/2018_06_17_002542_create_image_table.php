<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateImageTable extends Migration
{
    public function up()
    {
        Schema::create('image', function (Blueprint $table) {
            $table->string('id', 36)->nullable(false);
            $table->primary(['id']);
            $table->timestamps();
            $table->string('uploader_user_id', 36)->nullable(false);
            $table->foreign('uploader_user_id')->references('id')->on('user');
            $table->string('location_id', 36)->nullable(false);
            $table->foreign('location_id')->references('id')->on('location');
        });
        /*
        Unfortunately, if we can't make a 16MB blob type with Laravel's "bianry" type.
        Instead, we use raw SQL.

        To learn more, see: https://stackoverflow.com/questions/20089652/mediumblob-in-laravel-database-schema
        */
        DB::statement("ALTER TABLE image ADD raw_data MEDIUMBLOB");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('image');
    }
}
