<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Ticket\DTOs\ReplyTicketDTO;
use App\Domains\Ticket\Resources\V1\Admin\TicketResource;
use App\Domains\Ticket\Resources\V1\Customer\TicketMessageResource;
use App\Domains\Ticket\Services\TicketService;
use App\Http\Requests\Ticket\ReplyTicketRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\Services\TicketStateService;
use App\Domains\Ticket\Repositories\TicketRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Class TicketController
 *
 * Admin controller for managing support tickets.
 * Provides functionality to list, view, reply, reject, close, mark as pending,
 * and delete tickets. All actions are protected by authorization policies.
 *
 * @package App\Http\Controllers\Api\V1\Admin
 */
class TicketController extends Controller
{
    /**
     * TicketController constructor.
     *
     * No dependencies injected directly; services/repositories are method-injected.
     */
    public function __construct()
    {
    }

    /**
     * List all tickets for admin users.
     *
     * Retrieves a paginated list of tickets, typically including all tickets
     * regardless of owner, with admin-specific filters or sorting.
     *
     * @param Request $request The HTTP request instance.
     * @param TicketRepository $repository Repository for ticket data operations.
     * @return AnonymousResourceCollection A collection of admin ticket resources.
     */
    public function index(
        Request $request,
        TicketRepository $repository
    ): AnonymousResourceCollection
    {

        $tickets = $repository->paginateForAdmin($request->user());

        return TicketResource::collection($tickets);
    }

    /**
     * Display the specified ticket with its messages.
     *
     * Loads the ticket's messages, associated user, and media attachments.
     *
     * @param Ticket $ticket The ticket model (route model binding).
     * @return JsonResponse JSON response containing the ticket resource.
     * @throws \Illuminate\Auth\Access\AuthorizationException If the user is not authorized to view the ticket.
     */
    public function show(Ticket $ticket): JsonResponse
    {
        $ticket->load([
            'messages' => function ($query) {
                $query->with(['user', 'media'])
                    ->orderByDesc('created_at');
            }
        ]);

        return response()->json(new TicketResource($ticket));
    }

    /**
     * Reply to a ticket as an admin.
     *
     * Creates a new message on the ticket and optionally attaches files.
     *
     * @param ReplyTicketRequest $request The validated reply request.
     * @param Ticket $ticket The ticket being replied to.
     * @param TicketService $service The ticket service handling reply logic.
     * @return TicketMessageResource The created message resource.
     * @throws \Illuminate\Auth\Access\AuthorizationException If the user is not authorized to reply to the ticket.
     */
    public function reply(
        ReplyTicketRequest $request,
        Ticket $ticket,
        TicketService $service
    ): TicketMessageResource
    {

        $this->authorize('reply', $ticket);

        $dto = ReplyTicketDTO::fromArray([
            'ticket_id' => $ticket->id,
            'user_id'   => $request->user()->id,
            'message'   => $request->validated()['message'],
            'attachments' => $request->file('attachments', []),
        ]);

        $message = $service->reply($dto, $ticket);

        return new TicketMessageResource($message);
    }

    /**
     * Reject a ticket.
     *
     * Transitions the ticket to a rejected state, optionally with a rejection message.
     *
     * @param Request $request The HTTP request instance containing optional rejection message.
     * @param Ticket $ticket The ticket to reject.
     * @param TicketStateService $service The state service handling status transitions.
     * @return JsonResponse JSON response with success message and updated ticket resource.
     * @throws \Illuminate\Auth\Access\AuthorizationException If the user is not authorized to update the ticket.
     */
    public function reject(
        Request $request,
        Ticket $ticket,
        TicketStateService $service
    ): JsonResponse
    {

        $validated = $request->validate([
            'message' => 'nullable|string|max:1000',
        ]);

        $this->authorize('update', $ticket);

        $service->reject($ticket, $validated['message'] ?? null);

        return response()->json([
            'message' => 'Ticket moved to Rejected.',
            'data' => new TicketResource($ticket->fresh())
        ]);
    }

    /**
     * Close a resolved ticket.
     *
     * Changes the ticket status to closed.
     *
     * @param Request $request The HTTP request instance.
     * @param Ticket $ticket The ticket to close.
     * @param TicketStateService $service The state service handling status transitions.
     * @return JsonResponse JSON response with success message and updated ticket resource.
     * @throws \Illuminate\Auth\Access\AuthorizationException If the user is not authorized to close the ticket.
     */
    public function close(
        Request $request,
        Ticket $ticket,
        TicketStateService $service
    ): JsonResponse {

        $this->authorize('close', $ticket);

        $service->close($ticket, $request->user());

        return response()->json([
            'message' => 'Ticket closed successfully',
            'data' => new TicketResource($ticket->fresh())
        ]);
    }

    /**
     * Mark a ticket as pending.
     *
     * Moves the ticket to a pending state (e.g., awaiting user response).
     *
     * @param Request $request The HTTP request instance.
     * @param Ticket $ticket The ticket to mark as pending.
     * @param TicketStateService $service The state service handling status transitions.
     * @return JsonResponse JSON response with success message and updated ticket resource.
     * @throws \Illuminate\Auth\Access\AuthorizationException If the user is not authorized to update the ticket.
     * @throws \Exception If the state transition fails.
     */
    public function pending(
        Request $request,
        Ticket $ticket,
        TicketStateService $service
    ): JsonResponse {

        $this->authorize('update', $ticket);

        $service->pending($ticket, $request->user());

        return response()->json([
            'message' => 'Ticket moved to Pending',
            'data' => new TicketResource($ticket->fresh())
        ]);
    }

    /**
     * Delete a ticket permanently.
     *
     * Removes the ticket from the database.
     *
     * @param Ticket $ticket The ticket to delete.
     * @return Response HTTP 204 No Content response.
     * @throws \Illuminate\Auth\Access\AuthorizationException If the user is not authorized to delete the ticket.
     */
    public function destroy(Ticket $ticket): Response
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return response()->noContent();
    }
}
