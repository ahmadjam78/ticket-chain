<?php

namespace Tests\Unit;

use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\States\Approved;
use App\Domains\Ticket\States\Closed;
use App\Domains\Ticket\States\Failed;
use App\Domains\Ticket\States\PendingLevel1;
use App\Domains\Ticket\States\PendingLevel2;
use App\Domains\Ticket\States\Rejected;
use App\Domains\Ticket\States\TicketState;
use App\Domains\User\Models\User;
use App\Shared\Enums\TicketPriority;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\ModelStates\Exceptions\TransitionNotFound;
use PHPUnit\Framework\Attributes\Test;

class TicketStateTransitionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: Create a ticket with a specific state.
     */
    private function createTicketWithState(string $stateClass): Ticket
    {
        // Manually create user (without factory)
        $user = new User();
        $user->name = 'Test User';
        $user->email = 'test_' . uniqid() . '@example.com';
        $user->password = bcrypt('password');
        $user->save();

        // Assign role if needed (since role field is not in the table and Spatie Roles is used)
        // May not be needed because the test only checks ticket state

        $ticket = new Ticket([
            'user_id' => $user->id,
            'subject' => 'Test Ticket Subject',
            'priority' => \App\Shared\Enums\TicketPriority::MEDIUM,
        ]);
        $ticket->status = $stateClass;
        $ticket->save();

        return $ticket;
    }

    #[Test]
    public function it_allows_valid_transitions_from_pending_level1()
    {
        $ticket = $this->createTicketWithState(PendingLevel1::class);

        // Transition to PendingLevel2 should be allowed
        $ticket->status->transitionTo(PendingLevel2::class);
        $this->assertInstanceOf(PendingLevel2::class, $ticket->fresh()->status);

        // Revert to PendingLevel1 to test rejection
        $ticket = $this->createTicketWithState(PendingLevel1::class);
        $ticket->status->transitionTo(Rejected::class);
        $this->assertInstanceOf(Rejected::class, $ticket->fresh()->status);
    }

    #[Test]
    public function it_allows_valid_transitions_from_pending_level2()
    {
        // Transition to Approved
        $ticket = $this->createTicketWithState(PendingLevel2::class);
        $ticket->status->transitionTo(Approved::class);
        $this->assertInstanceOf(Approved::class, $ticket->fresh()->status);

        // Transition to Rejected
        $ticket = $this->createTicketWithState(PendingLevel2::class);
        $ticket->status->transitionTo(Rejected::class);
        $this->assertInstanceOf(Rejected::class, $ticket->fresh()->status);

        // Transition to Failed
        $ticket = $this->createTicketWithState(PendingLevel2::class);
        $ticket->status->transitionTo(Failed::class);
        $this->assertInstanceOf(Failed::class, $ticket->fresh()->status);
    }

    #[Test]
    public function it_allows_transition_to_closed_from_approved_rejected_failed()
    {
        $states = [Approved::class, Rejected::class, Failed::class];

        foreach ($states as $state) {
            $ticket = $this->createTicketWithState($state);
            $ticket->status->transitionTo(Closed::class);
            $this->assertInstanceOf(Closed::class, $ticket->fresh()->status);
        }
    }

    #[Test]
    public function it_allows_failed_to_failed_and_failed_to_approved_transitions()
    {
        $ticket = $this->createTicketWithState(Failed::class);

        // Transition Failed -> Failed
        $ticket->status->transitionTo(Failed::class);
        $this->assertInstanceOf(Failed::class, $ticket->fresh()->status);

        // Transition Failed -> Approved
        $ticket->status->transitionTo(Approved::class);
        $this->assertInstanceOf(Approved::class, $ticket->fresh()->status);
    }

    #[Test]
    public function it_prevents_invalid_transitions()
    {
        $ticket = $this->createTicketWithState(PendingLevel1::class);
        $this->expectException(TransitionNotFound::class);
        $ticket->status->transitionTo(Approved::class);
    }

    #[Test]
    public function it_prevents_direct_transition_from_pending_level1_to_closed()
    {
        $ticket = $this->createTicketWithState(PendingLevel1::class);
        $this->expectException(TransitionNotFound::class);
        $ticket->status->transitionTo(Closed::class);
    }

    #[Test]
    public function it_prevents_direct_transition_from_pending_level2_to_closed()
    {
        $ticket = $this->createTicketWithState(PendingLevel2::class);
        $this->expectException(TransitionNotFound::class);
        $ticket->status->transitionTo(Closed::class);
    }

    #[Test]
    public function it_prevents_transition_from_approved_to_pending_level2()
    {
        $ticket = $this->createTicketWithState(Approved::class);
        $this->expectException(TransitionNotFound::class);
        $ticket->status->transitionTo(PendingLevel2::class);
    }

    #[Test]
    public function it_prevents_transition_from_closed_to_any_other_state()
    {
        $ticket = $this->createTicketWithState(Closed::class);
        $this->expectException(TransitionNotFound::class);
        $ticket->status->transitionTo(PendingLevel1::class);
    }

    #[Test]
    public function it_correctly_identifies_final_state()
    {
        $closedTicket = $this->createTicketWithState(Closed::class);
        $this->assertTrue($closedTicket->status->isFinal());

        $nonFinalStates = [PendingLevel1::class, PendingLevel2::class, Approved::class, Rejected::class, Failed::class];
        foreach ($nonFinalStates as $stateClass) {
            $ticket = $this->createTicketWithState($stateClass);
            $this->assertFalse($ticket->status->isFinal(), "State {$stateClass} should not be final");
        }
    }

    #[Test]
    public function it_returns_non_empty_label_for_each_state()
    {
        $states = [
            PendingLevel1::class,
            PendingLevel2::class,
            Approved::class,
            Rejected::class,
            Failed::class,
            Closed::class,
        ];

        foreach ($states as $stateClass) {
            $ticket = $this->createTicketWithState($stateClass);
            $label = $ticket->status->label();
            $this->assertIsString($label);
            $this->assertNotEmpty($label, "Label for {$stateClass} is empty");
        }
    }
}
