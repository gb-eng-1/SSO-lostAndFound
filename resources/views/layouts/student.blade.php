<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Student Portal') — UB Lost and Found</title>

  {{-- Poppins font --}}
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  {{-- Core CSS: base admin layout + student-specific styles --}}
  <link rel="stylesheet" href="{{ asset('ADMIN/AdminDashboard.css') }}?v=10">
  <link rel="stylesheet" href="{{ asset('assets/modal-tokens.css') }}?v=1">
  <link rel="stylesheet" href="{{ asset('STUDENT/StudentDashboard.css') }}?v=10">
  <link rel="stylesheet" href="{{ asset('STUDENT/StudentsReport.css') }}?v=8">
  <link rel="stylesheet" href="{{ asset('STUDENT/ItemDetailsModal.css') }}?v=5">
  <link rel="stylesheet" href="{{ asset('ADMIN/NotificationsDropdown.css') }}?v=5">
  <link rel="stylesheet" href="{{ asset('assets/photo-picker.css') }}?v=2">
  <link rel="stylesheet" href="{{ asset('assets/app-ui-modals.css') }}?v=1">
  <link rel="stylesheet" href="{{ asset('assets/ub-unified.css') }}?v=1">

  <meta name="csrf-token" content="{{ csrf_token() }}">

  @stack('styles')

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
          <a class="nav-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}"
             href="{{ route('student.dashboard') }}">
            <div class="nav-item-icon"><i class="fa-solid fa-house"></i></div>
            <div class="nav-item-label">Dashboard</div>
          </a>
        </li>
        <li>
          <a class="nav-item {{ request()->routeIs('student.reports*') ? 'active' : '' }}"
             href="{{ route('student.reports') }}">
            <div class="nav-item-icon"><i class="fa-regular fa-file-lines"></i></div>
            <div class="nav-item-label">My Reports</div>
          </a>
        </li>
        <li>
          <a class="nav-item {{ request()->routeIs('student.claim-history*') ? 'active' : '' }}"
             href="{{ route('student.claim-history') }}">
            <div class="nav-item-icon"><i class="fa-regular fa-clipboard"></i></div>
            <div class="nav-item-label">Claim History</div>
          </a>
        </li>
        <li>
          <a class="nav-item {{ request()->routeIs('student.help*') ? 'active' : '' }}"
             href="{{ route('student.help') }}">
            <div class="nav-item-icon"><i class="fa-regular fa-circle-question"></i></div>
            <div class="nav-item-label">Help and Support</div>
          </a>
        </li>
      </ul>
    </nav>
  </aside>

  {{-- ── Main content ─────────────────────────────────────────────────────── --}}
  <main class="main">

    {{-- Topbar --}}
    <div class="topbar topbar-maroon">
      @include('partials.topbar-global-search', ['searchRole' => 'student'])
      <div class="topbar-spacer" aria-hidden="true"></div>
      <div class="topbar-right">
        @include('partials.notification-bell-dropdown', ['role' => 'student'])

        {{-- Student dropdown --}}
        <div class="admin-dropdown" id="studentDropdown">
          <button type="button" class="admin-link admin-dropdown-trigger" aria-expanded="false">
            <i class="fa-regular fa-user"></i>
            <span class="admin-name">{{ session('student_name', 'Student') }}</span>
            <i class="fa-solid fa-chevron-down" style="font-size:11px;"></i>
          </button>
          <div class="admin-dropdown-menu" role="menu">
            <form method="POST" action="{{ route('student.logout') }}">
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

  </main>
</div>

@include('partials.student-match-compare-modals')
@include('partials.student-compare-modal-script')

@include('partials.app-ui-modals')
<script src="{{ asset('assets/app-ui-modals.js') }}?v=1"></script>
{{-- Shared JS --}}
<script src="{{ asset('assets/photo-picker.js') }}?v=4"></script>
<script>
  (function () {
    var dropdown = document.getElementById('studentDropdown');
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

@include('partials.student-global-item-modal')
<script src="{{ asset('assets/topbar-search.js') }}?v=2"></script>

</body>
</html>
