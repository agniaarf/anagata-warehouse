<x-app-layout>
    @php
        $sectionLabels = [
            'hero' => 'Banner utama',
            'marquee' => 'Mitra ekspedisi',
            'services' => 'Layanan',
            'stats' => 'Angka utama',
            'about' => 'Tentang Anagata',
            'value_prop' => 'Keunggulan',
            'location' => 'Lokasi gudang',
            'gallery' => 'Galeri',
            'testimonials' => 'Testimoni',
            'contact' => 'Kontak',
        ];
        $channelLabels = ['whatsapp' => 'WhatsApp', 'instagram' => 'Instagram', 'email' => 'Email'];
        $deviceLabels = ['mobile' => 'Ponsel', 'tablet' => 'Tablet', 'desktop' => 'Desktop', 'unknown' => 'Tidak diketahui'];
    @endphp

    <div class="admin-header analytics-header">
        <div>
            <h2>Analitik Pengunjung</h2>
            <p>Ringkasan kunjungan {{ $from->format('d/m/Y') }}–{{ $to->format('d/m/Y') }} · waktu {{ $timezone }}</p>
        </div>
        <form method="GET" action="{{ route('admin.analytics') }}" class="analytics-period-form">
            <label for="analyticsDays">Rentang</label>
            <select id="analyticsDays" name="days" class="field-select" onchange="this.form.submit()">
                <option value="7" @selected($days === 7)>7 hari</option>
                <option value="30" @selected($days === 30)>30 hari</option>
                <option value="90" @selected($days === 90)>90 hari</option>
            </select>
            <noscript><button type="submit" class="btn btn-admin-ghost btn-sm">Terapkan</button></noscript>
        </form>
    </div>

    <div class="analytics-metrics">
        @foreach([
            'visitors' => ['Pengunjung unik', 'Per browser anonim'],
            'sessions' => ['Sesi kunjungan', 'Sesi dengan jeda 30 menit'],
            'contacts' => ['Klik kanal kontak', 'WhatsApp, Instagram, dan email'],
        ] as $key => [$label, $hint])
            @php $metric = $metrics[$key]; @endphp
            <article class="analytics-metric-card">
                <span class="analytics-metric-label">{{ $label }}</span>
                <strong>{{ number_format($metric['value'], 0, ',', '.') }}</strong>
                <span class="analytics-metric-hint">{{ $hint }}</span>
                @if($metric['change'] === null)
                    <span class="analytics-change is-new">Baru dibanding periode sebelumnya</span>
                @else
                    <span class="analytics-change {{ $metric['change'] > 0 ? 'is-up' : ($metric['change'] < 0 ? 'is-down' : '') }}">
                        {{ $metric['change'] > 0 ? '+' : '' }}{{ $metric['change'] }}% vs periode sebelumnya
                    </span>
                @endif
            </article>
        @endforeach
    </div>

    <section class="admin-card analytics-chart-card" aria-labelledby="analyticsChartTitle">
        <div class="analytics-section-head">
            <div>
                <h3 id="analyticsChartTitle">Pertumbuhan pengunjung</h3>
                <p>Pengunjung unik per hari dibanding periode sebelumnya yang sama panjang.</p>
            </div>
            <span class="analytics-chart-legend"><i></i> Pengunjung unik</span>
        </div>
        <div class="analytics-chart-scroll">
            <svg class="analytics-chart" viewBox="0 0 1000 270" role="img" aria-labelledby="analyticsChartTitle analyticsChartDescription">
                <desc id="analyticsChartDescription">Grafik pengunjung unik harian selama {{ $days }} hari.</desc>
                @foreach([38, 83, 128, 173, 218] as $gridY)
                    <line x1="40" y1="{{ $gridY }}" x2="960" y2="{{ $gridY }}" class="analytics-chart-grid" />
                @endforeach
                <polyline points="{{ $chartPoints }}" class="analytics-chart-line" />
                @foreach($series as $index => $day)
                    @php
                        $cx = 40 + (920 * $index / max(1, $series->count() - 1));
                        $cy = 218 - (180 * $day['visitors'] / $chartMax);
                    @endphp
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="3.2" class="analytics-chart-point">
                        <title>{{ $day['date'] }}: {{ $day['visitors'] }} pengunjung</title>
                    </circle>
                    @if($index === 0 || $index === (int) floor(($series->count() - 1) / 2) || $index === $series->count() - 1)
                        <text x="{{ $cx }}" y="252" text-anchor="{{ $index === 0 ? 'start' : ($index === $series->count() - 1 ? 'end' : 'middle') }}" class="analytics-chart-label">{{ $day['label'] }}</text>
                    @endif
                @endforeach
            </svg>
        </div>
    </section>

    <section class="admin-card analytics-activity-card" aria-labelledby="analyticsActivityTitle">
        <div class="analytics-section-head">
            <div>
                <h3 id="analyticsActivityTitle">Aktivitas sesi</h3>
                <p>Satu baris per sesi. Section dicatat saat terlihat; kanal dicatat saat diklik.</p>
            </div>
            <span class="analytics-table-count">{{ number_format($sessionsList->total(), 0, ',', '.') }} sesi</span>
        </div>

        <div class="analytics-table-scroll">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th scope="col">Waktu</th>
                        <th scope="col">Sumber</th>
                        <th scope="col">Perangkat</th>
                        <th scope="col">Section dilihat</th>
                        <th scope="col">Kontak diklik</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessionsList as $session)
                        @php
                            $sectionEvents = $session->events->where('event_type', 'section_view')->unique('target');
                            $contactEvents = $session->events->where('event_type', 'contact_click');
                            $source = $session->utm_source ?: ($session->referrer_domain ?: 'Langsung');
                        @endphp
                        <tr>
                            <td data-label="Waktu">
                                <time datetime="{{ $session->started_at->toIso8601String() }}">{{ $session->started_at->copy()->timezone($timezone)->format('d/m/Y H:i') }}</time>
                            </td>
                            <td data-label="Sumber">
                                <span>{{ $source }}</span>
                                @if($session->utm_medium || $session->utm_campaign)
                                    <small>{{ collect([$session->utm_medium, $session->utm_campaign])->filter()->implode(' · ') }}</small>
                                @endif
                            </td>
                            <td data-label="Perangkat">{{ $deviceLabels[$session->device_type] ?? $deviceLabels['unknown'] }}</td>
                            <td data-label="Section dilihat">
                                @if($sectionEvents->isEmpty())
                                    <span class="analytics-muted">Belum tercatat</span>
                                @else
                                    <div class="analytics-tags">
                                        @foreach($sectionEvents as $event)
                                            <span class="analytics-tag">{{ $sectionLabels[$event->target] ?? $event->target }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td data-label="Kontak diklik">
                                @if($contactEvents->isEmpty())
                                    <span class="analytics-muted">—</span>
                                @else
                                    <div class="analytics-tags">
                                        @foreach($contactEvents as $event)
                                            <span class="analytics-tag analytics-tag-contact">{{ $channelLabels[$event->target] ?? $event->target }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="analytics-empty">Belum ada data untuk rentang ini. Data akan muncul setelah situs berjalan dengan Laravel di hosting aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessionsList->hasPages())
            <nav class="analytics-pagination" aria-label="Navigasi halaman aktivitas">
                @if($sessionsList->onFirstPage())
                    <span class="is-disabled">Sebelumnya</span>
                @else
                    <a href="{{ $sessionsList->previousPageUrl() }}">Sebelumnya</a>
                @endif
                <span>Halaman {{ $sessionsList->currentPage() }} dari {{ $sessionsList->lastPage() }}</span>
                @if($sessionsList->hasMorePages())
                    <a href="{{ $sessionsList->nextPageUrl() }}">Berikutnya</a>
                @else
                    <span class="is-disabled">Berikutnya</span>
                @endif
            </nav>
        @endif
    </section>
</x-app-layout>
