<?php

use Illuminate\Database\Migrations\Migration;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Stylist\Models\StylistSchedule;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $stylists = Stylist::all();
        foreach ($stylists as $stylist) {
            for ($day = 0; $day <= 6; $day++) {
                $exists = StylistSchedule::where('stylist_id', $stylist->id)
                    ->where('day_of_week', $day)
                    ->exists();

                if (!$exists) {
                    StylistSchedule::create([
                        'stylist_id' => $stylist->id,
                        'day_of_week' => $day,
                        'start_time' => '10:00:00',
                        'end_time' => '20:00:00',
                        'break_start' => null,
                        'break_end' => null,
                        'is_working' => true,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to preserve operational data
    }
};
