<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Populate existing outlets with default 7-day schedule
        $outlets = DB::table('outlets')->get();

        foreach ($outlets as $outlet) {
            $open = $outlet->open_time ? date('H:i', strtotime($outlet->open_time)) : '09:00';
            $close = $outlet->close_time ? date('H:i', strtotime($outlet->close_time)) : '21:00';

            $schedule = [];
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

            foreach ($days as $day) {
                $schedule[$day] = [
                    'is_open' => true,
                    'open' => $open,
                    'close' => $close,
                ];
            }

            DB::table('outlets')->where('id', $outlet->id)->update([
                'opening_hours' => json_encode($schedule),
            ]);
        }

        // Drop the old columns
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn(['open_time', 'close_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->time('open_time')->nullable()->default('09:00:00')->after('phone');
            $table->time('close_time')->nullable()->default('21:00:00')->after('open_time');
        });

        // Revert data
        $outlets = DB::table('outlets')->get();
        foreach ($outlets as $outlet) {
            if ($outlet->opening_hours) {
                $schedule = json_decode($outlet->opening_hours, true);
                // use monday's schedule as fallback
                $monday = $schedule['monday'] ?? null;
                if ($monday) {
                    DB::table('outlets')->where('id', $outlet->id)->update([
                        'open_time' => $monday['open'].':00',
                        'close_time' => $monday['close'].':00',
                    ]);
                }
            }
        }
    }
};
