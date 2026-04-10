<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Admin') — UB Lost and Found</title>

  {{-- Poppins font --}}
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  {{-- Core admin CSS (served from the original LOSTANDFOUND/ADMIN/ folder) --}}
  <link rel="stylesheet" href="{{ asset('ADMIN/AdminDashboard.css') }}?v=11">
  <link rel="stylesheet" href="{{ asset('assets/modal-tokens.css') }}?v=1">
  <link rel="stylesheet" href="{{ asset('ADMIN/FoundAdmin.css') }}?v=4">
  <link rel="stylesheet" href="{{ asset('ADMIN/ItemMatchedAdmin.css') }}?v=3">
  <link rel="stylesheet" href="{{ asset('ADMIN/NotificationsDropdown.css') }}?v=5">
  <link rel="stylesheet" href="{{ asset('assets/photo-picker.css') }}?v=2">
  <link rel="stylesheet" href="{{ asset('assets/app-ui-modals.css') }}?v=1">

  {{-- CSRF meta tag for JS fetch calls --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  @stack('styles')

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/js/all.min.js"></script>
</head>
<body>
<div class="layout">

  {{-- ── Sidebar ──────────────────────────────────────────────────────────── --}}
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo"></div>
      <div class="sidebar-title">
        <span class="sidebar-title-line1">University of</span>
        <span class="sidebar-title-line2">Batangas</span>
      </div>
    </div>
    <nav>
      <ul class="nav-menu">
        <li>
          <a class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
             href="{{ route('admin.dashboard') }}">
            <div class="nav-item-icon"><i class="fa-solid fa-house"></i></div>
            <div class="nav-item-label">Dashboard</div>
          </a>
        </li>
        <li>
          <a class="nav-item {{ request()->routeIs('admin.found*') ? 'active' : '' }}"
             href="{{ route('admin.found') }}">
            <div class="nav-item-icon"><i class="fa-solid fa-folder"></i></div>
            <div class="nav-item-label">Found</div>
          </a>
        </li>
        <li>
          <a class="nav-item {{ request()->routeIs('admin.reports*') ? 'active' : '' }}"
             href="{{ route('admin.reports') }}">
            <div class="nav-item-icon"><i class="fa-regular fa-file-lines"></i></div>
            <div class="nav-item-label">Reports</div>
          </a>
        </li>
        <li>
          <a class="nav-item {{ request()->routeIs('admin.matched*') ? 'active' : '' }}"
             href="{{ route('admin.matched') }}">
            <div class="nav-item-icon"><i class="fa-regular fa-circle-check"></i></div>
            <div class="nav-item-label">Matching</div>
          </a>
        </li>
        <li>
          <a class="nav-item {{ request()->routeIs('admin.history*') ? 'active' : '' }}"
             href="{{ route('admin.history') }}">
            <div class="nav-item-icon"><i class="fa-regular fa-calendar"></i></div>
            <div class="nav-item-label">History</div>
          </a>
        </li>
      </ul>
    </nav>
  </aside>

  {{-- ── Main content ─────────────────────────────────────────────────────── --}}
  <main class="main">

    {{-- Topbar --}}
    <div class="topbar topbar-maroon">
      @include('partials.topbar-global-search', ['searchRole' => 'admin'])
      <div class="topbar-spacer" aria-hidden="true"></div>
      <div class="topbar-right">
        @include('partials.notification-bell-dropdown', ['role' => 'admin'])

        {{-- Admin dropdown --}}
        <div class="admin-dropdown" id="adminDropdown">
          <button type="button" class="admin-link admin-dropdown-trigger" aria-expanded="false">
            <i class="fa-regular fa-user"></i>
            <span class="admin-name">{{ session('admin_name', 'Admin') }}</span>
            <i class="fa-solid fa-chevron-down" style="font-size:11px;"></i>
          </button>
          <div class="admin-dropdown-menu" role="menu">
            <form method="POST" action="{{ route('admin.logout') }}">
              @csrf
              <button type="submit" role="menuitem" class="admin-dropdown-item">
                <i class="fa-solid fa-right-from-bracket"></i> Log Out
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="main-content-wrap">
      @yield('content')
    </div>

  </main>{{-- /main --}}
</div>{{-- /layout --}}

@include('partials.app-ui-modals')
<script src="{{ asset('assets/app-ui-modals.js') }}?v=1"></script>
@include('partials.admin-encode-review-modal')
{{-- Shared JS --}}
<script src="{{ asset('assets/photo-picker.js') }}?v=3"></script>
<script>
  // Admin dropdown: .open class + stopPropagation (matches AdminDashboard.css)
  (function () {
    var dropdown = document.getElementById('adminDropdown');
    var trigger = dropdown && dropdown.querySelector('.admin-dropdown-trigger');
    if (!dropdown || !trigger) return;
    trigger.addEventListener('click', function (e) {
      e.stopPropagation();
      dropdown.classList.toggle('open');
      trigger.setAttribute('aria-expanded', dropdown.classList.contains('open'));
    });
    document.addEventListener('click', function (e) {
      if (!dropdown.contains(e.target)) {
        dropdown.classList.remove('open');
        trigger.setAttribute('aria-expanded', 'false');
      }
    });
  })();
</script>

@stack('scripts')

@include('partials.admin-item-details-modal-script')
<script src="{{ asset('assets/topbar-search.js') }}?v=2"></script>

</body>
</html>
