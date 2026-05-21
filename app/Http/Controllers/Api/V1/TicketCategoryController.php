<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Ticket\Repositories\TicketCategoryRepository;
use App\Http\Controllers\Controller;
use App\Domains\Ticket\Resources\V1\TicketCategoryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Class TicketCategoryController
 *
 * Handles API endpoints for ticket category management.
 * Provides methods to retrieve hierarchical category structures.
 *
 * @package App\Http\Controllers\Api\V1
 */
class TicketCategoryController extends Controller
{
    /**
     * The ticket category repository instance.
     *
     * @var TicketCategoryRepository
     */
    protected TicketCategoryRepository $repository;

    /**
     * TicketCategoryController constructor.
     *
     * @param TicketCategoryRepository $repository The repository for ticket category data operations.
     */
    public function __construct(TicketCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get a list of all ticket categories with their nested children.
     *
     * Retrieves all categories from the repository, including their
     * hierarchical child relationships, and returns them as a collection
     * of TicketCategoryResource.
     *
     * @return AnonymousResourceCollection A collection of ticket category resources.
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = $this->repository->allWithChildren();

        return TicketCategoryResource::collection($categories);
    }
}
