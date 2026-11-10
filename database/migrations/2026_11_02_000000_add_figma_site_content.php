<?php

use App\Models\Content;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // An existing installation is migrated by the subsequent rename; do
        // not create defaults that could collide with its edited records.
        if (Content::where('section', 'fig'.'ma_site')->exists()) return;
        $groups = json_decode(file_get_contents(database_path('data/anagata-defaults.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($groups as $group => $items) {
            foreach ($items as $index => $item) {
                $key = $group.'_'.($index + 1);
                if (! Content::where('section', 'anagata_site')->where('item_key', $key)->exists()) {
                    Content::putItem('anagata_site', $key, $item, $index + 1);
                }
            }
        }
    }

    public function down(): void
    {
        // Preserve content that administrators may have edited.
    }
};
