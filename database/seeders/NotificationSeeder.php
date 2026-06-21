<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            foreach (range(1, fake()->numberBetween(1, 4)) as $i) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => fake()->randomElement([
                        'Appointment Reminder',
                        'Booking Confirmed',
                        'Schedule Updated',
                        'New Message',
                        'Promotion',
                    ]),
                    'body' => fake()->sentence(8),
                    'type' => fake()->randomElement(['booking', 'reminder', 'system', 'promotion']),
                    'data' => json_encode(['key' => fake()->uuid(), 'action' => fake()->word()]),
                    'is_read' => fake()->boolean(30),
                ]);
            }
        }
    }
}
