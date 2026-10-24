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
        Schema::create('cms_contents', function (Blueprint $table) {
            $table->id();
            $table->string('section', 40);
            $table->string('item_key', 60);
            $table->string('type', 10);
            $table->longText('value');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['section', 'item_key']);
            $table->index('section');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_contents');
    }
};
