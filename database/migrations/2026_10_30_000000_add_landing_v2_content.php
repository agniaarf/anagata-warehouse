<?php

use App\Models\Content;
use Database\Seeders\CmsContentSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // V2 uses brand-new section keys, so this cannot overwrite V1 copy.
        (new CmsContentSeeder())->seedLandingV2();

        $v2 = ['intro', 'meaning', 'history', 'team_note', 'collage_finale', 'inbound', 'outbound', 'return', 'location', 'footer'];
        foreach ($v2 as $slug) {
            $meta = config("page_sections.{$slug}");
            Content::putItem('page_layout', $slug, [
                'visible' => true,
                'show_in_nav' => $meta['default_show_in_nav'],
                'nav_label' => $meta['default_nav_label'],
            ], $meta['default_order']);
        }

        $legacy = ['hero', 'marquee', 'services', 'stats', 'about', 'value_prop', 'gallery', 'testimonials'];
        foreach ($legacy as $slug) {
            $row = Content::query()
                ->where('section', 'page_layout')
                ->where('item_key', $slug)
                ->first();

            if (! $row) {
                continue;
            }

            $value = $row->decoded_value;
            $value['visible'] = false;
            $value['show_in_nav'] = false;
            Content::putItem('page_layout', $slug, $value, config("page_sections.{$slug}.default_order", $row->order));
        }
    }

    public function down(): void
    {
        // Deliberately preserve V2 CMS content and editor changes on rollback.
    }
};
