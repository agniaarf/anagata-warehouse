<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\FieldCodec;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Shared field encoding for homepage copy, repeating items, and settings.
 * Field types use the same rules in every part of the editor.
 */
trait HandlesCmsFields
{
    protected function schemaFor(string $section): array
    {
        $schema = config("cms_schema.$section");

        if (! $schema) {
            throw new NotFoundHttpException("Unknown CMS section: $section");
        }

        return $schema;
    }

    protected function repeaterFor(array $schema, string $group): array
    {
        if (! isset($schema['repeaters'][$group])) {
            throw new NotFoundHttpException("Unknown repeater group: $group");
        }

        return $schema['repeaters'][$group];
    }

    /** Validate all editor input before writing any part of a CMS update. */
    protected function validateCmsFields(Request $request, array $fields): void
    {
        $rules = [];

        foreach ($fields as $key => $definition) {
            $type = $definition['type'];
            $fieldRules = [($definition['required'] ?? false) ? 'required' : 'nullable', 'bail'];

            if ($type === 'checkbox') {
                $fieldRules[] = 'boolean';
            } elseif ($type === 'select') {
                $fieldRules[] = 'string';
                $fieldRules[] = Rule::in(array_keys($definition['options'] ?? []));
            } elseif ($type === 'number') {
                $fieldRules[] = 'numeric';
                $fieldRules[] = match ($key) {
                    'lat' => 'between:-90,90',
                    'lng' => 'between:-180,180',
                    default => 'between:0,1000000',
                };
            } elseif ($type === 'color') {
                $fieldRules[] = 'string';
                $fieldRules[] = 'regex:/\A#[0-9a-fA-F]{6}\z/';
            } elseif ($type === 'image') {
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:2048';
                $fieldRules[] = function ($attribute, $value, $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
                    $host = strtolower((string) parse_url($value, PHP_URL_HOST));
                    $appHost = strtolower((string) parse_url(config('app.url'), PHP_URL_HOST));
                    $isValidUrl = filter_var($value, FILTER_VALIDATE_URL) !== false;
                    $remoteImage = ($scheme === 'https' || (app()->environment(['local', 'testing']) && $scheme === 'http' && $host === $appHost))
                        && $host !== '' && $isValidUrl;
                    $storedImage = preg_match('#\A(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_-]+\.(?:jpe?g|png|webp|gif|svg)\z#i', $value) === 1;

                    if (! $remoteImage && ! $storedImage) {
                        $fail('Referensi gambar harus berupa URL HTTPS atau path gambar yang valid.');
                    }
                };
            } elseif ($type === 'list' || $type === 'delimited_list') {
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:20000';
                $fieldRules[] = function ($attribute, $value, $fail) use ($type): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];
                    $maxLineLength = $type === 'list' ? 500 : 1500;

                    if (count($lines) > 100 || collect($lines)->contains(fn ($line) => mb_strlen($line) > $maxLineLength)) {
                        $fail('Daftar dibatasi hingga 100 baris dengan panjang teks yang wajar.');
                    }
                };
            } else {
                $fieldRules[] = 'string';
                $fieldRules[] = $type === 'textarea' ? 'max:10000' : 'max:255';

                if (str_ends_with($key, '_href')) {
                    $fieldRules[] = function ($attribute, $value, $fail): void {
                        if ($value === null || $value === '') {
                            return;
                        }

                        $isAnchor = preg_match('/\A#[A-Za-z][A-Za-z0-9_-]*\z/', $value) === 1;
                        $isRelative = str_starts_with($value, '/')
                            && ! str_starts_with($value, '//')
                            && preg_match('/[\x00-\x20<>"\'\\\\]/', $value) !== 1;
                        $parts = parse_url($value);
                        $isHttps = is_array($parts)
                            && strtolower($parts['scheme'] ?? '') === 'https'
                            && ! empty($parts['host'])
                            && filter_var($value, FILTER_VALIDATE_URL) !== false;

                        if (! $isAnchor && ! $isRelative && ! $isHttps) {
                            $fail('Tautan harus berupa anchor, path lokal, atau URL HTTPS.');
                        }
                    };
                }

                if ($key === 'email') {
                    $fieldRules[] = 'email:rfc';
                } elseif ($key === 'whatsapp_number') {
                    $fieldRules[] = 'regex:/\A[1-9][0-9]{7,15}\z/';
                } elseif ($key === 'instagram_handle') {
                    $fieldRules[] = 'regex:/\A[A-Za-z0-9._]{1,30}\z/';
                }
            }

            $rules[$key] = $fieldRules;
        }

        $request->validate($rules);
    }

    protected function encodeFieldValue(string $type, Request $request, string $key): string
    {
        return match ($type) {
            'checkbox' => $request->boolean($key) ? '1' : '0',
            'list' => json_encode(FieldCodec::listToArray((string) $request->input($key, '')), JSON_UNESCAPED_UNICODE),
            'delimited_list' => json_encode(FieldCodec::delimitedToArray((string) $request->input($key, '')), JSON_UNESCAPED_UNICODE),
            default => (string) $request->input($key, ''),
        };
    }

    protected function collectItemFields(array $repeaterDef, Request $request): array
    {
        $fields = [];
        foreach ($repeaterDef['fields'] as $key => $def) {
            $fields[$key] = match ($def['type']) {
                'checkbox' => $request->boolean($key),
                'list' => FieldCodec::listToArray((string) $request->input($key, '')),
                'delimited_list' => FieldCodec::delimitedToArray((string) $request->input($key, '')),
                default => (string) $request->input($key, ''),
            };
        }

        return $fields;
    }
}
