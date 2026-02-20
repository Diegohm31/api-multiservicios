<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dateTime('fecha_emision')->change();
            $table->dateTime('fecha_validacion')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->date('fecha_emision')->change();
            $table->date('fecha_validacion')->nullable()->change();
        });
    }
};
