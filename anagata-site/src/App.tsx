import { useEffect, useRef, useState } from 'react'
import { LocationMap } from './LocationMap'

type CmsEntry = { name?: string; label?: string; text?: string; position?: string; image?: string; subtitle?: string; body?: string; narrative?: string; outcome?: string; steps?: { title: string; description: string }[]; tagline?: string; headline?: string; body_1?: string; body_2?: string; body_3?: string; youtube_id?: string; credit?: string }
type CmsContent = { story: CmsEntry[]; service: CmsEntry[]; activity: CmsEntry[]; photo: CmsEntry[]; section: CmsEntry[] }
const cmsContent = (window as Window & { __ANAGATA_CONTENT__?: CmsContent }).__ANAGATA_CONTENT__
const cmsSettings = (window as Window & { __ANAGATA_SETTINGS__?: { logo?: string; email?: string; whatsapp?: string; instagram?: string } }).__ANAGATA_SETTINGS__
const logo = cmsSettings?.logo || '/anagata-images/anagata-logo-light.png'
const email = cmsSettings?.email || 'anagatafulfill@gmail.com'
const whatsapp = cmsSettings?.whatsapp || '6281930041391'
const instagram = cmsSettings?.instagram || 'anagatawarehouse'

const defaultServices = [
  {
    id: 'inbound',
    title: 'Inbound',
    subtitle: 'Penerimaan dan pencatatan produk masuk.',
    body: 'Setiap produk yang masuk dimonitor dari request seller, diterima dari seller atau supplier, diperiksa jumlah dan kondisinya, lalu dicatat ke sistem sebelum ditempatkan di lokasi penyimpanan.',
    narrative: 'Proses inbound dimulai bahkan sebelum barang tiba. Tim memonitor request dari seller agar jadwal, jumlah, dan kebutuhan penempatan sudah dipahami sejak awal. Saat produk sampai, seluruh barang melewati pemeriksaan fisik dan pencocokan data sebelum dinyatakan siap masuk ke inventory.',
    outcome: 'Seller mendapatkan catatan barang masuk yang lebih jelas, stok awal yang akurat, dan produk yang langsung tersusun pada lokasi penyimpanan yang tepat.',
    steps: [
      ['Request Monitoring', 'Request dari seller dimonitor dan divalidasi sebelum jadwal kedatangan barang.'],
      ['Receiving', 'Produk diterima dari seller atau supplier dan dicocokkan dengan informasi inbound.'],
      ['Checking & Unboxing', 'Jumlah, varian, dan kondisi fisik produk diperiksa saat proses unboxing.'],
      ['Update Inbound Data', 'Hasil pemeriksaan dicatat dan data stok masuk diperbarui di sistem.'],
      ['Putaway', 'Produk ditempatkan pada lokasi penyimpanan sesuai mapping seller dan SKU.'],
    ],
    image: '/anagata-images/warehouse-hero.jpg',
  },
  {
    id: 'storage',
    title: 'Inventory',
    subtitle: 'Penataan dan kontrol stok harian.',
    body: 'Lokasi penyimpanan diatur berdasarkan seller dan SKU. Produk disimpan sesuai kapasitas serta kebutuhan inventory agar stok tetap rapi, aman, dan mudah dipantau.',
    narrative: 'Inventory yang rapi bukan hanya tentang meletakkan barang di rak. Setiap seller dan SKU membutuhkan lokasi yang jelas agar produk mudah ditemukan, dihitung, dan diproses ketika order masuk. Penataan ini dikontrol setiap hari untuk menjaga konsistensi antara stok fisik dan data.',
    outcome: 'Produk tersimpan secara terpisah dan terstruktur, proses picking menjadi lebih efisien, dan seller memiliki visibilitas stok yang lebih dapat diandalkan.',
    steps: [
      ['Location Mapping', 'Area penyimpanan dirancang berdasarkan kebutuhan setiap seller.'],
      ['Seller & SKU Slotting', 'Setiap SKU mendapat slot yang jelas untuk mengurangi risiko tertukar.'],
      ['Storage', 'Produk disimpan sesuai kapasitas, karakter, dan kebutuhan inventory.'],
      ['Daily Control', 'Kondisi lokasi dan pergerakan stok diperiksa secara berkala setiap hari.'],
    ],
    image: '/anagata-images/inventory.jpg',
  },
  {
    id: 'packing',
    title: 'Pick & Pack',
    subtitle: 'Verifikasi order sampai pengemasan.',
    body: 'Produk diambil sesuai order, diverifikasi kembali berdasarkan SKU, varian, dan quantity, kemudian dikemas mengikuti SOP fulfillment sebelum masuk ke tahap sortir.',
    narrative: 'Ketika order diterima, tim memproses data pesanan dan mengambil produk dari lokasi yang sudah dipetakan. Setiap item kembali diverifikasi sebelum dikemas agar SKU, varian, dan jumlah sesuai dengan pesanan pelanggan.',
    outcome: 'Pesanan diproses dengan standar yang konsisten, risiko kesalahan produk berkurang, dan paket siap memasuki proses pengiriman pada hari yang sama.',
    steps: [
      ['Order Processing', 'Data order seller diterima dan disiapkan untuk proses operasional.'],
      ['Picking', 'Produk diambil dari lokasi penyimpanan sesuai daftar order.'],
      ['QC Verification', 'SKU, varian, quantity, dan kondisi produk diverifikasi kembali.'],
      ['Packing', 'Pesanan dikemas mengikuti SOP fulfillment dan kebutuhan brand.'],
      ['Sorting', 'Paket yang selesai dipisahkan berdasarkan layanan pengiriman dan 3PL.'],
    ],
    image: '/anagata-images/packing.jpg',
  },
  {
    id: 'outbound',
    title: 'Outbound',
    subtitle: 'Proses order sampai ke ekspedisi.',
    body: 'Pesanan diproses, diverifikasi, dikemas, dan disortir berdasarkan layanan pengiriman sebelum diserahterimakan kepada mitra 3PL seperti JNE, J&T, SiCepat, SPX, dan AnterAja.',
    narrative: 'Outbound menghubungkan operasional gudang dengan perjalanan terakhir paket menuju pelanggan. Setelah order selesai diproses, paket disortir berdasarkan ekspedisi dan layanan pengiriman agar serah terima kepada mitra 3PL berjalan teratur.',
    outcome: 'Seller mendapatkan proses handover yang lebih tertib, status outbound yang diperbarui, dan paket yang siap bergerak melalui mitra pengiriman terpercaya.',
    steps: [
      ['Order Processing', 'Order outbound seller diterima dan masuk ke antrean pemrosesan.'],
      ['QC', 'Kesesuaian produk dan data pengiriman diverifikasi sebelum paket ditutup.'],
      ['Picking & Packing', 'Produk diambil, dikemas, dan dilabeli sesuai order.'],
      ['Sorting', 'Paket dikelompokkan berdasarkan layanan dan mitra ekspedisi.'],
      ['3PL Handover', 'Paket diserahterimakan kepada JNE, J&T, SiCepat, SPX, AnterAja, atau mitra terkait.'],
      ['Status Update', 'Data outbound dan status penyerahan diperbarui setelah handover.'],
    ],
    image: '/anagata-images/operations.jpg',
  },
  {
    id: 'return',
    title: 'Return',
    subtitle: 'Pengelolaan produk kembali.',
    body: 'Paket return diterima dari 3PL, dibuka dan diperiksa kondisi serta isinya, kemudian data return diperbarui sebelum produk ditempatkan kembali ke inventory.',
    narrative: 'Perjalanan paket tidak selalu selesai saat dikirim. Ketika return datang dari 3PL, tim menerima dan membuka kembali paket untuk memahami kondisi produk serta memastikan isi return sesuai dengan data yang tersedia.',
    outcome: 'Produk return terdokumentasi dengan jelas, kondisi barang diketahui, dan item yang masih layak dapat dikembalikan ke inventory secara teratur.',
    steps: [
      ['Return Receiving', 'Paket return diterima dari pihak 3PL dan dicatat sebagai barang kembali.'],
      ['Checking & Unboxing', 'Isi paket dan kondisi produk diperiksa saat proses pembukaan.'],
      ['Update Return Data', 'Hasil pemeriksaan dicatat dan status return diperbarui.'],
      ['Putaway', 'Produk yang dapat disimpan kembali ditempatkan ke lokasi inventory.'],
    ],
    image: '/anagata-images/inventory.jpg',
  },
]

