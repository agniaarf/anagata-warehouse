<?php

namespace App\Http\Controllers;

use App\Repositories\ContentRepository;
use Illuminate\View\View;

class PageController extends Controller
{
    private const SECTIONS = [
        'page_layout', 'hero', 'about', 'services', 'stats', 'value_prop',
        'location', 'marquee', 'gallery', 'testimonials', 'footer', 'seo', 'site_settings',
    ];

    public function home(ContentRepository $cms): View
    {
        $sections = collect(self::SECTIONS)->mapWithKeys(fn ($section) => [
            $section => $cms->section($section),
        ]);

        return view('landing', ['cms' => $sections]);
    }
}
