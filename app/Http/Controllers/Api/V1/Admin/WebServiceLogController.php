<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Ticket\Repositories\WebServiceLogRepository;
use App\Domains\Ticket\Resources\V1\Admin\WebServiceLogResource;
use App\Domains\User\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Class WebServiceLogController
 *
 * Admin controller for managing and viewing web service logs.
 * Provides functionality to list all web service logs in a paginated format.
 *
 * @package App\Http\Controllers\Api\V1\Admin
 */
class WebServiceLogController extends Controller
{
    /**
     * WebServiceLogController constructor.
     *
     * No dependencies are injected directly; the repository is method-injected.
     */
    public function __construct()
    {
    }

    /**
     * Get a paginated list of all web service logs.
     *
     * Retrieves logs from the repository (typically for admin overview)
     * and returns them as a collection of WebServiceLogResource.
     *
     * @param WebServiceLogRepository $repository The repository for web service log data operations.
     * @return AnonymousResourceCollection A collection of web service log resources.
     */
    public function index(
        WebServiceLogRepository $repository
    ): AnonymousResourceCollection
    {
        $webServiceLogs = $repository->paginateForAdmin();

        return WebServiceLogResource::collection($webServiceLogs);
    }
}
