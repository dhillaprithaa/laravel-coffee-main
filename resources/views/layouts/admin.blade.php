@php
  use App\Enums\RoleType;
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('static/layouts/admin.css') }}">
  @vite(['resources/js/app.js'])
  @stack('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-dark">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button">
            <i class="fas fa-bars"></i>
          </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="{{ route('admin.dashboard.index') }}" class="nav-link">
            <i class="fas fa-home me-1"></i> Dashboard
          </a>
        </li>
      </ul>
      <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
            <i class="fas fa-user-circle me-1"></i>
            {{ Auth::user()->name }}
            <span class="badge badge-warning" style="font-size:.65rem">
              {{ Auth::user()->role->label() }}
            </span>
          </a>
          <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
              <i class="fas fa-lock me-2 text-warning"></i> Ubah Password
            </a>
            @can('viewAny', \App\Models\User::class)
              <a class="dropdown-item" href="{{ route('admin.staff.index') }}">
                <i class="fas fa-users me-2 text-warning"></i> Kelola Pengguna
              </a>
            @endcan
            <div class="dropdown-divider"></div>
            <form action="{{ route('auth.logout') }}" method="POST">
              @csrf
              <button type="submit" class="dropdown-item text-danger">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
              </button>
            </form>
          </div>
        </li>
      </ul>
    </nav>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <a href="{{ route('admin.dashboard.index') }}" class="brand-link d-flex align-items-center">
        <i class="fas fa-coffee brand-image"></i>
        <span class="brand-text">{{ config('app.name') }}</span>
      </a>
      <div class="sidebar">
        <div class="user-panel mt-1 pb-3 d-flex">
          <div class="image">
            <div
              style="width:34px; height:34px; background:linear-gradient(135deg,var(--coffee-medium),var(--accent-gold)); border-radius:50%; display:flex; align-items:center; justify-content:center;">
              <i class="fas fa-user" style="color:white; font-size:.85rem;"></i>
            </div>
          </div>
          <div class="info">
            <a href="#" class="d-block">{{ Auth::user()->name }}</a>
            <small style="color:var(--coffee-light); font-size:.75rem;">{{ Auth::user()->role->label() }}</small>
          </div>
        </div>
        <div class="form-inline mt-2">
          <div class="input-group" data-widget="sidebar-search">
            <input class="form-control form-control-sidebar" type="search" placeholder="Cari menu..."
              aria-label="Search">
            <div class="input-group-append">
              <button class="btn btn-sidebar">
                <i class="fas fa-search fa-fw"></i>
              </button>
            </div>
          </div>
        </div>
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column sidebar-menu" data-widget="treeview" role="menu"
            data-accordion="false">
            <li class="nav-item">
              <a href="{{ route('admin.dashboard.index') }}"
                class="nav-link {{ request()->routeIs('admin.dashboard.index') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.orders.index') }}"
                class="nav-link {{ request()->routeIs('admin.kasir') ? 'active' : '' }}">
                <i class="nav-icon fas fa-cash-register"></i>
                <p>Kasir / POS</p>
              </a>
            </li>
            @php $antrianCount = \App\Models\Order::whereIn('status',['pending','diproses'])->count(); @endphp
            <li class="nav-item">
              <a href="{{ route('admin.orders.queue') }}"
                class="nav-link {{ request()->routeIs('admin.antrian') ? 'active' : '' }}">
                <i class="nav-icon fas fa-list-alt"></i>
                <p>
                  Antrian
                  @if ($antrianCount > 0)
                    <span class="right badge badge-warning" style="font-size:.65rem;">{{ $antrianCount }}</span>
                  @endif
                </p>
              </a>
            </li>

            <li class="nav-header" style="color:rgba(212,168,67,.6); font-size:.7rem;">MANAJEMEN</li>

            @can('viewAny', \App\Models\Table::class)
              <li class="nav-item">
                <a href="{{ route('admin.tables.index') }}"
                  class="nav-link {{ request()->routeIs('admin.tables.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-chair"></i>
                  <p>Kelola Meja</p>
                </a>
              </li>
            @endcan

            @can('viewAny', \App\Models\Menu::class)
              <li class="nav-item">
                <a href="{{ route('admin.menus.index') }}"
                  class="nav-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-utensils"></i>
                  <p>Menu Produk</p>
                </a>
              </li>
            @endcan

            <li class="nav-item">
              <a href="{{ route('admin.reports.index') }}"
                class="nav-link {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
                <i class="nav-icon fas fa-chart-line"></i>
                <p>
                  @php
                    echo match (Auth::user()->role) {
                        RoleType::PIMPINAN => 'Laporan Penjualan',
                        RoleType::STAFF => 'Laporan Transaksi',
                        default => 'Laporan Transaksi',
                    };
                  @endphp
                </p>
              </a>
            </li>

            @can('viewAny', \App\Models\User::class)
              <li class="nav-item">
                <a href="{{ route('admin.staff.index') }}"
                  class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-users"></i>
                  <p>Staff</p>
                </a>
              </li>
            @endcan
          </ul>
        </nav>
      </div>
    </aside>
    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item active">@yield('page-title', 'Dashboard')</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
      <section class="content">
        <div class="container-fluid">
          @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:10px;">
              <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
              <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
              </button>
            </div>
          @endif
          @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:10px;">
              <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
              <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
              </button>
            </div>
          @endif
          @yield('content')
        </div>
      </section>
    </div>
    <footer class="main-footer">
      <strong>
        &copy; {{ date('Y') }}<a href="#" style="color:var(--coffee-brown);">{{ config('app.name') }}</a>.
      </strong>
      Sistem POS & Self-Ordering
      <div class="float-right d-none d-sm-inline-block">
        <b>v{{ config('app.version') }}</b>
      </div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
  </div>
  <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
  <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
  @stack('scripts')
</body>

</html>
