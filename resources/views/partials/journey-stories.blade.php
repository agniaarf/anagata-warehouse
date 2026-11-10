<section id="journey-stories" class="journey-stories" aria-label="Perjalanan Anagata">
  @foreach([['A BEGINNING', 'Setiap bisnis dimulai dari sebuah mimpi. Anagata hadir untuk menjaga mimpi itu tetap bergerak.'], ['ROOM TO GROW', 'Bertumbuh bukan hanya tentang menjual lebih banyak, tetapi tentang membangun proses yang mampu mengikuti setiap langkahmu.'], ['MADE BY PEOPLE', 'Terinspirasi oleh manusia dan kerja keras, kami menyatukan ruang, sistem, dan perhatian dalam satu perjalanan.'], ['FUTURE IN MOTION', 'Bayangkan operasional yang lebih ringan. Pelanggan yang lebih bahagia. Dan bisnismu melangkah lebih jauh.'], ['THE NEXT CHAPTER', 'Apakah kamu siap tumbuh bersama kami?']] as $story)
    <article class="journey-story"><span>{{ $story[0] }}</span><p>{{ $story[1] }}</p></article>
  @endforeach
</section>
