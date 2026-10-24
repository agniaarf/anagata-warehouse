<?php

namespace App\Support;

class FieldCodec
{
    /** Textarea (one entry per line) <-> plain JSON string array. */
    public static function listToArray(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => $line !== '')
            ->values()
            ->all();
    }

    public static function arrayToList(array $items): string
    {
        return implode("\n", $items);
    }

    /** Textarea ("Title | Description" per line) <-> array of {title, description}. */
    public static function delimitedToArray(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => $line !== '')
            ->map(function ($line) {
                [$title, $description] = array_pad(explode('|', $line, 2), 2, '');

                return ['title' => trim($title), 'description' => trim($description)];
            })
            ->values()
            ->all();
    }

    public static function arrayToDelimited(array $items): string
    {
        return collect($items)
            ->map(fn ($item) => trim($item['title'] ?? '').' | '.trim($item['description'] ?? ''))
            ->implode("\n");
    }
}
