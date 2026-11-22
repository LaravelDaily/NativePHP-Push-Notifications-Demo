<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CalendarEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = now()->startOfDay();

        $events = [
            ['studio-session', 'Record the NativePHP demo', 0, '10:00', 60, 'Home studio', 'CREATE', 'A little PHP. A real Android app. Record the calendar walkthrough and try a reminder on your phone.'],
            ['design-review', 'Calendar design review', 0, '13:30', 45, 'Workspace', 'WORK', 'Review the month view, the daily agenda, and the little details that make the app feel at home on Android.'],
            ['community-live', 'Laravel community livestream', 0, '17:00', 90, 'YouTube Live', 'COMMUNITY', 'Build something useful with the community. Bring your questions about PHP on mobile.'],
            ['coffee-walk', 'Coffee & a long walk', 1, '09:00', 45, 'Around the neighbourhood', 'PERSONAL', 'Step away from the screen, grab a coffee, and make room for a few new ideas.'],
            ['publish-video', 'Publish the new video', 1, '14:00', 30, 'YouTube Studio', 'CREATE', 'One final check of the thumbnail, description, and source code before the video goes live.'],
            ['planning', 'Plan the next build', 3, '11:00', 60, 'Workspace', 'WORK', 'Sketch a small idea, pick the one interaction that matters, and keep the scope simple.'],
            ['meetup', 'PHP developers meetup', 5, '18:00', 120, 'City centre', 'COMMUNITY', 'An evening of demos, conversations, and ideas with local developers.'],
            ['review', 'Monthly review', -3, '15:00', 30, 'Workspace', 'PERSONAL', 'Look back at what shipped and decide what deserves your attention next.'],
        ];

        foreach ($events as [$id, $title, $offset, $time, $duration, $location, $category, $description]) {
            DB::table('calendar_events')->insertOrIgnore([
                'id' => $id,
                'title' => $title,
                'starts_at' => $today->copy()->addDays($offset)->setTimeFromTimeString($time)->toDateTimeString(),
                'duration_minutes' => $duration,
                'location' => $location,
                'category' => $category,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
