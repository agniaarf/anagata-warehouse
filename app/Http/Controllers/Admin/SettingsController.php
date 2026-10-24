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

class SettingsController extends Controller
{
    use HandlesCmsFields;

    /** Each tab shows a short group of fields from the shared settings records. */
    private const TABS = [
        'branding' => [
            'label' => 'Identitas situs',
            'section' => 'site_settings',
            'fields' => ['logo_light', 'logo_dark', 'favicon', 'brand_color_primary', 'brand_color_glow', 'brand_color_deep'],
        ],
        'contact' => [
            'label' => 'Kontak',
            'section' => 'site_settings',
            'fields' => ['whatsapp_number', 'email', 'instagram_handle'],
        ],
        'seo' => [
            'label' => 'Pencarian Google',
            'section' => 'seo',
            'fields' => ['meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description', 'image_alt_suffix'],
        ],
    ];

    public function show(string $tab, ContentRepository $cms): View
    {
        $tabDef = $this->tabFor($tab);
        $fullSchema = $this->schemaFor($tabDef['section']);
        $schema = ['fields' => array_intersect_key($fullSchema['fields'], array_flip($tabDef['fields']))];
        $rows = $cms->section($tabDef['section']);

        return view('admin.settings.show', [
            'tab' => $tab,
            'tabs' => self::TABS,
            'section' => $tabDef['section'],
            'schema' => $schema,
            'rows' => $rows,
        ]);
    }

    public function update(Request $request, string $tab): RedirectResponse
    {
        $tabDef = $this->tabFor($tab);
        $fullSchema = $this->schemaFor($tabDef['section']);
        $fields = array_intersect_key($fullSchema['fields'], array_flip($tabDef['fields']));
        $this->validateCmsFields($request, $fields);

        foreach ($tabDef['fields'] as $key) {
            $def = $fullSchema['fields'][$key];
            Content::putField($tabDef['section'], $key, $this->encodeFieldValue($def['type'], $request, $key));
        }

        return back()->with('status', 'Perubahan tersimpan.');
    }

    private function tabFor(string $tab): array
    {
        if (! isset(self::TABS[$tab])) {
            throw new NotFoundHttpException("Unknown settings tab: $tab");
        }

        return self::TABS[$tab];
    }
}
