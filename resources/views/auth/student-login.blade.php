<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Login — UB Lost and Found</title>
  <link rel="stylesheet" href="{{ asset('ADMIN/login.css') }}?v=2">
  <style>
    body {
      background-color: #5a0000;
      background-image: linear-gradient(rgba(139,0,0,.70), rgba(139,0,0,.70)), url('{{ asset('ADMIN/UBBG.jpg') }}');
      background-size: cover; background-position: center;
      background-repeat: no-repeat; background-attachment: fixed;
    }
    .login-error-popup {
      position: fixed; top: 24px; left: 50%; transform: translateX(-50%);
      padding: 12px 24px; background: #b91c1c; color: #fff;
      font-size: 14px; font-weight: 500; border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,.25); z-index: 9999;
      transition: opacity .4s ease, visibility .4s ease;
    }
    .login-error-popup.hide { opacity: 0; visibility: hidden; }
  </style>
</head>
<body>

  @if(session('error'))
    <p id="loginErrorPopup" class="login-error-popup" role="alert">{{ session('error') }}</p>
  @endif

  <div class="login-wrapper">
    <div class="login-box">
      <h1 class="login-title">Welcome to UB Lost and Found!</h1>

      <form class="login-form" method="POST" action="{{ route('student.login') }}">
        @csrf

        <label for="email">Email</label>
        <input type="email" id="email" name="email"
               value="{{ old('email') }}" required autocomplete="email">

        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               required autocomplete="current-password">

        @error('email')
          <p class="field-error" style="color:#b91c1c;font-size:13px;margin-top:4px;">{{ $message }}</p>
        @enderror

        <div class="login-options">
          <label class="remember-me">
            <input type="checkbox" name="remember" value="1">
            <span>Remember me</span>
          </label>
          <a class="forgot-link" href="#">Forgot Password?</a>
        </div>

        <button type="submit" class="login-btn">Login</button>
      </form>

      <button type="button" class="ubmail-btn" disabled title="UBmail login coming soon">
        <span class="ubmail-icon">
          <svg width="18" height="18" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M43.6 20.2H24v7.6h11.2C33.6 32 29.2 35 24 35c-6.1 0-11-4.9-11-11s4.9-11 11-11c2.8 0 5.3 1 7.2 2.7l5.4-5.4C33.1 7.2 28.8 5 24 5 13.5 5 5 13.5 5 24s8.5 19 19 19c9.5 0 18-7 18-19 0-1.3-.1-2.6-.4-3.8z" fill="#8b0000"/>
          </svg>
        </span>
        Login using UB Mail
      </button>

      <div style="text-align:center;margin-top:20px;font-size:13px;color:#6b7280;">
        <a href="{{ route('home') }}" style="color:#8b0000;text-decoration:underline;margin-right:15px;">← Back to Main</a>
        <a href="{{ route('admin.login') }}" style="color:#8b0000;text-decoration:underline;">Admin Login</a>
      </div>
    </div>
  </div>

  @if(session('error'))
  <script>
    (function () {
      var el = document.getElementById('loginErrorPopup');
      if (el) setTimeout(function () { el.classList.add('hide'); }, 2500);
    })();
  </script>
  @endif
</body>
</html>
