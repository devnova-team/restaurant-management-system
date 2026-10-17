<?php

namespace App\Http\Resources;

use App\Enums\DeliveryStep;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicTrackingResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        $step = DeliveryStep::from($this->status);

        return [
            'status' => $step->value,

            'step' => $step->stepNumber(),

        ];
    }
}
