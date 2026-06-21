<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pops', function (Blueprint $table) {
            $table->boolean('show_starting_from')->default(false)->after('additional_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pops', function (Blueprint $table) {
            $table->dropColumn('show_starting_from');
        });
    }
};
