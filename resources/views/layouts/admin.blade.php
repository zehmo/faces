<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Faces') — Faculty of Computing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --faces-green: #2e7d32;
            --faces-green-dark: #1b5e20;
            --faces-green-light: #4caf50;
            --faces-green-pale: #e8f5e9;
        }
        body { background: #f5f5f5; }
        .navbar { background: var(--faces-green-dark) !important; }
        .navbar .navbar-brand, .navbar .nav-link, .navbar .text-white,
        .navbar .navbar-brand strong, .navbar .btn-outline-light { color: #fff !important; }
        .btn-primary, .btn-success {
            background: var(--faces-green); border-color: var(--faces-green);
        }
        .btn-primary:hover, .btn-success:hover {
            background: var(--faces-green-dark); border-color: var(--faces-green-dark);
        }
        .badge-paid { background: var(--faces-green); color: #fff; }
        .badge-unpaid { background: #dc3545; color: #fff; }
        .sidebar { min-height: calc(100vh - 56px); background: #fff; border-right: 1px solid #dee2e6; padding-top: .5rem; }
        .sidebar .nav-link { color: #333 !important; padding: .5rem .75rem; font-weight: 500; font-size: .9rem; display: flex; align-items: center; gap: .5rem; margin-bottom: .15rem; }
        .sidebar .nav-link:hover { background: #c8e6c9; color: #1b5e20 !important; }
        .sidebar .nav-link.active { background: var(--faces-green); color: #fff !important; border-radius: 4px; margin-left: .25rem; margin-right: .25rem; }
        .sidebar .nav-link i { width: 20px; text-align: center; font-size: .95rem; }
        .offcanvas .nav-link { color: #333 !important; padding: .6rem 1rem; font-weight: 500; }
        .offcanvas .nav-link:hover { background: #c8e6c9; color: #1b5e20 !important; }
        .offcanvas .nav-link.active { background: var(--faces-green); color: #fff !important; border-radius: 4px; margin: 0 .5rem; }
        .offcanvas .nav-link i { width: 24px; }
        .card-stat { border-left: 4px solid var(--faces-green); }
        .table th { font-weight: 600; font-size: .875rem; }
        .photo-thumb { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; }
        @media (max-width: 767.98px) {
            main { padding: 1rem !important; }
            .table { font-size: .85rem; }
            h4 { font-size: 1.15rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <button class="btn btn-outline-light btn-sm me-2 d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
                <i class="bi bi-list fs-5"></i>
            </button>
            <a class="navbar-brand d-flex align-items-center" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('img/logo.png') }}" alt="Logo" height="36" class="me-2" onerror="this.style.display='none'">
                <strong>FACES</strong>
                <span class="ms-2 d-none d-md-inline" style="font-size:.8rem;opacity:.85;">Faculty of Computing</span>
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3 d-none d-sm-inline">{{ Auth::user()->full_name ?? '' }}</span>
                <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Offcanvas sidebar for mobile --}}
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" style="width:260px;">
        <div class="offcanvas-header" style="background:var(--faces-green-dark);">
            <h5 class="offcanvas-title text-white"><i class="bi bi-grid"></i> Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <ul class="nav flex-column py-2">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" href="{{ route('admin.students.index') }}">
                        <i class="bi bi-people"></i> Students
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}" href="{{ route('admin.departments.index') }}">
                        <i class="bi bi-building"></i> Departments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}" href="{{ route('admin.sessions.index') }}">
                        <i class="bi bi-calendar3"></i> Sessions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.officers.*') ? 'active' : '' }}" href="{{ route('admin.officers.index') }}">
                        <i class="bi bi-person-badge"></i> Officers
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 sidebar py-3 d-none d-md-block">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" href="{{ route('admin.students.index') }}">
                            <i class="bi bi-people"></i> Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}" href="{{ route('admin.departments.index') }}">
                            <i class="bi bi-building"></i> Departments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}" href="{{ route('admin.sessions.index') }}">
                            <i class="bi bi-calendar3"></i> Sessions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.officers.*') ? 'active' : '' }}" href="{{ route('admin.officers.index') }}">
                            <i class="bi bi-person-badge"></i> Officers
                        </a>
                    </li>
                </ul>
            </nav>

            <main class="col-md-10 py-4 px-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
