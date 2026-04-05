<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'specialty' => $this->specialty,
            'specialty_ar' => $this->specialty_ar,
            'email' => $this->email,
            'phone' => $this->phone,
            'photo_url' => $this->photo_url,
            'experience_years' => $this->experience_years,
            'about' => $this->about,
            'about_ar' => $this->about_ar,
            'consultation_fee' => (float) $this->consultation_fee,
            'available_hours_start' => $this->available_hours_start,
            'available_hours_end' => $this->available_hours_end,
            'status' => $this->status,
            'rating' => (float) $this->rating ?? 0,
            'reviews_count' => $this->reviews_count ?? 0,
            'verified' => $this->verified ?? false,
            'user' => new UserResource($this->whenLoaded('user')),
            'medical_centers' => MedicalCenterResource::collection($this->whenLoaded('medicalCenters')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
