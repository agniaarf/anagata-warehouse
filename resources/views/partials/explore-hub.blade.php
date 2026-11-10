@php
  $photos = cms_items($cms['collage_finale'])->filter(fn ($item) => str_starts_with($item['key'], 'photo_'));
@endphp
<section id="working-activity" class="explore-hub" data-main-entry aria-label="Explore Anagata">
  <div class="people-ribbon" aria-hidden="true"><span>PEOPLE · PROCESS · PURPOSE · PEOPLE · PROCESS · PURPOSE ·</span></div>
  <div class="wrap explore-layout">
    <div class="explore-copy">
      <span class="story-kicker">EXPLORE ANAGATA</span>
      <h2 data-explore-title>WORKING<br><em>ACTIVITY.</em></h2>
      <p data-explore-description>Kenali manusia, proses, dan tujuan di balik setiap perjalanan bersama Anagata.</p>
    </div>
    <div class="explore-cards" data-explore-main>
      @php $hubCards = [['Working Activity', '#working-activity'], ['Our Services', '#services'], ['Our Locations', '#location']]; @endphp
      @foreach($hubCards as $card)
        @php $photo = $photos->values()->get($loop->index); @endphp
        <a class="explore-card" href="{{ $card[1] }}" @if($loop->first) data-open-activity @endif data-title="{{ $card[0] }}" data-description="{{ ['Kenali manusia, proses, dan tujuan di balik setiap perjalanan bersama Anagata.', 'Dari barang tiba hingga siap dikirim, setiap langkah ditangani dengan perhatian.', 'Temukan ruang dan tim yang siap mendukung perjalanan bisnismu.'][$loop->index] }}">
          @if($photo)<img src="{{ \App\Support\Cms::largeUrl($photo['value']['image'] ?? '') }}" alt="" loading="lazy">@endif
          <span>{{ $card[0] }} <b aria-hidden="true">↗</b></span>
        </a>
      @endforeach
    </div>
    <div class="explore-cards" data-explore-activity hidden>
      @foreach([['People', 'Manusia di balik setiap paket.', 'Orang-orang yang saling membantu dan menjaga kepercayaan dalam setiap pekerjaan.'], ['Process', 'Perhatian pada setiap detail.', 'Proses yang terarah membantu setiap pesanan bergerak dengan rapi dan konsisten.'], ['Purpose', 'Ruang untuk bertumbuh.', 'Kami hadir agar pemilik bisnis punya lebih banyak ruang untuk melangkah lebih jauh.']] as $item)
        @php $photo = $photos->values()->get($loop->index + 3) ?? $photos->values()->get($loop->index); @endphp
        <button type="button" class="explore-card" data-title="{{ $item[0] }}" data-description="{{ $item[1] }} {{ $item[2] }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
          @if($photo)<img src="{{ \App\Support\Cms::largeUrl($photo['value']['image'] ?? '') }}" alt="" loading="lazy">@endif
          <span>{{ $item[0] }}</span>
        </button>
      @endforeach
    </div>
  </div>
</section>
