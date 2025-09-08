<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;

trait PaginationTrait
{

    public array $data = [];

    public function makePaginationResponse(LengthAwarePaginator $pagination, callable $call, array $append = []): self
    {
        $items = $pagination->items();

        $items = $call($items);

        $this->data['items'] = $items;
        $this->data['page'] = $pagination->currentPage();
        $this->data['total_page']    = $pagination->lastPage();
        $this->data['total_items']        = $pagination->total();

        foreach ($append as $key => $value) {
            $this->data[$key] = $value;
        }

        return $this;
    }
}