export type WarehouseLocation = { name: string; area: string; address: string; status: 'hub' | 'coming' | 'listed'; lat?: number | null; lng?: number | null }
type ShippingPartner = { name: string; image: string }
declare global { interface Window { __ANAGATA_LOCATIONS__?: WarehouseLocation[]; __ANAGATA_PARTNERS__?: ShippingPartner[] } }
const shippingPartners = window.__ANAGATA_PARTNERS__ ?? []
const locations: WarehouseLocation[] = window.__ANAGATA_LOCATIONS__ ?? [
  { name: 'Warehouse Jakarta', area: 'Duren Sawit, Jakarta Timur', address: 'Jl. Bojong Indah Raya No.4, Duren Sawit, Jakarta Timur 13450', status: 'hub', lat: -6.233975, lng: 106.927144 },
  { name: 'Warehouse Makassar', area: 'Rappocini, Makassar', address: 'Jl. Cilallang Jaya VII No.17, Rappocini, Makassar 90222', status: 'hub', lat: -5.147, lng: 119.432 },
  { name: 'Warehouse Surabaya', area: 'Surabaya, Jawa Timur', address: 'Perumahan kehakiman blok C no :7, Babat Jerawat, Kec. Pakal, Surabaya, Jawa Timur 60197', status: 'listed', lat: -7.245230, lng: 112.627834 },
  { name: 'Warehouse Yogyakarta', area: 'Yogyakarta, DI Yogyakarta', address: 'Jalan Margotirto UH V No.40/10 283 A, Pandeyan, Kec. Umbulharjo, Kota Yogyakarta, Daerah Istimewa Yogyakarta 55161', status: 'listed', lat: -7.822245, lng: 110.393732 },
]

const defaultStoryChapters = [
  {
    image: '/anagata-images/operations.jpg',
    position: 'left',
    label: 'A beginning',
    text: 'Setiap bisnis dimulai dari sebuah mimpi. Anagata hadir untuk menjaga mimpi itu tetap bergerak.',
  },
  {
    image: '/anagata-images/inventory.jpg',
    position: 'right',
    label: 'Room to grow',
    text: 'Bertumbuh bukan hanya tentang menjual lebih banyak, tetapi tentang membangun proses yang mampu mengikuti setiap langkahmu.',
  },
  {
    image: '/anagata-images/packing.jpg',
    position: 'left',
    label: 'Made by people',
    text: 'Terinspirasi oleh manusia dan kerja keras, kami menyatukan ruang, sistem, dan perhatian dalam satu perjalanan.',
  },
  {
    image: '/anagata-images/warehouse-hero.jpg',
    position: 'right',
    label: 'Future in motion',
    text: 'Bayangkan operasional yang lebih ringan. Pelanggan yang lebih bahagia. Dan bisnismu melangkah lebih jauh.',
  },
  {
    image: '/anagata-images/hero.jpg',
    position: 'center',
    label: 'The next chapter',
    text: 'Apakah kamu siap tumbuh bersama kami?',
  },
]

