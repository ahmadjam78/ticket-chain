<?php

namespace Database\Seeders;

use App\Domains\User\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\Models\TicketMessage;
use App\Domains\Ticket\Models\TicketCategory;

use App\Domains\Ticket\States\PendingLevel1;
use App\Domains\Ticket\States\PendingLevel2;
use App\Domains\Ticket\States\Rejected;
use App\Domains\Ticket\States\Approved;
use App\Domains\Ticket\States\Closed;
use Illuminate\Support\Arr;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::role('customer')->get();
        $categories = TicketCategory::all();

        $priorities = ['low','medium','high'];
        $statuses = [PendingLevel1::class, PendingLevel2::class, Rejected::class, Approved::class, Closed::class];

        foreach ($customers as $customer) {
            for ($i = 1; $i <= 2; $i++) {
                $category = $categories->random();

                $ticket = Ticket::create([
                    'user_id' => $customer->id,
                    'category_id' => $category->id,
                    'subject' => "Sample Ticket $i for {$customer->name}",
                    'status' => Arr::random($statuses),
                    'priority' => Arr::random($priorities),
                ]);

                TicketMessage::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $customer->id,
                    'message' => "This is the initial message for ticket #{$ticket->id}.",
                ]);
            }
        }
    }
}
