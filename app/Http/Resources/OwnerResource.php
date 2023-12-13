<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OwnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
            'ownership_stake' => $this->ownership_stake,
            'email' => $this->email,
            'phone' => $this->phone,
            'document_type' => $this->document_type,
            'document_expiration' => $this->document_expiration,
            'kyc_document' => $this->document_expiration,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
