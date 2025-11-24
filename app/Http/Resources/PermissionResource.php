<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Si es una colección paginada, devolver formato de colección
        if ($this->resource instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            return $this->formatPaginatedCollection($request);
        }

        // Si no, devolver el recurso individual
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Format paginated collection response
     */
    private function formatPaginatedCollection(Request $request): array
    {
        return [
            'current_page' => $this->resource->currentPage(),
            'data' => $this->resource->getCollection()->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'created_at' => $permission->created_at?->toISOString(),
                    'updated_at' => $permission->updated_at?->toISOString(),
                ];
            }),
            'first_page_url' => $this->resource->url(1),
            'from' => $this->resource->firstItem(),
            'last_page' => $this->resource->lastPage(),
            'last_page_url' => $this->resource->url($this->resource->lastPage()),
            'links' => $this->getPaginationLinks(),
            'next_page_url' => $this->resource->nextPageUrl(),
            'path' => $this->resource->path(),
            'per_page' => $this->resource->perPage(),
            'prev_page_url' => $this->resource->previousPageUrl(),
            'to' => $this->resource->lastItem(),
            'total' => $this->resource->total(),
        ];
    }

    /**
     * Generate pagination links array
     */
    private function getPaginationLinks()
    {
        $links = [];

        // Previous link
        $links[] = [
            'url' => $this->resource->previousPageUrl(),
            'label' => 'pagination.previous',
            'page' => $this->resource->currentPage() > 1 ? $this->resource->currentPage() - 1 : null,
            'active' => false
        ];

        // Page links
        for ($page = 1; $page <= $this->resource->lastPage(); $page++) {
            $links[] = [
                'url' => $this->resource->url($page),
                'label' => (string) $page,
                'page' => $page,
                'active' => $page === $this->resource->currentPage()
            ];
        }

        // Next link
        $links[] = [
            'url' => $this->resource->nextPageUrl(),
            'label' => 'pagination.next',
            'page' => $this->resource->currentPage() < $this->resource->lastPage() ? $this->resource->currentPage() + 1 : null,
            'active' => false
        ];

        return $links;
    }
}
