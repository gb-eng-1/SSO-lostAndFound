@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')

  <div class="dashboard-header-row notif-page-header-row">
    <h1 class="page-title">Notifications</h1>
  </div>

  @if(session('success'))
    <div class="alert-success" style="background:#d1fae5;color:#065f46;padding:10px 16px;border-radius:6px;margin-bottom:12px;">
      {{ session('success') }}
    </div>
  @endif

  @include('partials.notifications-page-body', ['role' => 'admin', 'unreadCount' => $unreadCount ?? 0])

  <div style="margin-top:16px;">
    {{ $notifications->links() }}
  </div>

@endsection
