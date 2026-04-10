<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UB Lost and Found — Login</title>
  <link rel="stylesheet" href="{{ asset('ADMIN/login.css') }}?v=2">
  <style>
    body {
      background-color: #5a0000;
      background-image: linear-gradient(rgba(139,0,0,.70), rgba(139,0,0,.70)), url('{{ asset('ADMIN/UBBG.jpg') }}');
      background-size: cover; background-position: center;
      background-repeat: no-repeat; background-attachment: fixed;
    }
  </style>
</head>
<body>
  <div class="login-wrapper">
    <div class="login-box">
      <h1 class="login-title">UB Lost and Found</h1>
      <p style="text-align:center;color:#6b7280;margin-bottom:24px;">Choose your portal</p>
      <div style="display:flex;gap:12px;flex-direction:column;">
        <a href="{{ route('admin.login') }}" class="login-btn" style="text-align:center;text-decoration:none;">Admin Login</a>
        <a href="{{ route('student.login') }}" class="login-btn" style="text-align:center;text-decoration:none;background:#6b0000;">Student Login</a>
      </div>
    </div>
  </div>
</body>
</html>
