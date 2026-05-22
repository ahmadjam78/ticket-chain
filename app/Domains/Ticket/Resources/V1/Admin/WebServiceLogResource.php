<?php

namespace App\Domains\Ticket\Resources\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Domains\Ticket\Resources\V1\Admin\TicketResource;

class WebServiceLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'attempt_number' => $this->attempt_number,
            'response' => json_decode($this->response),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'ticket' => $this->whenLoaded('ticket', function () {
                return TicketResource::make($this->ticket);
            }),

        ];
    }
}
