<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — {{ config('app.name') }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --coffee-dark: #1a0a00;
      --coffee-brown: #4e2a04;
      --coffee-medium: #7b4a1e;
      --coffee-light: #c8a97a;
      --coffee-cream: #f5ebe0;
      --accent-gold: #d4a843;
    }

    body {
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      background: linear-gradient(135deg, var(--coffee-dark) 0%, #3d1a00 50%, var(--coffee-brown) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      position: relative;
      overflow: hidden;
    }

    body::before,
    body::after {
      content: '';
      position: fixed;
      border-radius: 50%;
      background: rgba(212, 168, 67, 0.07);
    }

    body::before {
      width: 500px;
      height: 500px;
      top: -150px;
      right: -150px;
    }

    body::after {
      width: 350px;
      height: 350px;
      bottom: -100px;
      left: -100px;
    }

    .login-card {
      background: white;
      border-radius: 24px;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
      width: 100%;
      max-width: 420px;
      overflow: hidden;
      position: relative;
      z-index: 1;
    }

    .login-header {
      background: linear-gradient(135deg, var(--coffee-dark), var(--coffee-brown));
      padding: 40px 40px 32px;
      text-align: center;
      position: relative;
    }

    .login-header::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      right: 0;
      height: 30px;
      background: white;
      border-radius: 30px 30px 0 0;
    }

    .logo-ring {
      width: 80px;
      height: 80px;
      border: 3px solid var(--accent-gold);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
      background: rgba(212, 168, 67, 0.1);
    }

    .logo-ring i {
      font-size: 2rem;
      color: var(--accent-gold);
    }

    .login-header h1 {
      color: white;
      font-size: 1.5rem;
      font-weight: 800;
      letter-spacing: 1px;
      margin-bottom: 4px;
    }

    .login-header p {
      color: rgba(245, 235, 224, 0.7);
      font-size: .85rem;
    }

    .login-body {
      padding: 32px 40px 40px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      font-size: .83rem;
      font-weight: 600;
      color: var(--coffee-brown);
      margin-bottom: 6px;
    }

    .input-wrap {
      position: relative;
    }

    .input-wrap i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--coffee-light);
      font-size: .9rem;
    }

    .form-control {
      width: 100%;
      padding: 11px 14px 11px 38px;
      border: 2px solid #e8ddd4;
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      font-size: .9rem;
      color: var(--coffee-dark);
      transition: all .2s;
      background: #faf8f5;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--coffee-medium);
      background: white;
      box-shadow: 0 0 0 4px rgba(123, 74, 30, .12);
    }

    .form-control.is-invalid {
      border-color: #dc3545;
      background: #fff5f5;
    }

    .invalid-feedback {
      color: #dc3545;
      font-size: .78rem;
      margin-top: 4px;
    }

    .btn-login {
      width: 100%;
      padding: 13px;
      background: linear-gradient(135deg, var(--coffee-brown), var(--coffee-medium));
      color: white;
      border: none;
      border-radius: 12px;
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: all .25s;
      letter-spacing: .5px;
      margin-top: 8px;
    }

    .btn-login:hover {
      background: linear-gradient(135deg, var(--coffee-dark), var(--coffee-brown));
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, .25);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    .login-footer {
      text-align: center;
      margin-top: 20px;
      font-size: .8rem;
      color: #bbb;
    }

    .login-footer a {
      color: var(--coffee-medium);
      text-decoration: none;
      font-weight: 600;
    }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .login-card {
      animation: fadeUp .5s ease;
    }
  </style>
</head>

<body>
  <div class="login-card">
    <div class="login-header">
      <div class="logo-ring">
        <i class="fas fa-coffee"></i>
      </div>
      <h1>{{ config('app.name') }}</h1>
      <p>Sistem POS & Self-Ordering</p>
    </div>
    <div class="login-body">
      <h2 style="font-size:1.2rem; font-weight:700; color:var(--coffee-dark); margin-bottom:4px;">Selamat Datang!</h2>
      <p style="font-size:.85rem; color:#888; margin-bottom:24px;">Masuk ke panel admin Anda</p>
      @if ($errors->any())
        <div
          style="background:#fff3f3; border:1px solid #ffcdd2; border-radius:10px; padding:12px 14px; margin-bottom:20px; color:#c62828; font-size:.85rem;">
          <i class="fas fa-exclamation-circle mr-1"></i>
          {{ $errors->first() }}
        </div>
      @endif
      <form action="{{ route('auth.login') }}" method="POST">
        @csrf
        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <div class="input-wrap">
            <i class="fas fa-user"></i>
            <input type="text" id="username" name="username"
              class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}" placeholder="Masukkan Username"
              value="{{ old('username') }}" autocomplete="username" autofocus>
          </div>
          @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="input-wrap">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan Password"
              autocomplete="current-password">
          </div>
        </div>
        <button type="submit" class="btn-login">
          <i class="fas fa-sign-in-alt mr-2"></i>
          Masuk
        </button>
      </form>
    </div>
  </div>
</body>

</html>
