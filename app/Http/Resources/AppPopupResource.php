<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppPopupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imagePath = $this->banner_image ?? $this->image;
        $imageUrl = null;
        if ($imagePath) {
            $imageUrl = str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')
                ? $imagePath
                : asset('uploads/' . ltrim($imagePath, '/'));
        }

        return [
            'id' => $this->id,
            'title' => null,
            'description' => null,
            'banner_image' => $imageUrl,
            'image' => $imageUrl,
            'status' => (bool) $this->status,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