const defaultActivityItems = [
  {
    id: 'people',
    title: 'People',
    tagline: 'Manusia di balik setiap paket.',
    body: 'Tim yang peduli membuat sistem terasa manusiawi. Setiap barang disentuh, diperiksa, dan dipersiapkan dengan perhatian.',
    image: '/anagata-images/packing.jpg',
  },
  {
    id: 'process',
    title: 'Process',
    tagline: 'Gerak cepat, tetap terukur.',
    body: 'Alur kerja yang jelas membantu kami bergerak cepat tanpa kehilangan akurasi pada setiap tahap fulfillment.',
    image: '/anagata-images/operations.jpg',
  },
  {
    id: 'purpose',
    title: 'Purpose',
    tagline: 'Ruang untuk tumbuh lebih jauh.',
    body: 'Semua yang kami kerjakan punya satu tujuan: memberi pemilik bisnis lebih banyak ruang untuk fokus pada langkah berikutnya.',
    image: '/anagata-images/inventory.jpg',
  },
]

const mainHubItems = [
  {
    id: 'activity',
    title: 'Working Activity',
    tagline: 'People, process, and purpose.',
    body: 'Lihat energi dan cara kerja yang menggerakkan Anagata setiap hari.',
    image: '/anagata-images/packing.jpg',
  },
  {
    id: 'services',
    title: 'Our Services',
    tagline: 'Fulfillment yang saling terhubung.',
    body: 'Dari inbound sampai return, semua bergerak dalam satu alur yang jelas.',
    image: '/anagata-images/operations.jpg',
  },
  {
    id: 'locations',
    title: 'Our Locations',
    tagline: 'Lebih dekat dengan pertumbuhanmu.',
    body: 'Temukan lokasi aktif kami dan rencana lokasi berikutnya.',
    image: '/anagata-images/hero.jpg',
  },
]

const defaultCollageItems = [
  ['/anagata-images/packing.jpg', 'PACK / 08:42', '2%', '4%', '17%', '15%', '-8deg'],
  ['/anagata-images/inventory.jpg', 'STOCK / DAILY', '27%', '2%', '13%', '19%', '5deg'],
  ['/anagata-images/warehouse-hero.jpg', 'PEOPLE / ANAGATA', '75%', '3%', '20%', '16%', '7deg'],
  ['/anagata-images/operations.jpg', 'MOVE / FORWARD', '47%', '8%', '22%', '13%', '-4deg'],
  ['/anagata-images/packing.jpg', 'DETAIL / MATTERS', '8%', '22%', '21%', '14%', '4deg'],
  ['/anagata-images/inventory.jpg', 'COUNT / CHECK', '34%', '23%', '17%', '17%', '-7deg'],
  ['/anagata-images/operations.jpg', 'FLOW / FORWARD', '69%', '21%', '26%', '15%', '3deg'],
  ['/anagata-images/warehouse-hero.jpg', 'BETTER / TOGETHER', '52%', '28%', '13%', '19%', '8deg'],
  ['/anagata-images/inventory.jpg', 'SPACE / GROW', '1%', '40%', '16%', '19%', '-5deg'],
  ['/anagata-images/packing.jpg', 'HANDS / CARE', '20%', '42%', '23%', '14%', '6deg'],
  ['/anagata-images/operations.jpg', 'DAILY / MOTION', '73%', '41%', '17%', '18%', '-8deg'],
  ['/anagata-images/warehouse-hero.jpg', 'TEAM / ANAGATA', '47%', '48%', '21%', '14%', '2deg'],
  ['/anagata-images/packing.jpg', 'READY / TO GO', '4%', '63%', '23%', '14%', '7deg'],
  ['/anagata-images/inventory.jpg', 'ORGANIZED / DAILY', '31%', '59%', '14%', '19%', '-3deg'],
  ['/anagata-images/warehouse-hero.jpg', 'PEOPLE / FIRST', '76%', '61%', '20%', '15%', '5deg'],
  ['/anagata-images/operations.jpg', 'OUTBOUND / MOTION', '50%', '65%', '19%', '15%', '-7deg'],
  ['/anagata-images/inventory.jpg', 'REAL TIME / STOCK', '1%', '80%', '15%', '16%', '-4deg'],
  ['/anagata-images/packing.jpg', 'PACKED / WITH CARE', '20%', '78%', '20%', '14%', '8deg'],
  ['/anagata-images/operations.jpg', 'FORWARD / 10', '72%', '78%', '25%', '14%', '-3deg'],
  ['/anagata-images/warehouse-hero.jpg', 'GROW / 11', '45%', '82%', '17%', '16%', '6deg'],
  ['/anagata-images/packing.jpg', 'ONE BY ONE', '5%', '84%', '18%', '12%', '-8deg'],
  ['/anagata-images/inventory.jpg', 'SAFE / 12', '28%', '82%', '13%', '15%', '3deg'],
  ['/anagata-images/operations.jpg', 'NEXT MOVE', '67%', '85%', '21%', '12%', '7deg'],
  ['/anagata-images/warehouse-hero.jpg', 'FUTURE / NOW', '48%', '81%', '13%', '16%', '-5deg'],
]

