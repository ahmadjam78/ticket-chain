<?php

namespace App\Domains\Ticket\Repositories;

use App\Domains\Ticket\Models\TicketCategory;
use App\Domains\Ticket\Repositories\TicketCategoryRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class EloquentTicketCategoryRepository
 *
 * Eloquent implementation of the TicketCategoryRepository interface.
 * Handles retrieval of ticket categories with hierarchical relationships.
 *
 * @package App\Domains\Ticket\Repositories
 */
class EloquentTicketCategoryRepository implements TicketCategoryRepository
{
    /**
     * Retrieve all top-level categories with their immediate children.
     *
     * @return Collection Collection of TicketCategory models with 'children' relationship loaded.
     */
    public function allWithChildren(): Collection
    {
        return TicketCategory::with('children')->whereNull('parent_id')->get();
    }

    /**
     * Retrieve all top-level categories with nested children recursively.
     *
     * Uses the 'childrenRecursive' relationship to load the entire category tree.
     *
     * @return Collection Collection of TicketCategory models with recursive children.
     */
    public function allNested(): Collection
    {
        return TicketCategory::with('childrenRecursive')->whereNull('parent_id')->get();
    }
}
