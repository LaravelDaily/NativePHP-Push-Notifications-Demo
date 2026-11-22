<?php

use Database\Seeders\CalendarEventSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        (new CalendarEventSeeder)->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('calendar_events')->whereIn('id', [
            'studio-session', 'design-review', 'community-live', 'coffee-walk',
            'publish-video', 'planning', 'meetup', 'review',
        ])->delete();
    }
};
