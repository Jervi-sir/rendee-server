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

        if ($users->isEmpty()) {
            return;
        }

        $notificationTemplates = [
            [
                'title' => 'Rendez-vous confirmé',
                'body' => 'Votre rendez-vous a été confirmé par le professionnel de santé.',
                'type' => 'booking_confirmed',
            ],
            [
                'title' => 'Rappel de rendez-vous',
                'body' => 'N\'oubliez pas votre rendez-vous prévu pour demain.',
                'type' => 'booking_reminder',
            ],
            [
                'title' => 'Nouveau message reçu',
                'body' => 'Vous avez reçu un nouveau message concernant votre consultation.',
                'type' => 'message',
            ],
            [
                'title' => 'Mise à jour du profil',
                'body' => 'Votre profil a été vérifié et mis à jour avec succès.',
                'type' => 'profile_updated',
            ],
            [
                'title' => 'Demande de reprogrammation',
                'body' => 'Un nouvel horaire vous a été proposé pour votre consultation.',
                'type' => 'reschedule_request',
            ],
            [
                'title' => 'Bienvenue sur Rendee',
                'body' => 'Bienvenue sur votre plateforme de prise de rendez-vous médicaux en Algérie.',
                'type' => 'welcome',
            ],
        ];

        foreach ($users as $user) {
            $count = fake()->numberBetween(1, 4);
            $selectedTemplates = fake()->randomElements($notificationTemplates, $count);

            foreach ($selectedTemplates as $template) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => $template['title'],
                    'body' => $template['body'],
                    'type' => $template['type'],
                    'data' => [
                        'action_url' => '/dashboard',
                        'timestamp' => now()->toISOString(),
                    ],
                    'is_read' => fake()->boolean(40),
                ]);
            }
        }
    }
}
