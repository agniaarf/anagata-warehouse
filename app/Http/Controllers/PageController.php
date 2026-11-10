<?php

namespace App\Http\Controllers;

use App\Repositories\ContentRepository;
use Illuminate\View\View;

class PageController extends Controller
{
    private const SECTIONS = [
        'page_layout', 'anagata_site', 'intro', 'meaning', 'history', 'team_note', 'collage_finale',
        'inbound', 'outbound', 'return', 'location', 'footer', 'seo', 'site_settings',
        'hero', 'about', 'services', 'stats', 'value_prop', 'marquee', 'gallery', 'testimonials',
    ];

    public function home(ContentRepository $cms): View
    {
        return $this->renderLanding($cms, (bool) config('landing.anagata_enabled'));
    }

    public function preview(ContentRepository $cms): View
    {
        return $this->renderLanding($cms, true)->with('isPreview', true);
    }

    private function renderLanding(ContentRepository $cms, bool $anagata): View
    {
        $sections = collect(self::SECTIONS)->mapWithKeys(fn ($section) => [
            $section => $cms->section($section),
        ]);

        return view($anagata ? 'anagata-landing' : 'landing', ['cms' => $sections]);
    }
}
