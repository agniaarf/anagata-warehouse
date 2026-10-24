<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsCollector;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class AnalyticsCollectionController extends Controller
{
    public function __invoke(Request $request, AnalyticsCollector $collector): Response
    {
        if (auth()->check() || $collector->isBot($request->userAgent())) {
            return response()->noContent();
        }

        abort_unless($request->isJson(), 415, 'Analytics menerima JSON saja.');

        $validated = $request->validate([
            'event_id' => ['required', 'bail', 'uuid'],
            'session_id' => ['required', 'bail', 'uuid'],
            'visitor_id' => ['required', 'bail', 'uuid'],
            'type' => ['required', 'bail', 'string', Rule::in(['session_start', 'section_view', 'contact_click'])],
            'target' => ['nullable', 'bail', 'string', 'max:60', Rule::requiredIf(fn () => in_array($request->input('type'), ['section_view', 'contact_click'], true))],
            'path' => ['nullable', 'bail', 'string', 'max:255', 'starts_with:/'],
            'referrer_host' => ['nullable', 'bail', 'string', 'max:253', 'regex:/\A[a-z0-9.-]+\z/i'],
            'utm_source' => ['nullable', 'bail', 'string', 'max:100'],
            'utm_medium' => ['nullable', 'bail', 'string', 'max:100'],
            'utm_campaign' => ['nullable', 'bail', 'string', 'max:100'],
        ]);

        $collector->collect($validated, $request->userAgent());

        return response()->noContent();
    }
}
