@extends('layouts.admin')
@section('title', 'Ubah Password')
@section('page-title', 'Ubah Password')

@push('styles')
  <style>
    .pw-card {
      max-width: 480px;
      margin: 0 auto;
      background: white;
      border-radius: 20px;
      box-shadow: 0 8px 40px rgba(0, 0, 0, .10);
      overflow: hidden;
    }

    .pw-header {
      background: linear-gradient(135deg, #1a0a00 0%, #4e2a04 100%);
      padding: 28px 32px 24px;
      text-align: center;
    }

    .pw-header .icon-wrap {
      width: 64px;
      height: 64px;
      background: rgba(212, 168, 67, .15);
      border: 2px solid rgba(212, 168, 67, .35);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 14px;
    }

    .pw-header i {
      color: #d4a843;
      font-size: 1.6rem;
    }

    .pw-header h5 {
      margin: 0;
      font-weight: 800;
      color: #d4a843;
      letter-spacing: .5px;
    }

    .pw-header small {
      color: rgba(245, 235, 224, .6);
      font-size: .8rem;
    }

    .pw-body {
      padding: 28px 32px 32px;
    }

    .field-group {
      margin-bottom: 20px;
    }

    .field-label {
      font-size: .78rem;
      font-weight: 700;
      color: #7b4a1e;
      text-transform: uppercase;
      letter-spacing: .8px;
      margin-bottom: 6px;
      display: block;
    }

    .field-wrap {
      position: relative;
    }

    .field-input {
      width: 100%;
      border: 2px solid #e0d5c8;
      border-radius: 12px;
      padding: 11px 44px 11px 14px;
      font-size: .92rem;
      color: #2d1200;
      font-family: inherit;
      transition: border-color .2s, box-shadow .2s;
      outline: none;
      background: #fafaf8;
    }

    .field-input:focus {
      border-color: #7b4a1e;
      box-shadow: 0 0 0 3px rgba(123, 74, 30, .12);
      background: white;
    }

    .field-input.is-invalid {
      border-color: #dc3545;
    }

    .field-toggle {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #aaa;
      cursor: pointer;
      font-size: .95rem;
      transition: color .2s;
    }

    .field-toggle:hover {
      color: #7b4a1e;
    }

    .invalid-msg {
      color: #dc3545;
      font-size: .78rem;
      margin-top: 4px;
    }

    .pw-strength {
      margin-top: 6px;
    }

    .pw-strength-bar {
      height: 4px;
      border-radius: 4px;
      background: #f0e8df;
      overflow: hidden;
      margin-bottom: 4px;
    }

    .pw-strength-fill {
      height: 100%;
      border-radius: 4px;
      transition: width .3s, background .3s;
      width: 0%;
    }

    .pw-strength-text {
      font-size: .72rem;
      font-weight: 600;
    }

    .btn-submit {
      width: 100%;
      background: linear-gradient(135deg, #4e2a04, #7b4a1e);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 13px;
      font-size: .95rem;
      font-weight: 700;
      cursor: pointer;
      transition: all .25s;
      letter-spacing: .3px;
      margin-top: 4px;
    }

    .btn-submit:hover {
      background: linear-gradient(135deg, #1a0a00, #4e2a04);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, .2);
    }

    .alert-success-custom {
      background: #d1e7dd;
      color: #0a5436;
      border-radius: 12px;
      padding: 12px 16px;
      font-weight: 600;
      font-size: .88rem;
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 20px;
      border: 1px solid #a3cfbb;
    }
  </style>
@endpush

@section('content')
  <div class="pw-card">
    <div class="pw-header">
      <div class="icon-wrap">
        <i class="fas fa-lock"></i>
      </div>
      <h5>Ubah Password</h5>
      <small>Pastikan password baru kuat dan mudah diingat</small>
    </div>
    <div class="pw-body">
      @if (session('success'))
        <div class="alert-success-custom">
          <i class="fas fa-check-circle"></i>
          {{ session('success') }}
        </div>
      @endif
      <form action="{{ route('admin.profile.update') }}" method="POST" id="pwForm">
        @csrf
        <div class="field-group">
          <label class="field-label" for="current_password">Password Saat Ini</label>
          <div class="field-wrap">
            <input type="password" id="current_password" name="current_password"
              class="field-input {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
              placeholder="Masukkan password saat ini" autocomplete="current-password">
            <button type="button" class="field-toggle" onclick="togglePw('current_password', this)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          @error('current_password')
            <div class="invalid-msg"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</div>
          @enderror
        </div>
        <div class="field-group">
          <label class="field-label" for="new_password">Password Baru</label>
          <div class="field-wrap">
            <input type="password" id="new_password" name="new_password"
              class="field-input {{ $errors->has('new_password') ? 'is-invalid' : '' }}" placeholder="Minimal 6 karakter"
              autocomplete="new-password" oninput="checkStrength(this.value)">
            <button type="button" class="field-toggle" onclick="togglePw('new_password', this)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <div class="pw-strength">
            <div class="pw-strength-bar">
              <div class="pw-strength-fill" id="strengthFill"></div>
            </div>
            <span class="pw-strength-text" id="strengthText" style="color:#aaa;">—</span>
          </div>
          @error('new_password')
            <div class="invalid-msg"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</div>
          @enderror
        </div>
        <div class="field-group">
          <label class="field-label" for="new_password_confirmation">Konfirmasi Password Baru</label>
          <div class="field-wrap">
            <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="field-input"
              placeholder="Ulangi password baru" autocomplete="new-password">
            <button type="button" class="field-toggle" onclick="togglePw('new_password_confirmation', this)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
        <button type="submit" class="btn-submit">
          <i class="fas fa-save mr-2"></i>Simpan Password Baru
        </button>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    function togglePw(id, btn) {
      const input = document.getElementById(id);
      const icon = btn.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
      } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
      }
    }

    function checkStrength(val) {
      const fill = document.getElementById('strengthFill');
      const text = document.getElementById('strengthText');
      let score = 0;
      if (val.length >= 6) score++;
      if (val.length >= 10) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;
      const levels = [{
          pct: '0%',
          color: '#aaa',
          label: '—'
        },
        {
          pct: '25%',
          color: '#dc3545',
          label: 'Sangat Lemah'
        },
        {
          pct: '50%',
          color: '#fd7e14',
          label: 'Lemah'
        },
        {
          pct: '70%',
          color: '#ffc107',
          label: 'Cukup'
        },
        {
          pct: '85%',
          color: '#20c997',
          label: 'Kuat'
        },
        {
          pct: '100%',
          color: '#198754',
          label: 'Sangat Kuat'
        },
      ];
      const l = levels[Math.min(score, 5)];
      fill.style.width = l.pct;
      fill.style.background = l.color;
      text.textContent = l.label;
      text.style.color = l.color;
    }
  </script>
@endpush
