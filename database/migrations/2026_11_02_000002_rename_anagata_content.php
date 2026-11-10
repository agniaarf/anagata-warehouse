<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Old installations may already have editor changes. Never replace a
        // conflicting destination record silently.
        $old = 'fig'.'ma_site';
        $oldImages = '/fig'.'ma-images/';
        DB::transaction(function () use ($old, $oldImages) {
            $rows = DB::table('cms_contents')->where('section', $old)->get();
            foreach ($rows as $row) {
                if (DB::table('cms_contents')->where('section', 'anagata_site')->where('item_key', $row->item_key)->exists()) {
                    throw new RuntimeException('CMS content conflict for '.$row->item_key.'; resolve before migrating.');
                }
            }
            foreach ($rows as $row) {
                DB::table('cms_contents')->where('id', $row->id)->update(['section' => 'anagata_site']);
            }
            // Replace only the old built-in asset URL, including JSON-escaped
            // slashes. Leave all other editor content untouched.
            foreach (DB::table('cms_contents')->where('value', 'like', '%'.'fig'.'ma-images%')->get() as $row) {
                $value = str_replace([$oldImages, str_replace('/', '\\/', $oldImages)], ['/anagata-images/', '\\/anagata-images\\/'], $row->value);
                if ($value !== $row->value) DB::table('cms_contents')->where('id', $row->id)->update(['value' => $value]);
            }
        });
        Cache::forget('cms.section.'.$old);
        Cache::forget('cms.section.anagata_site');
    }

    public function down(): void
    {
        // Editor data is preserved on rollback.
    }
};
