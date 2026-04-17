@php
  $isAdmin = ($role ?? 'admin') === 'admin';
  $listUrl = $isAdmin ? route('admin.notifications') : route('student.notifications');
  $mapLink = $isAdmin
    ? [
        'item_encoded' => route('admin.found'),
        'item_claimed' => route('admin.history'),
        'claim_submitted' => route('admin.matched'),
        'item_matched' => route('admin.matched'),
        'lost_report_submitted' => route('admin.reports'),
      ]
    : [
        'item_matched' => route('student.dashboard'),
        'claim_approved' => route('student.claim-history'),
        'claim_rejected' => route('student.claim-history'),
      ];
@endphp

<div class="notif-page-toolbar">
  <div class="notif-page-toolbar-inner">
    <p class="notif-page-sub">
      @if(($unreadCount ?? 0) > 0)
        <span class="notif-page-unread-pill">{{ $unreadCount }} unread</span>
      @else
        <span class="notif-page-all-read">All caught up</span>
      @endif
    </p>
    @if(($unreadCount ?? 0) > 0)
      <form method="post" action="{{ $isAdmin ? route('admin.notifications.read-all') : route('student.notifications.read-all') }}" class="notif-page-markall-form">
        @csrf
        <button type="submit" class="notif-mark-all-btn notif-mark-all-btn--solid">Mark all as read</button>
      </form>
    @endif
  </div>
</div>

<div class="notif-panel-scroll notif-page-scroll">
  @forelse($notifications as $notif)
    @php
      $unread = !$notif->is_read;
      $thumb = null;
      if ($isAdmin && $notif->related_id) {
        $img = \App\Models\Item::query()->where('id', $notif->related_id)->value('image_data');
        if (\App\Support\ItemImageDisplay::canUseAsBellThumbnail($img)) {
          $thumb = $img;
        }
      }
      $viewHref = $mapLink[$notif->type] ?? $listUrl;
      $relatedId = $notif->related_id;
    @endphp
    <div class="notif-card notif-page-card {{ $unread ? 'notif-card--unread' : 'notif-card--read' }}">
      <div class="notif-item-thumb">
        @if($thumb)
          <img class="notif-thumb-img" src="{{ $thumb }}" alt="">
        @else
          <div class="notif-thumb-placeholder"><i class="fa-regular fa-image"></i></div>
        @endif
      </div>
      <div class="notif-item-body">
        <div class="notif-item-top">
          <span class="notif-item-title">{{ $notif->title }}</span>
          @if($unread)
            <span class="notif-item-new-badge">New</span>
          @endif
          <span class="notif-item-time">{{ $notif->created_at?->diffForHumans() }}</span>
        </div>
        <div class="notif-item-message">
          {{ $notif->message }}
          @if($relatedId)
            <a href="#" class="notif-view-link notif-view-link--modal" data-related-id="{{ $relatedId }}">View Details</a>
          @else
            <a href="{{ $viewHref }}" class="notif-view-link">View Details</a>
          @endif
        </div>
        <div class="notif-page-card-meta">
          <form method="post" action="{{ url(($isAdmin ? '/admin' : '/student') . '/notifications/' . $notif->id) }}" class="notif-delete-form" data-notif-delete="1">
            @csrf
            @method('DELETE')
            <button type="submit" class="notif-delete-link">Delete</button>
          </form>
        </div>
      </div>
      @if($unread)
        <form method="post" action="{{ $isAdmin ? route('admin.notifications.read', $notif->id) : route('student.notifications.read', $notif->id) }}" class="notif-mark-read-form">
          @csrf
          <button type="submit" class="notif-mark-read-btn" title="Mark as read" aria-label="Mark as read">
            <i class="fa-solid fa-check"></i>
          </button>
        </form>
      @else
        <span class="notif-mark-read-done" aria-hidden="true"><i class="fa-solid fa-check" style="opacity:.35;"></i></span>
      @endif
    </div>
  @empty
    <p class="notif-empty">No notifications yet.</p>
  @endforelse
</div>

<script>
(function(){
  document.querySelectorAll('form.notif-delete-form[data-notif-delete]').forEach(function(form){
    form.addEventListener('submit', function(e){
      e.preventDefault();
      if (typeof window.appUiConfirm === 'function') {
        window.appUiConfirm('Delete this notification?', {
          onConfirm: function(){ form.removeAttribute('data-notif-delete'); form.submit(); }
        });
      } else if (window.confirm('Delete this notification?')) {
        form.submit();
      }
    });
  });
})();
(function(){
  var isAdminPage = @json($isAdmin);
  document.addEventListener('click', function(ev) {
    var a = ev.target.closest('a.notif-view-link--modal[data-related-id]');
    if (!a) return;
    ev.preventDefault();
    var rid = a.getAttribute('data-related-id');
    if (!rid) return;
    if (isAdminPage) {
      if (typeof window.showItemDetailsModal === 'function') window.showItemDetailsModal(rid);
      return;
    }
    if (typeof window.openStudentItemFromSearch === 'function') window.openStudentItemFromSearch(rid);
  });
})();
</script>
