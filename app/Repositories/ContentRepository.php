<?php

namespace App\Repositories;

use App\Models\Content;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ContentRepository
{
    /** All rows for a section, decoded, ordered. Cached until the ContentObserver busts it. */
    public function section(string $section): Collection
    {
        return Cache::rememberForever("cms.section.$section", fn () =>
            Content::forSection($section)->get()->map(fn (Content $c) => [
                'key' => $c->item_key,
                'type' => $c->type,
                'value' => $c->decoded_value,
                'order' => $c->order,
            ])
        );
    }

    public function items(string $section): Collection
    {
        return $this->section($section)->where('type', Content::TYPE_ITEM)->values();
    }
}
