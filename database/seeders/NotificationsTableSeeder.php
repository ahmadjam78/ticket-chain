<?php

namespace Database\Seeders;

use App\Domains\User\Models\User;
use App\Domains\User\Notifications\AdminNotification;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class NotificationsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $users = User::all();

        foreach ($users as $user) {
            for ($i = 0; $i < 12; $i++) {
                $title = $this->getRandomTitle($faker);
                $message = $this->getRandomMessage($faker);
                $type = $this->getRandomType();

                $user->notify(new AdminNotification($title, $message, $type));

                if ($i % 3 == 0) {
                    $notification = $user->notifications()->latest()->first();
                    $notification->created_at = now()->subDays(rand(1, 30));
                    $notification->save();
                }
            }
        }

        $this->command->info('Notifications seeded successfully!');
    }

    private function getRandomTitle($faker)
    {
        $titles = [
            'New ticket created',
            'Ticket status updated',
            'System maintenance',
            'Security alert',
            'New user registered',
            'Payment received',
            'Server overload warning',
            'Backup completed',
            'Cron job failed',
            'SSL certificate expiry',
            'User reported issue',
            'Task assigned to you',
            'Database backup successful',
        ];
        return $faker->randomElement($titles);
    }

    private function getRandomMessage($faker)
    {
        $messages = [
            'Ticket #' . rand(1000,9999) . ' has been created by a customer.',
            'Ticket status changed from open to in-progress.',
            'Scheduled maintenance on Sunday from 2 AM to 4 AM.',
            'Multiple failed login attempts detected from IP ' . $faker->ipv4,
            'New user ' . $faker->userName . ' has joined the platform.',
            'Invoice #' . rand(10000,99999) . ' has been paid successfully.',
            'CPU usage exceeded 85% on server web-01.',
            'Daily backup completed successfully with size 2.4GB.',
            'The cron job "email-digest" failed to run. Check logs.',
            'SSL certificate for domain ' . $faker->domainName . ' expires in 14 days.',
            'User reported a problem with checkout process.',
            'A new task "Review logs" has been assigned to you.',
            'Database backup stored on S3: backup_' . now()->format('Ymd') . '.sql',
        ];
        return $faker->randomElement($messages);
    }

    private function getRandomType()
    {
        $types = ['info', 'success', 'warning', 'danger'];
        return $types[array_rand($types)];
    }
}
