<?php

use App\Models\Content;
use Illuminate\Support\Collection;

if (! function_exists('cms_field')) {
    /** Read one scalar field's value out of a partial's $cms[section] collection. */
    function cms_field(Collection $section, string $key, string $default = ''): string
    {
        $row = $section->firstWhere('key', $key);

        return $row && $row['type'] === Content::TYPE_FIELD ? $row['value'] : $default;
    }
}

if (! function_exists('cms_items')) {
    /** Read all repeater items (already ordered) out of a partial's $cms[section] collection. */
    function cms_items(Collection $section): Collection
    {
        return $section->where('type', Content::TYPE_ITEM)->values();
    }
}

if (! function_exists('content_collections')) {
    /**
     * Repeating content groups available from the content page.
     */
    function content_collections(): array
    {
        $collections = [];

        $pageSections = config('page_sections');
        $pageOrder = array_map(fn ($meta) => $meta['schema_section'], $pageSections);
        $schemaOrder = array_merge($pageOrder, array_diff(array_keys(config('cms_schema')), $pageOrder));

        foreach ($schemaOrder as $sectionSlug) {
            $schema = config("cms_schema.$sectionSlug", []);
            $pageSection = collect($pageSections)->first(fn ($meta) => $meta['schema_section'] === $sectionSlug);

            foreach ($schema['repeaters'] ?? [] as $group => $groupDef) {
                $collections[] = [
                    'section' => $sectionSlug,
                    'group' => $group,
                    'label' => $groupDef['label'],
                    'parent_label' => $pageSection['label'] ?? $schema['label'] ?? ucfirst($sectionSlug),
                ];
            }
        }

        return $collections;
    }
}

if (! function_exists('cms_display_value')) {
    /**
     * Format a stored field value back into what its admin form control should show.
     * $raw is a JSON string for top-level 'field' rows (list/delimited_list types),
     * or a native array for repeater item fields.
     */
    function cms_display_value(string $type, $raw): string
    {
        $decoded = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);

        return match ($type) {
            'list' => \App\Support\FieldCodec::arrayToList($decoded),
            'delimited_list' => \App\Support\FieldCodec::arrayToDelimited($decoded),
            'checkbox' => $raw ? '1' : '',
            default => (string) $raw,
        };
    }
}

if (! function_exists('cms_rich_text')) {
    /**
     * Keep the tiny formatting subset used by seeded CMS copy while escaping
     * every other tag, attribute, and potentially executable markup.
     */
    function cms_rich_text(string $value): string
    {
        $escaped = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);

        return preg_replace_callback('/&lt;(\/)?(br|strong|em)\s*\/?&gt;/i', function (array $match): string {
            $tag = strtolower($match[2]);

            return $tag === 'br' ? '<br>' : '<'.$match[1].$tag.'>';
        }, $escaped) ?? $escaped;
    }
}
