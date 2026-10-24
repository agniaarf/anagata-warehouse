{{-- Decorative "silk" light ribbon, lifted from the Business Profile deck's
     backgrounds (grainy fabric-like sheen, violet bleed, mint→lavender edge).
     Used in the Hero only, without the edge line at the client's request (the
     page background is the same silk as public/images/silk-tile.svg).
     Not CMS content.
     Usage: @include('partials._silk', ['variant' => 'hero'|'value'|'contact', 'uid' => 'a', 'class' => '...']) —
     'uid' keeps SVG ids unique when a variant is used more than once;
     'line' => false drops the thin mint→lavender edge line (sheen only). --}}
@php $id = 'silk-'.$variant.($uid ?? ''); $line = $line ?? true; @endphp
<div class="silk silk--{{ $variant }} {{ $class ?? '' }}" aria-hidden="true">
  <svg viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <filter id="{{ $id }}-soft" x="-20%" y="-20%" width="140%" height="140%"><feGaussianBlur stdDeviation="38"/></filter>
      <filter id="{{ $id }}-edge" x="-5%" y="-20%" width="110%" height="140%"><feGaussianBlur stdDeviation="1.4"/></filter>
      <linearGradient id="{{ $id }}-sheen" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0" stop-color="#ffffff" stop-opacity="0"/>
        <stop offset=".35" stop-color="#ffffff" stop-opacity=".11"/>
        <stop offset=".7" stop-color="#c7b8e0" stop-opacity=".05"/>
        <stop offset="1" stop-color="#a855f7" stop-opacity=".30"/>
      </linearGradient>
      <linearGradient id="{{ $id }}-line" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0" stop-color="#9ff0dc" stop-opacity="0"/>
        <stop offset=".3" stop-color="#9ff0dc" stop-opacity=".45"/>
        <stop offset=".75" stop-color="#c4a5f5" stop-opacity=".35"/>
        <stop offset="1" stop-color="#c4a5f5" stop-opacity="0"/>
      </linearGradient>
    </defs>

    @if($variant === 'hero')
      {{-- cover-slide sweep: rises from bottom-left, exits top-right into violet --}}
      <path filter="url(#{{ $id }}-soft)" fill="url(#{{ $id }}-sheen)"
            d="M-120 720 C200 540 430 400 720 480 S1160 400 1560 110 L1560 360 C1220 580 960 660 720 650 S200 780 -120 920 Z"/>
      @if($line)
      <path filter="url(#{{ $id }}-edge)" fill="none" stroke="url(#{{ $id }}-line)" stroke-width="1.2"
            d="M-120 720 C200 540 430 400 720 480 S1160 400 1560 110"/>
      @endif
    @elseif($variant === 'value')
      {{-- "Kenapa Anagata?" slide: sheen falls from top-left toward bottom-right --}}
      <path filter="url(#{{ $id }}-soft)" fill="url(#{{ $id }}-sheen)" transform="translate(1440 0) scale(-1 1)"
            d="M-120 60 C260 40 560 250 880 300 S1300 520 1560 720 L1560 920 C1200 720 820 580 520 540 S120 380 -120 420 Z"/>
    @else
      {{-- "Thank You" slide: one long fold edge, violet bleeding in from the right --}}
      <path filter="url(#{{ $id }}-soft)" fill="url(#{{ $id }}-sheen)"
            d="M-120 -40 L620 -40 C820 180 1060 460 1560 560 L1560 900 C1160 820 820 560 560 300 S80 120 -120 160 Z"/>
      @if($line)
      <path filter="url(#{{ $id }}-edge)" fill="none" stroke="url(#{{ $id }}-line)" stroke-width="1"
            d="M620 -40 C820 180 1060 460 1560 560"/>
      @endif
    @endif
  </svg>
</div>
