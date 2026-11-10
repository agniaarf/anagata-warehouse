<?php

use App\Models\Content;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Content::where('section', 'fig'.'ma_site')->exists()) return;
        if (! Content::where('section', 'anagata_site')->where('item_key', 'section_6')->exists()) {
            $defaults = json_decode(file_get_contents(database_path('data/anagata-defaults.json')), true, 512, JSON_THROW_ON_ERROR);
            Content::putItem('anagata_site', 'section_6', $defaults['section'][5], 6);
        }
    }

    public function down(): void
    {
        // Preserve content that administrators may have edited.
    }
};
