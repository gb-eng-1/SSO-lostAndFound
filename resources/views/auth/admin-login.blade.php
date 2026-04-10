<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — UB Lost and Found</title>
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

      <form class="login-form" method="POST" action="{{ route('admin.login') }}">
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

      <div style="text-align:center;margin-top:20px;font-size:13px;color:#6b7280;">
        <a href="{{ route('home') }}" style="color:#8b0000;text-decoration:underline;margin-right:15px;">← Back to Main</a>
        <a href="{{ route('student.login') }}" style="color:#8b0000;text-decoration:underline;">Student Login</a>
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