const services = defaultServices.map((item, i) => {
  const entry = cmsContent?.service?.[i]
  return entry ? { ...item, title: entry.name ?? item.title, subtitle: entry.subtitle ?? item.subtitle, body: entry.body ?? item.body, narrative: entry.narrative ?? item.narrative, outcome: entry.outcome ?? item.outcome, image: entry.image || item.image, steps: entry.steps ? entry.steps.map(step => [step.title, step.description]) : item.steps } : item
})
const storyChapters = defaultStoryChapters.map((item, i) => ({ ...item, ...(cmsContent?.story?.[i] ? { label: cmsContent.story[i].label ?? item.label, text: cmsContent.story[i].text ?? item.text, image: cmsContent.story[i].image || item.image, position: cmsContent.story[i].position || item.position } : {}) }))
const activityItems = defaultActivityItems.map((item, i) => ({ ...item, ...(cmsContent?.activity?.[i] ? { title: cmsContent.activity[i].name ?? item.title, tagline: cmsContent.activity[i].tagline ?? item.tagline, body: cmsContent.activity[i].body ?? item.body, image: cmsContent.activity[i].image || item.image } : {}) }))
const collageItems = defaultCollageItems.map((item, i) => cmsContent?.photo?.[i] ? [cmsContent.photo[i].image || item[0], cmsContent.photo[i].name || item[1]] : item)
const storySections = cmsContent?.section || []
const cmsText = (index: number, key: keyof CmsEntry, fallback: string) => String(storySections[index]?.[key] ?? fallback)
const storyHeading = (text: string) => { const [start, ...emphasis] = text.split('|'); return <>{start}{emphasis.length > 0 && <em>{emphasis.join('|')}</em>}</> }

// Each frame has its own position and proportions, rather than fixed rows.
const collageLayout = [
  [0, 2, 17, 14], [21, 0, 11, 19], [36, 5, 15, 12], [55, 0, 10, 19], [70, 3, 15, 13], [89, 0, 11, 19],
  [4, 21, 12, 18], [20, 24, 16, 12], [40, 20, 10, 19], [54, 23, 18, 12], [77, 21, 12, 18],
  [0, 43, 16, 13], [20, 41, 11, 18], [35, 44, 17, 13], [57, 40, 10, 19], [72, 43, 17, 13], [91, 40, 9, 19],
  [4, 63, 12, 18], [32, 65, 17, 12], [70, 61, 11, 19], [58, 84, 16, 13], [85, 83, 15, 14],
  [0, 84, 18, 13], [29, 84, 12, 14],
]

const mobileCollageLayout = [
  [0, 1, 31, 9], [36, 0, 23, 11], [65, 2, 35, 8],
  [4, 13, 25, 11], [34, 13, 33, 8], [73, 12, 25, 11],
  [0, 25, 33, 8], [38, 24, 23, 11], [67, 25, 30, 9],
  [4, 37, 25, 11], [34, 38, 34, 8], [74, 36, 26, 11],
  [0, 50, 32, 8], [38, 49, 23, 11], [67, 50, 29, 9],
  [3, 62, 27, 11], [35, 62, 33, 8], [74, 61, 26, 11],
  [0, 74, 32, 8], [38, 73, 23, 11], [66, 74, 34, 8],
  [4, 86, 28, 10], [37, 86, 29, 9], [72, 85, 25, 11],
]

const Arrow = () => (
  <svg viewBox="0 0 32 32" aria-hidden="true">
    <circle cx="16" cy="16" r="15" />
    <path d="M10 16h11M17 11l5 5-5 5" />
  </svg>
)

