<!-- ======= Top Navigation Bar ======= -->
<nav id="topbar" class="topbar-nav fixed-top">
  <div class="container-fluid topbar-container">
    <div class="topbar-wrapper">
      <ul class="topbar-menu" id="topbar-menu">

        <!-- Dashboard Nav -->
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-grid"></i>
            <span>Dashboard</span>
          </a>
        </li><!-- End Dashboard Nav -->

        <!-- Data Master Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ request()->routeIs('institusis.*', 'mahasiswas.*', 'periode-akademiks.*') ? 'active' : '' }}" href="#" id="masterDropdown" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
            <i class="bi bi-database"></i>
            <span>Data Master</span>
            <i class="bi bi-chevron-down dropdown-chevron"></i>
          </a>
          <ul class="dropdown-menu" aria-labelledby="masterDropdown">
            <li>
              <a class="dropdown-item {{ request()->routeIs('institusis.*') ? 'active' : '' }}" href="{{ route('institusis.index') }}">
                <i class="bi bi-circle"></i>
                <span>Institusi</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item {{ request()->routeIs('mahasiswas.*') ? 'active' : '' }}" href="{{ route('mahasiswas.index') }}">
                <i class="bi bi-circle"></i>
                <span>Mahasiswa</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item {{ request()->routeIs('periode-akademiks.*') ? 'active' : '' }}" href="{{ route('periode-akademiks.index') }}">
                <i class="bi bi-circle"></i>
                <span>Periode Akademik</span>
              </a>
            </li>
          </ul>
        </li><!-- End Data Master Dropdown -->

        <!-- Tagihan & Transaksi UKT Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ request()->routeIs('tagihan-ukts.*', 'transaksi-pembayarans.*') ? 'active' : '' }}" href="#" id="uktDropdown" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
            <i class="bi bi-cash-stack"></i>
            <span>Tagihan & Pembayaran</span>
            <i class="bi bi-chevron-down dropdown-chevron"></i>
          </a>
          <ul class="dropdown-menu" aria-labelledby="uktDropdown">
            <li>
              <a class="dropdown-item {{ request()->routeIs('tagihan-ukts.*') ? 'active' : '' }}" href="{{ route('tagihan-ukts.index') }}">
                <i class="bi bi-circle"></i>
                <span>Tagihan UKT</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item {{ request()->routeIs('transaksi-pembayarans.*') ? 'active' : '' }}" href="{{ route('transaksi-pembayarans.index') }}">
                <i class="bi bi-circle"></i>
                <span>Transaksi Pembayaran</span>
              </a>
            </li>
          </ul>
        </li><!-- End Tagihan & Transaksi UKT Dropdown -->

        <!-- Log Webhook Nav -->
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('log-webhooks.*') ? 'active' : '' }}" href="{{ route('log-webhooks.index') }}">
            <i class="bi bi-journal-code"></i>
            <span>Log Webhook</span>
          </a>
        </li><!-- End Log Webhook Nav -->

        <!-- Pengguna Nav -->
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
            <i class="bi bi-people"></i>
            <span>Pengguna</span>
          </a>
        </li><!-- End Pengguna Nav -->

      </ul>
    </div>
  </div>
</nav><!-- End Top Navigation Bar -->
