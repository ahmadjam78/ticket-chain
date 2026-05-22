<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Domains\Ticket\Resources\V1\Customer\TicketMessageResource;
use App\Domains\Ticket\Resources\V1\Customer\TicketResource;
use App\Domains\User\Models\User;
use App\Domains\User\Notifications\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\DTOs\CreateTicketDTO;
use App\Domains\Ticket\DTOs\ReplyTicketDTO;
use App\Domains\Ticket\Services\TicketService;
use App\Domains\Ticket\Repositories\TicketRepository;
use App\Http\Requests\Ticket\CreateTicketRequest;
use App\Http\Requests\Ticket\ReplyTicketRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Class TicketController
 *
 * Handles customer-facing ticket operations including listing,
 * creating, viewing details, and replying to support tickets.
 * All methods enforce authorization via Laravel Policies.
 *
 * @package App\Http\Controllers\Api\V1\Customer
 */
class TicketController extends Controller
{
    /**
     * TicketController constructor.
     *
     * No dependencies are injected directly; services and repositories
     * are injected via method parameters to follow Laravel's
     * dynamic method injection pattern.
     */
    public function __construct()
    {
    }

    /**
     * List all tickets belonging to the authenticated customer.
     *
     * Retrieves a paginated list of tickets for the current user.
     *
     * @param Request $request The HTTP request instance (authenticated user is accessible).
     * @param TicketRepository $repository Repository for ticket data operations.
     * @return AnonymousResourceCollection A collection of ticket resources.
     */
    public function index(
        Request $request,
        TicketRepository $repository
    ): AnonymousResourceCollection
    {

        $tickets = $repository->paginateForUser($request->user());

        return TicketResource::collection($tickets);
    }

    /**
     * Create a new support ticket.
     *
     * Validates the request, converts input to a DTO, and uses the service
     * to create a new ticket. Authorization is checked via policy.
     *
     * @param CreateTicketRequest $request The validated create ticket request.
     * @param TicketService $service The ticket service handling business logic.
     * @return TicketResource The newly created ticket resource.
     * @throws \Illuminate\Auth\Access\AuthorizationException If the user is not authorized to create a ticket.
     */
    public function store(
        CreateTicketRequest $request,
        TicketService $service
    ): TicketResource
    {

        $this->authorize('create', Ticket::class);

        $dto = CreateTicketDTO::fromArray([
            'user_id'     => $request->user()->id,
            'category_id' => $request->validated()['category_id'],
            'subject'     => $request->validated()['subject'],
            'description' => $request->validated()['description'],
            'priority'    => $request->validated()['priority'],
            'attachments' => $request->file('attachments', []),
        ]);

        $ticket = $service->create($dto);

        return new TicketResource($ticket);
    }

    /**
     * Display the specified ticket with its messages.
     *
     * Loads the ticket's messages, user, and media attachments,
     * then returns the ticket resource as JSON.
     *
     * @param Ticket $ticket The ticket model (route model binding).
     * @return JsonResponse JSON response containing the ticket resource.
     * @throws \Illuminate\Auth\Access\AuthorizationException If the user is not authorized to view this ticket.
     */
    public function show(Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'messages' => function ($query) {
                $query->with(['user', 'media'])
                    ->orderByDesc('created_at');
            }
        ]);

        return response()->json(new TicketResource($ticket));
    }

    /**
     * Reply to an existing ticket.
     *
     * Creates a new message and optionally attaches files.
     * Authorization is checked via policy.
     *
     * @param ReplyTicketRequest $request The validated reply request.
     * @param Ticket $ticket The ticket being replied to (route model binding).
     * @param TicketService $service The ticket service handling reply logic.
     * @return TicketMessageResource The created message resource.
     * @throws \Illuminate\Auth\Access\AuthorizationException If the user is not authorized to reply to this ticket.
     */
    public function reply(
        ReplyTicketRequest $request,
        Ticket $ticket,
        TicketService $service
    ): TicketMessageResource
    {

        $this->authorize('reply', $ticket);

        $dto = ReplyTicketDTO::fromArray([
            'ticket_id'   => $ticket->id,
            'user_id'     => $request->user()->id,
            'message'     => $request->validated()['message'],
            'attachments' => $request->file('attachments', []),
        ]);

        $message = $service->reply($dto, $ticket, $request->user());

        return new TicketMessageResource($message);
    }
}
