<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <li class="pc-item">
                    <a href="{{ route('dashboard.index') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="ti ti-dashboard"></i>
                        </span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>
                @if (Auth::user()->role === 'admin')
                    <li class="pc-item">
                        <a href="{{ route('user.index') }}" class="pc-link">
                            <span class="pc-micon">
                                <i class="ti ti-users"></i>
                            </span>
                            <span class="pc-mtext">Kelola User</span>
                        </a>
                    </li>
                    <li class="pc-item">
                        <a href="{{ route('config.index') }}" class="pc-link">
                            <span class="pc-micon">
                                <i class="ti ti-settings"></i>
                            </span>
                            <span class="pc-mtext">Sheet Config</span>
                        </a>
                    </li>
                @endif
                <li class="pc-item">
                    <a href="{{ route('monitoring.index') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="ti ti-activity"></i>
                        </span>
                        <span class="pc-mtext">Monitoring</span>
                    </a>
                </li>
                @if (Auth::user()->role === 'admin')
                    <li class="pc-item">
                        <a href="{{ route('laporan.index') }}" class="pc-link">
                            <span class="pc-micon">
                                <i class="ti ti-file-text"></i>
                            </span>
                            <span class="pc-mtext">Laporan</span>
                        </a>
                    </li>
                @endif
                <li class="pc-item">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="pc-link w-100 border-0 bg-transparent text-start">
                            <span class="pc-micon">
                                <i class="ti ti-logout"></i>
                            </span>
                            <span class="pc-mtext">Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
