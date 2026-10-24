<?php

namespace App\Observers;

use App\Models\Content;
use Illuminate\Support\Facades\Cache;

class ContentObserver
{
    public function saved(Content $content): void
    {
        Cache::forget("cms.section.{$content->section}");
    }

    public function deleted(Content $content): void
    {
        Cache::forget("cms.section.{$content->section}");
    }
}
