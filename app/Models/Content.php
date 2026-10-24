<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $table = 'cms_contents';

    const TYPE_FIELD = 'field';
    const TYPE_ITEM = 'item';

    protected $fillable = ['section', 'item_key', 'type', 'value', 'order'];

    protected $casts = [
        'order' => 'integer',
    ];

    protected function decodedValue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type === self::TYPE_ITEM
                ? (json_decode($this->value, true) ?? [])
                : $this->value,
        );
    }

    public function scopeForSection(Builder $query, string $section): Builder
    {
        return $query->where('section', $section)->orderBy('order')->orderBy('item_key');
    }

    public static function putField(string $section, string $key, string $value, int $order = 0): self
    {
        return self::updateOrCreate(
            ['section' => $section, 'item_key' => $key],
            ['type' => self::TYPE_FIELD, 'value' => $value, 'order' => $order]
        );
    }

    public static function putItem(string $section, string $key, array $fields, int $order = 0): self
    {
        return self::updateOrCreate(
            ['section' => $section, 'item_key' => $key],
            ['type' => self::TYPE_ITEM, 'value' => json_encode($fields, JSON_UNESCAPED_UNICODE), 'order' => $order]
        );
    }
}
