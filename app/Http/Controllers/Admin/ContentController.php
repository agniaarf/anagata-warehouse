<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesCmsFields;
use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Repositories\ContentRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentController extends Controller
{
    use HandlesCmsFields;

    public function index(): View
    {
        return view('admin.content.index', ['collections' => content_collections()]);
    }

    public function show(string $section, string $group, ContentRepository $cms): View
    {
        $schema = $this->schemaFor($section);
        $repeaterDef = $this->repeaterFor($schema, $group);

        $items = $cms->items($section)
            ->filter(fn ($row) => str_starts_with($row['key'], $group.'_'))
            ->sortBy('order')
            ->values();

        return view('admin.content.show', [
            'section' => $section,
            'group' => $group,
            'schema' => $schema,
            'repeaterDef' => $repeaterDef,
            'items' => $items,
        ]);
    }

    public function storeItem(Request $request, string $section, string $group): RedirectResponse
    {
        $schema = $this->schemaFor($section);
        $repeaterDef = $this->repeaterFor($schema, $group);
        abort_if($repeaterDef['locked'] ?? false, 403, 'Item baru tidak dapat ditambahkan ke daftar ini.');
        $this->validateCmsFields($request, $repeaterDef['fields']);

        $fields = $this->collectItemFields($repeaterDef, $request);
        $key = $this->nextItemKey($section, $group);
        $order = $this->nextOrder($section, $group);

        Content::putItem($section, $key, $fields, $order);

        return back()->with('status', 'Item baru ditambahkan.');
    }

    public function updateItem(Request $request, string $section, string $group, string $item): RedirectResponse
    {
        $schema = $this->schemaFor($section);
        $repeaterDef = $this->repeaterFor($schema, $group);
        abort_unless(str_starts_with($item, $group.'_'), 404);

        $row = Content::where('section', $section)->where('item_key', $item)->firstOrFail();
        $this->validateCmsFields($request, $repeaterDef['fields']);
        $fields = $this->collectItemFields($repeaterDef, $request);

        Content::putItem($section, $item, $fields, $row->order);

        return back()->with('status', 'Item tersimpan.');
    }

    public function destroyItem(string $section, string $group, string $item): RedirectResponse
    {
        $repeaterDef = $this->repeaterFor($this->schemaFor($section), $group);
        abort_if($repeaterDef['locked'] ?? false, 403, 'Item dari daftar ini tidak dapat dihapus.');
        abort_unless(str_starts_with($item, $group.'_'), 404);

        Content::where('section', $section)->where('item_key', $item)->firstOrFail()->delete();

        return back()->with('status', 'Item dihapus.');
    }

    public function moveItem(string $section, string $group, string $item, string $direction): RedirectResponse
    {
        $repeaterDef = $this->repeaterFor($this->schemaFor($section), $group);
        abort_if($repeaterDef['locked'] ?? false, 403, 'Urutan item ini tidak dapat diubah.');
        abort_unless(str_starts_with($item, $group.'_'), 404);

        $items = $this->itemsForGroup($section, $group);

        $index = $items->search(fn ($row) => $row->item_key === $item);
        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index !== false && $items->has($swapWith)) {
            $a = $items[$index];
            $b = $items[$swapWith];
            [$a->order, $b->order] = [$b->order, $a->order];
            $a->save();
            $b->save();
        }

        return back();
    }

    private function nextItemKey(string $section, string $group): string
    {
        return $group.'_'.($this->maxSuffix($section, $group) + 1);
    }

    private function nextOrder(string $section, string $group): int
    {
        return (int) ($this->itemsForGroup($section, $group)->max('order') ?? 0) + 1;
    }

    private function maxSuffix(string $section, string $group): int
    {
        return $this->itemsForGroup($section, $group)
            ->map(function ($row) use ($group): int {
                preg_match('/\A'.preg_quote($group, '/').'_(\d+)\z/', $row->item_key, $matches);

                return (int) ($matches[1] ?? 0);
            })
            ->max() ?? 0;
    }

    private function itemsForGroup(string $section, string $group): \Illuminate\Support\Collection
    {
        return Content::where('section', $section)
            ->get()
            ->filter(fn ($row) => str_starts_with($row->item_key, $group.'_'))
            ->sortBy('order')
            ->values();
    }
}
