<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesCmsFields;
use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Repositories\ContentRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WebsiteController extends Controller
{
    use HandlesCmsFields;

    public function homepage(ContentRepository $cms): View
    {
        $layout = $cms->items('page_layout')->sortBy('order')->values();
        $pageSections = config('page_sections');

        return view('admin.website.homepage', [
            'layout' => $layout,
            'pageSections' => $pageSections,
        ]);
    }

    public function toggleVisible(string $slug): RedirectResponse
    {
        $meta = $this->pageSectionFor($slug);
        $row = Content::where('section', 'page_layout')->where('item_key', $slug)->firstOrFail();

        abort_if($meta['locked'], 403, 'Bagian ini selalu ditampilkan.');

        $value = $row->decoded_value;
        $value['visible'] = ! $value['visible'];
        Content::putItem('page_layout', $slug, $value, $row->order);

        return back()->with('status', 'Perubahan tersimpan.');
    }

    public function updateNav(Request $request, string $slug): RedirectResponse
    {
        $request->validate([
            'show_in_nav' => ['nullable', 'boolean'],
            'nav_label' => ['nullable', 'string', 'max:60'],
        ]);

        $this->pageSectionFor($slug);
        $row = Content::where('section', 'page_layout')->where('item_key', $slug)->firstOrFail();

        $value = $row->decoded_value;
        $value['show_in_nav'] = $request->boolean('show_in_nav');
        $value['nav_label'] = (string) $request->input('nav_label', $value['nav_label']);
        Content::putItem('page_layout', $slug, $value, $row->order);

        return back()->with('status', 'Perubahan tersimpan.');
    }

    public function move(string $slug, string $direction): RedirectResponse
    {
        $meta = $this->pageSectionFor($slug);
        abort_if($meta['locked'], 403, 'Urutan bagian ini tidak dapat diubah.');

        $items = Content::where('section', 'page_layout')->orderBy('order')->get();
        $index = $items->search(fn ($row) => $row->item_key === $slug);
        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || ! $items->has($swapWith)) {
            return back();
        }

        $target = $items[$swapWith];
        // Locked sections (hero=first, footer=last) are never swap targets either,
        // so the list can't be reordered past them.
        if (config("page_sections.{$target->item_key}.locked")) {
            return back();
        }

        [$items[$index]->order, $target->order] = [$target->order, $items[$index]->order];
        $items[$index]->save();
        $target->save();

        return back();
    }

    public function editSection(string $slug, ContentRepository $cms): View
    {
        $meta = $this->pageSectionFor($slug);
        $schema = $this->schemaFor($meta['schema_section']);
        $rows = $cms->section($meta['schema_section']);

        return view('admin.website.section-edit', [
            'slug' => $slug,
            'meta' => $meta,
            'schema' => $schema,
            'rows' => $rows,
        ]);
    }

    public function updateSection(Request $request, string $slug): RedirectResponse
    {
        $meta = $this->pageSectionFor($slug);
        $schema = $this->schemaFor($meta['schema_section']);
        $this->validateCmsFields($request, $schema['fields']);

        foreach ($schema['fields'] as $key => $def) {
            Content::putField($meta['schema_section'], $key, $this->encodeFieldValue($def['type'], $request, $key));
        }

        return back()->with('status', 'Perubahan tersimpan.');
    }

    private function pageSectionFor(string $slug): array
    {
        $meta = config("page_sections.$slug");

        if (! $meta) {
            throw new NotFoundHttpException("Unknown page section: $slug");
        }

        return $meta;
    }
}
