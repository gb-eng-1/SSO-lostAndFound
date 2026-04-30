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
    /* Keep password field full-width when JS toggles type to text */
    .login-form input[type="text"]#password {
      width: 100%;
      padding: 12px 44px 12px 18px;
      font-size: 1rem;
      font-family: inherit;
      color: #111827;
      background: #ffffff;
      border: 1px solid #d1d5db;
      border-radius: 10px;
      margin-bottom: 0;
      outline: none;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .login-form input[type="text"]#password:focus {
      border-color: #8b0000;
      box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.15);
    }
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
        <div style="position:relative;display:block;width:100%;margin-bottom:20px;">
          <input type="password" id="password" name="password"
                 required autocomplete="current-password" style="width:100%;padding-right:44px;margin-bottom:0;">
          <button type="button" id="togglePassword"
                  style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:#6b7280;line-height:0;"
                  aria-label="Show password">
            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
          </button>
        </div>

        @error('email')
          <p class="field-error" style="color:#b91c1c;font-size:13px;margin-top:4px;">{{ $message }}</p>
        @enderror

        <div class="login-options">
          <label class="remember-me" style="margin-bottom:0;">
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

  <script>
  (function () {
    var btn   = document.getElementById('togglePassword');
    var input = document.getElementById('password');
    var icon  = document.getElementById('eyeIcon');
    var eyeOpen   = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    var eyeClosed = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
    if (btn && input && icon) {
      btn.addEventListener('click', function () {
        if (input.type === 'password') {
          input.type = 'text';
          icon.innerHTML = eyeClosed;
          btn.setAttribute('aria-label', 'Hide password');
        } else {
          input.type = 'password';
          icon.innerHTML = eyeOpen;
          btn.setAttribute('aria-label', 'Show password');
        }
      });
    }
  })();
  </script>
</body>
</html>
