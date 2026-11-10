@php
  $s = $cms['team_note'];
  $image = \App\Support\Cms::largeUrl(cms_field($s, 'image'));
@endphp
<section id="team-note" class="anagata-note" data-analytics-section="team_note" data-funnel-event="story_team_note_view">
  <div class="wrap">
    <span class="anagata-eyebrow">SEKAPUR SIRIH</span>
    <h2>UNTUK PARA PEMILIK MIMPI <em>DI INDONESIA.</em></h2>
    <div class="anagata-note-grid">
      <figure><img src="{{ $image }}" alt="{{ cms_field($s, 'image_alt') }}" loading="lazy"><figcaption>A NOTE FROM US <span>#Let'sGrowTogether</span></figcaption></figure>
      <article><p>Ada keberanian besar di balik setiap bisnis yang dimulai. Keberanian untuk mencoba, melayani, dan terus berjalan ketika pesanan datang lebih cepat dari perkiraan.</p><p>Kami percaya sebuah warehouse bukan sekadar tempat menaruh barang. Ia adalah ruang tempat rencana dijaga, kepercayaan pelanggan dirawat, dan masa depan sebuah brand dipersiapkan.</p><p>Karena ketika operasional berjalan lebih ringan, pemilik bisnis punya lebih banyak ruang untuk membayangkan langkah berikutnya. Dan bagi kami, pertumbuhan terbaik adalah pertumbuhan yang dijalani bersama.</p><strong>ANAGATA WAREHOUSE</strong></article>
    </div>
  </div>
</section>
