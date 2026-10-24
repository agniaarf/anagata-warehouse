<button type="button" class="admin-sidebar-toggle" id="sidebarToggle" aria-label="Buka menu">Menu</button>

<aside class="admin-sidebar" id="adminSidebar">
  <a href="{{ route('admin.website.homepage') }}" class="admin-sidebar-brand">
    <x-application-logo />
  </a>

  <nav class="admin-sidebar-nav">
    <a href="{{ route('admin.website.homepage') }}" class="admin-side-link {{ request()->routeIs('admin.website.*') ? 'active' : '' }}">
      Halaman utama
    </a>

    <a href="{{ route('admin.content.index') }}" class="admin-side-link {{ request()->routeIs('admin.content.*') ? 'active' : '' }}">
      Konten
    </a>

    <a href="{{ route('admin.analytics') }}" class="admin-side-link {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
      Analitik
    </a>

    <a href="{{ route('admin.settings.show', 'branding') }}" class="admin-side-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
      Pengaturan
    </a>
  </nav>

  <div class="admin-sidebar-foot">
    <a href="{{ route('home') }}" class="admin-side-link" target="_blank" rel="noopener">Lihat situs</a>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="admin-side-link admin-side-link--logout">Keluar</button>
    </form>
  </div>
</aside>
