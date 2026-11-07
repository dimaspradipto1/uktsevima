  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="{{ route('dashboard') }}" class="logo d-flex align-items-center">
        <img src="{{ asset('assets/img/logo.png') }}" alt="">
        <span class="d-none d-lg-block">uktsevima</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div><!-- End Search Bar -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon-->

        @php
          $notifications = $headerNotifications ?? [];
          $notifCount = count($notifications);
        @endphp

        <!-- Notification Nav -->
        <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown" title="Notifikasi Sistem">
            <i class="bi bi-bell"></i>
            @if($notifCount > 0)
              <span class="badge bg-primary badge-number">{{ $notifCount }}</span>
            @endif
          </a><!-- End Notification Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications" style="min-width: 320px; max-width: 360px;">
            <li class="dropdown-header">
              @if($notifCount > 0)
                Ada {{ $notifCount }} notifikasi baru
              @else
                Tidak ada notifikasi baru
              @endif
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            @forelse($notifications as $notif)
              <li class="notification-item">
                <a href="{{ $notif['link'] ?? '#' }}" class="d-flex align-items-start text-decoration-none text-dark p-2">
                  <i class="bi {{ $notif['icon'] ?? 'bi-info-circle text-primary' }} fs-4 me-3 mt-1"></i>
                  <div>
                    <h4 class="fs-6 fw-bold mb-1 text-dark">{{ $notif['title'] }}</h4>
                    <p class="small text-muted mb-1">{{ $notif['message'] }}</p>
                    <p class="small text-secondary mb-0 font-monospace"><i class="bi bi-clock me-1"></i>{{ $notif['time'] }}</p>
                  </div>
                </a>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
            @empty
              <li class="notification-item text-center py-3">
                <div class="text-muted small">
                  <i class="bi bi-check2-all text-success fs-3 d-block mb-1"></i>
                  Semua transaksi dan aktivitas UKT terpantau normal.
                </div>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
            @endforelse

            <li class="dropdown-footer">
              @if(auth()->user() && auth()->user()->role === 'mahasiswa')
                <a href="{{ route('tagihan-ukts.index') }}">Lihat Tagihan & Histori Transaksi</a>
              @elseif(auth()->user() && auth()->user()->institusi_id)
                <a href="{{ route('transaksi-pembayarans.index') }}">Lihat Seluruh Transaksi Kampus</a>
              @else
                <a href="{{ route('transaksi-pembayarans.index') }}">Monitoring Transaksi Global</a>
              @endif
            </li>

          </ul><!-- End Notification Dropdown Items -->

        </li><!-- End Notification Nav -->

        <!-- User Profile Nav -->
        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="{{ asset('assets/img/profile-img.jpg') }}" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2">{{ Auth::user()->name ?? 'User' }}</span>
          </a><!-- End Profile Image Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6>{{ Auth::user()->name ?? 'User' }}</h6>
              <span>{{ Auth::user()->email ?? '' }}</span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="{{ route('dashboard') }}">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            @if(Auth::user())
              <li>
                <a class="dropdown-item d-flex align-items-center" href="{{ route('users.password.edit', Auth::id()) }}">
                  <i class="bi bi-key"></i>
                  <span>Ganti Password</span>
                </a>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
            @endif

            <li>
              <a class="dropdown-item d-flex align-items-center" href="{{ route('logout') }}">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->
