@php
  $role = $searchRole ?? 'admin';
  $formAction = $role === 'admin' ? route('admin.found') : route('student.reports');
  $inputName = $role === 'admin' ? 'q' : 'search';
@endphp
<div class="topbar-search-wrap topbar-search-left"
     data-global-search
     data-search-role="{{ $role }}"
     data-search-endpoint="{{ $role === 'admin' ? route('admin.search.suggestions') : route('student.search.suggestions') }}"
     data-admin-item-url="{{ route('admin.item') }}"
     data-student-item-url="{{ route('student.item') }}">
  <form class="search-form" action="{{ $formAction }}" method="get" autocomplete="off">
    <input name="{{ $inputName }}" type="text" class="search-input global-search-input" placeholder="Search" autocomplete="off">
    <button class="search-submit" type="submit" title="Search" aria-label="Search">
      <i class="fa-solid fa-magnifying-glass"></i>
    </button>
    <div class="search-dropdown global-search-dropdown" style="display:none" role="listbox"></div>
  </form>
</div>
