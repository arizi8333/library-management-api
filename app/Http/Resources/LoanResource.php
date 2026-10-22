<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Loan
 */
class LoanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'book_id' => $this->book_id,
            'book' => new BookResource($this->whenLoaded('book')),
            'borrowed_at' => $this->borrowed_at,
            'returned_at' => $this->returned_at,
            'is_returned' => $this->returned_at !== null,
        ];
    }
}
