<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('stylists')->update(['phone' => '6282247431493']);
    }

    public function down(): void
    {
        //
    }
};