export default function App() {
  const [showSplash, setShowSplash] = useState(() => {
    if (window.location.hash || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return false
    try { return sessionStorage.getItem('anagata.splash.seen') !== '1' } catch { return true }
  })
  const [splashLeaving, setSplashLeaving] = useState(false)
  const [entered, setEntered] = useState(false)
  const [lowerNavVisible, setLowerNavVisible] = useState(false)
  const [hoveredHub, setHoveredHub] = useState(0)
  const [activeHub, setActiveHub] = useState<string | null>(null)
  const [activeActivity, setActiveActivity] = useState(0)
  const [activeService, setActiveService] = useState(0)
  const [showStoryVideo, setShowStoryVideo] = useState(false)
  const [collageVideo, setCollageVideo] = useState<{ id: string; title: string } | null>(null)
  const collageDialog = useRef<HTMLDialogElement>(null)

  useEffect(() => {
    if (collageVideo && collageDialog.current && !collageDialog.current.open) collageDialog.current.showModal()
  }, [collageVideo])
  const [selectedLocation, setSelectedLocation] = useState<string | null>(null)
  const storyVideoId = cmsText(3, 'youtube_id', 'TprgJuLRPFY')
  const hasStoryVideo = /^[A-Za-z0-9_-]{11}$/.test(storyVideoId)

  useEffect(() => {
    if (!showSplash) return
    const leaving = window.setTimeout(() => setSplashLeaving(true), 1100)
    const complete = window.setTimeout(() => {
      try { sessionStorage.setItem('anagata.splash.seen', '1') } catch { /* Session storage may be unavailable. */ }
      setShowSplash(false)
    }, 1600)
    return () => { window.clearTimeout(leaving); window.clearTimeout(complete) }
  }, [showSplash])

  useEffect(() => {
    // The opening locks scrolling; always restore its position on a fresh load.
    if ('scrollRestoration' in window.history) window.history.scrollRestoration = 'manual'
    window.scrollTo({ top: 0, behavior: 'instant' })
  }, [])

  useEffect(() => {
    const navigation = document.querySelector<HTMLElement>('.lower-nav')
    if (!navigation) return
    const updateHeight = () => document.documentElement.style.setProperty('--main-nav-height', `${navigation.offsetHeight}px`)
    updateHeight()
    if (typeof ResizeObserver === 'undefined') {
      window.addEventListener('resize', updateHeight)
      return () => window.removeEventListener('resize', updateHeight)
    }
    const observer = new ResizeObserver(updateHeight)
    observer.observe(navigation)
    return () => observer.disconnect()
  }, [])

  useEffect(() => {
    const onScroll = () => {
      const pageHeight = document.documentElement.scrollHeight - window.innerHeight
      const progress = pageHeight > 0 ? window.scrollY / pageHeight : 0
      document.documentElement.style.setProperty(
        '--scroll-progress',
        String(Math.min(1, Math.max(0, progress))),
      )
      const activity = document.querySelector('#working-activity')
      setLowerNavVisible(Boolean(activity && activity.getBoundingClientRect().top <= 80))
    }
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  useEffect(() => {
    const elements = document.querySelectorAll<HTMLElement>('[data-reveal]')
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      elements.forEach((element) => element.classList.add('revealed'))
      return
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('revealed')
            observer.unobserve(entry.target)
          }
        })
      },
      { threshold: 0.14 },
    )

    elements.forEach((element) => observer.observe(element))
    return () => observer.disconnect()
  }, [])

  useEffect(() => {
    document.body.style.overflow = !entered ? 'hidden' : ''
    return () => {
      document.body.style.overflow = ''
    }
  }, [entered])

  const scrollBehavior = (): ScrollBehavior => window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'

  const beginJourney = () => {
    setEntered(true)
    window.setTimeout(
      () => document.querySelector('#journey')?.scrollIntoView({ behavior: scrollBehavior() }),
      40,
    )
  }

  const openHub = (id: string) => {
    setActiveHub(id)
    if (id === 'activity') setActiveActivity(0)
    const selector =
      id === 'services'
        ? '.service-longform'
        : id === 'activity'
          ? '.activity-overview'
          : '.menu-hub .hub-detail'
    window.setTimeout(
      () => {
        const target = document.querySelector(selector)
        if (!target) return
        const top = target.getBoundingClientRect().top + window.scrollY - 82
        window.scrollTo({ top, behavior: scrollBehavior() })
      },
      100,
    )
  }

  const navigateHub = (id: string) => {
    openHub(id)
  }

  const selectService = (index: number) => {
    setActiveService(index)
    window.requestAnimationFrame(() => {
      const hero = document.querySelector('.service-narrative-hero')
      const navigation = document.querySelector('.lower-nav')
      const switcher = document.querySelector('.service-switcher')
      if (!hero || !navigation || !switcher) return
      const top = hero.getBoundingClientRect().top + window.scrollY
        - navigation.getBoundingClientRect().height - switcher.getBoundingClientRect().height - 16
      window.scrollTo({ top: Math.max(0, top), behavior: scrollBehavior() })
    })
  }

  return (
    <main className={entered ? 'experience-entered' : 'experience-locked'}>
      {showSplash && <div className={`site-splash${splashLeaving ? ' is-leaving' : ''}`} role="status" aria-label="Membuka Anagata Warehouse">
        <img src={logo} alt="" />
        <div className="site-splash-line" aria-hidden="true"><span /></div>
      </div>}
      <div className="scroll-progress" aria-hidden="true" />
      <header className={`lower-nav ${lowerNavVisible ? 'visible' : ''}`}>
        <a
          href="#working-activity"
          className="lower-nav-brand"
          onClick={() => setActiveHub(null)}
        >
          <img src={logo} alt="Anagata Warehouse" />
        </a>
        <nav>
          <button onClick={() => navigateHub('activity')}>Activity</button>
          <button onClick={() => navigateHub('services')}>Services</button>
          <button onClick={() => navigateHub('locations')}>Locations</button>
          <a href="#contact">Contact</a>
        </nav>
      </header>

      <section className="opening" id="home" data-analytics-section="intro">
        <div className="opening-grid" />
        <div className="opening-shade" />
        <div className="opening-center">
          <div className="type-wordmark" aria-label={`${cmsText(5, 'headline', 'ANAGATA')} ${cmsText(5, 'body_1', 'WAREHOUSE')}`}>
            <strong>{cmsText(5, 'headline', 'ANAGATA')}</strong>
            <span>{cmsText(5, 'body_1', 'WAREHOUSE')}</span>
          </div>
          <button onClick={beginJourney} data-journey-start>{cmsText(5, 'body_2', 'Jelajahi Anagata')}</button>
        </div>
        <span className="opening-coordinate">06°12′S · 106°49′E</span>
        <span className="opening-year">Indonesia · 2024</span>
      </section>

      <section className="journey" id="journey">
        {storyChapters.map((chapter) => (
          <article className={`story-chapter ${chapter.position}`} key={chapter.label}>
            <img src={chapter.image} alt="" loading="lazy" decoding="async" />
            <div className="chapter-violet" />
            <div className="chapter-copy" data-reveal>
              <span>{chapter.label}</span>
              <p>{chapter.text}</p>
            </div>
          </article>
        ))}
      </section>

      <section className="story-film" aria-labelledby="story-film-title">
        <div className="story-film-copy" data-reveal>
          <span>People behind the process</span>
          <h2 id="story-film-title">{storyHeading(cmsText(3, 'headline', 'Ada cerita di balik |setiap paket.'))}</h2>
          <p>{cmsText(3, 'body_1', 'Di balik setiap pesanan, ada orang-orang yang bekerja bersama: menerima barang, menata stok, dan menyiapkannya untuk perjalanan berikutnya.')}</p>
        </div>
        <div className="story-film-player">
          {showStoryVideo && hasStoryVideo ? (
            <iframe title="Video aktivitas gudang" src={`https://www.youtube-nocookie.com/embed/${storyVideoId}?autoplay=1&rel=0`} allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowFullScreen />
          ) : hasStoryVideo ? (
            <button type="button" className="story-film-play" onClick={() => setShowStoryVideo(true)} aria-label="Putar video referensi aktivitas gudang">
              <img src={cmsText(3, 'image', '/anagata-images/operations.jpg')} alt="" loading="lazy" />
              <span className="story-film-play-icon" aria-hidden="true">▶</span>
              <span className="story-film-play-label">Lihat aktivitas gudang</span>
            </button>
          ) : <img className="story-film-still" src={cmsText(3, 'image', '/anagata-images/operations.jpg')} alt="" loading="lazy" />}
        </div>
        <p className="story-film-credit">{cmsText(3, 'credit', 'Video referensi: DFDS & Miele · YouTube')}</p>
      </section>

      <section className="floating-gallery" aria-label="Kegiatan Anagata Warehouse" data-analytics-section="collage_finale">
        <div className="collage-loop" aria-hidden="true">
          {[0, 1, 2].map(row => <div className="collage-loop-row" key={row}><div className="collage-loop-track"><span>#LET’S GROW TOGETHER&nbsp; · &nbsp;</span><span>#LET’S GROW TOGETHER&nbsp; · &nbsp;</span></div></div>)}
        </div>
        <div className="collage-photos">
        {collageItems.map(([image, label], index) => {
          const youtubeId = cmsContent?.photo?.[index]?.youtube_id || ''
          const hasVideo = /^[A-Za-z0-9_-]{11}$/.test(youtubeId)
          return <figure className={`collage-piece piece-${index + 1}${[1, 5, 8, 12, 16, 20].includes(index) ? ' collage-polaroid' : ''}`} key={`${label}-${index}`} style={{ '--x': `${collageLayout[index][0]}%`, '--y': `${collageLayout[index][1]}%`, '--w': `${collageLayout[index][2]}%`, '--h': `${collageLayout[index][3]}%`, '--mx': `${mobileCollageLayout[index][0]}%`, '--my': `${mobileCollageLayout[index][1]}%`, '--mw': `${mobileCollageLayout[index][2]}%`, '--mh': `${mobileCollageLayout[index][3]}%` } as React.CSSProperties}>
            <img src={image} alt="" loading="lazy" decoding="async" />
            {hasVideo && <button type="button" className="collage-video-play" onClick={() => setCollageVideo({ id: youtubeId, title: label })} aria-label={`Putar video ${label}`}><span aria-hidden="true">▶</span></button>}
            <figcaption>{label}</figcaption>
          </figure>
        })}
        </div>
        <div className="collage-notes" aria-hidden="true">
          <div className="collage-accent collage-accent-mint">made with<br />care <span>↗</span></div>
          <div className="collage-handwriting">the people<br />behind it all ↗</div>
          <div className="collage-accent collage-accent-orange">little details,<br />big meaning.</div>
        </div>
      </section>

      {collageVideo && <dialog className="collage-video-dialog" ref={collageDialog} onClose={() => setCollageVideo(null)} aria-label={`Video ${collageVideo.title}`}>
        <div className="collage-video-dialog-head"><span>{collageVideo.title}</span><button type="button" onClick={() => collageDialog.current?.close()} autoFocus aria-label="Tutup video">✕</button></div>
        <div className="collage-video-frame"><iframe title={`Video ${collageVideo.title}`} src={`https://www.youtube-nocookie.com/embed/${collageVideo.id}?autoplay=1&rel=0`} allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowFullScreen /></div>
      </dialog>}

      <section className="foreword" id="foreword" data-analytics-section="team_note">
        <div className="foreword-title" data-reveal>
          <span>Sekapur Sirih</span>
          <h2>{cmsText(0, 'headline', 'Untuk para pemilik mimpi di Indonesia.')}</h2>
        </div>
        <div className="foreword-body" data-reveal>
          <aside className="foreword-visual">
            <img src={cmsText(0, 'image', '/anagata-images/packing.jpg')} alt="Aktivitas menyiapkan pesanan di gudang" />
            <div>
              <span>A note from us</span>
              <strong>#Let'sGrowTogether</strong>
            </div>
          </aside>
          <article>
            <p>
              {cmsText(0, 'body_1', 'Untuk setiap pemilik bisnis yang memulai dengan sebuah ide dan terus menjaganya hingga menjadi pesanan pertama: kami tahu ada banyak hal yang kamu kerjakan di balik layar.')}
            </p>
            <p>
              {cmsText(0, 'body_2', 'Setiap produk membawa kerja kerasmu. Setiap paket juga membawa kepercayaan pelanggan. Karena itu, perhatian pada detail menjadi bagian dari cara kami mendampingi perjalanan bisnismu.')}
            </p>
            <p>
              {cmsText(0, 'body_3', 'Dari barang yang tiba sampai pesanan siap dikirim, kami membantu merapikan prosesnya agar kamu punya lebih banyak ruang untuk fokus pada pelanggan dan langkah berikutnya.')}
            </p>
            <div>
              <span>Anagata Warehouse</span>
              <small>Indonesia · #Let'sGrowTogether</small>
            </div>
          </article>
        </div>
      </section>

      <section className="letter" id="meaning" data-analytics-section="meaning">
        <div className="letter-heading" data-reveal>
          <span>Arti dari Anagata</span>
          <h2>
            {storyHeading(cmsText(1, 'headline', 'Sebuah nama untuk sesuatu yang |akan datang.'))}
          </h2>
        </div>
        <div className="letter-body" data-reveal>
          <div className="letter-aside">
            <img src={cmsText(1, 'image', '/anagata-images/inventory.jpg')} alt="" loading="lazy" />
            <div className="letter-definition"><span>Anagata</span><p>/a·na·ga·ta/</p><small>Masa depan; sesuatu yang akan datang.</small></div>
          </div>
          <article>
            <p>
              {cmsText(1, 'body_1', 'Anagata berarti masa depan—harapan baik untuk sesuatu yang akan datang. Bagi kami, harapan itu dimulai dari hal-hal yang kita kerjakan hari ini.')}
            </p>
            <p>
              {cmsText(1, 'body_2', 'Saat barang diterima, stok ditata, dan setiap pesanan dipersiapkan, ada perhatian pada detail yang membantu perjalanan sebuah brand terus berjalan.')}
            </p>
            <p>
              {cmsText(1, 'body_3', 'Kami hadir sebagai teman perjalanan seller, menjaga proses di belakang layar agar kamu dapat melangkah lebih yakin menuju rencana berikutnya.')}
            </p>
            <div className="letter-sign">
              <strong>#Let'sGrowTogether</strong>
              <span>Anagata Warehouse</span>
            </div>
          </article>
        </div>
      </section>

      <section className="history" id="history" data-analytics-section="history">
        <div className="history-image">
          <img src={cmsText(2, 'image', '/anagata-images/warehouse-hero.jpg')} alt="Aktivitas di area gudang" loading="lazy" />
          <span>Sejak<br /><strong>2024</strong></span>
        </div>
        <article data-reveal>
          <span>Sejarah Anagata</span>
          <h2>{storyHeading(cmsText(2, 'headline', 'Lahir dari komitmen menjadi |sahabat seller.'))}</h2>
          <div className="history-copy">
            <p>
              {cmsText(2, 'body_1', 'Anagata Warehouse berdiri di bawah bendera CV Anagata Satria Nusantara sejak 2024, bergerak di lini fulfillment dan home packing. Nama “Anagata” berarti harapan yang baik, untuk seller, kurir, buyer, dan setiap orang yang terlibat di dalamnya.')}
            </p>
            <p>
              {cmsText(2, 'body_2', 'Kami hadir untuk mempermudah seller mengelola inventory, data, packing, hingga urusan shipping, supaya seller bisa fokus mengembangkan bisnisnya, bukan sibuk mengurus gudang.')}
            </p>
          </div>
          <button className="story-service-link" type="button" onClick={() => navigateHub('services')}>Kenali layanan kami <span aria-hidden="true">↗</span></button>
        </article>
      </section>

      <div className="lower-experience">
      <section className="menu-hub" id="working-activity" aria-label="Explore Anagata">
        <div className="culture-marquee">
          <div>People · Process · Purpose · People · Process · Purpose ·</div>
          <div aria-hidden="true">People · Process · Purpose · People · Process · Purpose ·</div>
        </div>
        {!activeHub ? (
          <div className="hub-overview" data-reveal>
            <div className="hub-intro">
              <span>Explore Anagata</span>
              <h2>{mainHubItems[hoveredHub].title}</h2>
              <strong>{mainHubItems[hoveredHub].tagline}</strong>
              <p>{mainHubItems[hoveredHub].body}</p>
            </div>
            <div className="hub-cards">
              {mainHubItems.map((item, index) => (
                <button
                  key={item.id}
                  onMouseEnter={() => setHoveredHub(index)}
                  onFocus={() => setHoveredHub(index)}
                  onClick={() => openHub(item.id)}
                >
                  <img src={item.image} alt="" loading="lazy" decoding="async" />
                  <span>{item.title}</span>
                </button>
              ))}
            </div>
          </div>
        ) : activeHub === 'activity' ? (
          <div className="hub-overview activity-overview">
            <div className="hub-intro">
              <span>Working activity</span>
              <h2>{activityItems[activeActivity].title}</h2>
              <strong>{activityItems[activeActivity].tagline}</strong>
              <p>{activityItems[activeActivity].body}</p>
            </div>
            <div className="hub-cards">
              {activityItems.map((item, index) => (
                <button
                  className={activeActivity === index ? 'active' : ''}
                  key={item.id}
                  onMouseEnter={() => setActiveActivity(index)}
                  onFocus={() => setActiveActivity(index)}
                  onClick={() => setActiveActivity(index)}
                  aria-pressed={activeActivity === index}
                >
                  <img src={item.image} alt="" loading="lazy" decoding="async" />
                  <span>{item.title}</span>
                </button>
              ))}
            </div>
          </div>
        ) : (
          <div className={`hub-detail ${activeHub === 'services' ? 'service-detail-mode' : ''}`}>
            {activeHub === 'services' && (
              <div className="service-longform" data-analytics-section="services">
                <div className="detail-tabs service-switcher" role="group" aria-label="Pilih layanan">
                  {services.map((service, index) => (
                    <button
                      className={activeService === index ? 'active' : ''}
                      key={service.id}
                      onClick={() => selectService(index)}
                      aria-pressed={activeService === index}
                    >
                      {service.title}
                    </button>
                  ))}
                </div>
                <div className="service-narrative-hero">
                  <div className="hub-detail-copy">
                    <span>Our services</span>
                    <h3>{services[activeService].title}</h3>
                    <strong>{services[activeService].subtitle}</strong>
                    <p>{services[activeService].body}</p>
                    <p className="service-outcome"><strong>Untuk seller:</strong> {services[activeService].outcome}</p>
                  <div className="detail-tabs">
                      <span>Warehouse operations</span>
                      <span>Clear SOP</span>
                      <span>Seller focused</span>
                    </div>
                  </div>
                  <div className="hub-detail-image">
                    <img
                      key={services[activeService].id}
                      src={services[activeService].image}
                      alt={services[activeService].title}
                    />
                  </div>
                </div>
                <div className="service-narrative-copy">
                  <span>Bagaimana prosesnya bekerja</span>
                  <h4>{services[activeService].narrative}</h4>
                  <p>{services[activeService].outcome}</p>
                </div>
                <ol className="service-timeline">
                  {services[activeService].steps.map(([title, description]) => (
                    <li key={title}>
                      <i />
                      <div>
                        <strong>{title}</strong>
                        <p>{description}</p>
                      </div>
                    </li>
                  ))}
                </ol>
                <div className="service-narrative-cta">
                  <span>Siap merapikan operasionalmu?</span>
                  <h4>Biarkan kami mengurus prosesnya.<br />Kamu fokus menumbuhkan brand.</h4>
                  <a href={`https://wa.me/${whatsapp}`} target="_blank" rel="noreferrer" data-analytics-contact="whatsapp">
                    Diskusikan kebutuhanmu <Arrow />
                  </a>
                </div>
              </div>
            )}
            {activeHub === 'locations' && (
              <>
                <div className="hub-detail-copy" data-analytics-section="location">
                  <span>Our locations</span>
                  <h3>Dekat dengan<br />pertumbuhanmu.</h3>
                  <div className="hub-location-list">
                    {locations.map((location) => {
                      const hasAddress = location.status !== 'coming' && Boolean(location.address)
                      return <div className="location-entry" key={location.name}>
                        <button type="button" className={selectedLocation === location.name ? 'is-selected' : ''} onClick={() => { setSelectedLocation(location.name); if (window.matchMedia('(max-width: 900px)').matches) document.querySelector('.location-map-wrap')?.scrollIntoView({ behavior: scrollBehavior(), block: 'center' }) }} aria-pressed={selectedLocation === location.name}>
                          <strong>{location.name.replace(/^Warehouse\s+/i, '')}</strong><span>{hasAddress ? location.address : location.area}</span>
                        </button>
                        {hasAddress ? <a className="location-status is-active" href={`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(location.address)}`} target="_blank" rel="noopener noreferrer">Lihat di Google Maps ↗</a> : <span className="location-status is-coming">Segera hadir</span>}
                      </div>
                    })}
                  </div>
                </div>
                <LocationMap locations={locations} selected={selectedLocation} onSelect={setSelectedLocation} />
              </>
            )}
          </div>
        )}
      </section>

      {shippingPartners.length > 0 && <section className="shipping-partners" aria-labelledby="shipping-partners-title" data-analytics-section="marquee">
        <div className="shipping-partners-heading">
          <span>Mitra pengiriman</span>
          <h2 id="shipping-partners-title">Bersama, paket melanjutkan <em>perjalanan.</em></h2>
          <p>Setelah disiapkan, pesanan diserahkan kepada mitra ekspedisi untuk melanjutkan perjalanan menuju pelanggan.</p>
        </div>
        <ul className="shipping-partners-grid">{shippingPartners.map(partner => <li key={partner.name}><img src={partner.image} alt={partner.name} loading="lazy" /></li>)}</ul>
      </section>}

      <section className="closing" id="contact" data-analytics-section="contact">
        <img src={cmsText(4, 'image', '/anagata-images/warehouse-hero.jpg')} alt="" loading="lazy" decoding="async" />
        <div className="closing-shade" />
        <div className="closing-copy">
          <span>Perjalanan berikutnya dimulai di sini</span>
          <i>Future in motion</i>
          <h2>{cmsText(4, 'headline', "#Let'sGrowTogether")}</h2>
          <p>
            {cmsText(4, 'body_1', 'Bisnismu punya cerita. Mari kita jaga perjalanan setiap pesanannya, bersama.')}
          </p>
          <a
            href={`https://wa.me/${whatsapp}?text=Halo%20Anagata%2C%20saya%20ingin%20berdiskusi%20tentang%20layanan%20fulfillment.`}
            target="_blank"
            rel="noreferrer"
            data-analytics-contact="whatsapp"
          >
            Cerita kebutuhanmu
          </a>
        </div>
      </section>

      <footer>
        <div className="footer-main">
          <img src={logo} alt="Anagata Warehouse" />
          <div>
            <span>Say hello</span>
            <a href={`mailto:${email}`} data-analytics-contact="email">{email}</a>
            <a href={`tel:+${whatsapp}`}>+{whatsapp}</a>
          </div>
          <div>
            <span>Follow our journey</span>
            <a href={`https://instagram.com/${instagram}`} data-analytics-contact="instagram">Instagram</a>
            <a href={`https://wa.me/${whatsapp}`} data-analytics-contact="whatsapp">WhatsApp</a>
          </div>
        </div>
        <div className="footer-bottom">
          <span>© 2026 Anagata Warehouse</span>
          <span>Fulfillment partner for Indonesia’s growing brands.</span>
          <a href="#home">Back to top ↑</a>
        </div>
        <p className="analytics-disclosure">Statistik penggunaan anonim membantu kami memahami bagian yang dilihat. Alamat IP mentah tidak disimpan.</p>
      </footer>
      </div>
    </main>
  )
}
