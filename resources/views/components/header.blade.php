<div class="topbar d-print-none">
    <div class="container-xxl">
        <nav class="topbar-custom d-flex justify-content-between" id="topbar-custom">
            <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                <li>
                    <button class="nav-link mobile-menu-btn nav-icon" id="togglemenu">
                        <i class="iconoir-menu-scale"></i>
                    </button>
                </li>
                @auth
                    <li class="mx-3 welcome-text">
                        <h3 class="mb-0 fw-bold text-truncate">
                            @yield('header_title', App\Http\Traits\Traits::getGreeting() . ', ' . Auth::user()->name . '!')
                        </h3>
                        <h6 class="mb-0 fw-normal text-muted text-truncate fs-14">@yield('tagline', "Here's your overview.")
                        </h6>
                    </li>
                @endauth
            </ul>
            <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                <li class="d-none d-md-inline-block me-3">
                    <form action="{{ route('mobiles.searchGlobal') }}" method="GET" class="search-form">
                        <div class="search-input-group">
                            <i class="iconoir-search search-icon"></i>
                            <input type="text" name="hsn" class="search-input"
                                placeholder="Search by HSN Number..." required>
                        </div>
                    </form>
                </li>
                <li class="dropdown topbar-item">
                    <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false" id="light-dark-mode">
                        <i class="icofont-moon dark-mode"></i>
                        <i class="icofont-sun light-mode"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="light-dark-mode">
                        <a class="dropdown-item theme-item" href="javascript:void(0);" data-theme="light">
                            <i class="icofont-sun me-2"></i>Light
                        </a>
                        <a class="dropdown-item theme-item" href="javascript:void(0);" data-theme="dark">
                            <i class="icofont-moon me-2"></i>Dark
                        </a>
                        <a class="dropdown-item theme-item" href="javascript:void(0);" data-theme="auto">
                            <i class="iconoir-system-restart me-2"></i>Auto
                        </a>
                    </div>
                </li>
                @auth
                    <li class="dropdown topbar-item">
                        <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            <img src="{{ Auth::user()->profile_url }}" alt="" class="thumb-lg rounded-circle">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end py-0">
                            <div class="d-flex align-items-center dropdown-item py-2 bg-secondary-subtle">
                                <div class="flex-shrink-0">
                                    <img src="{{ Auth::user()->profile_url }}" alt=""
                                        class="thumb-md rounded-circle">
                                </div>
                                <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                                    <h6 class="my-0 fw-medium text-dark fs-13">{{ Auth::user()->name }}</h6>
                                    <small class="text-muted mb-0">{{ Auth::user()->email }}</small>
                                </div>
                            </div>
                            <div class="dropdown-divider mt-0"></div>
                            <small class="text-muted px-2 pb-1 d-block">Account</small>
                            <a class="dropdown-item" href="{{ route('profile') }}"><i
                                    class="las la-user fs-18 me-1 align-text-bottom"></i> Profile</a>
                            {{-- <small class="text-muted px-2 py-1 d-block">Settings</small>
							<a class="dropdown-item" href="pages-profile.html"><i
									class="las la-cog fs-18 me-1 align-text-bottom"></i>Account Settings</a>
							<a class="dropdown-item" href="pages-profile.html"><i
									class="las la-lock fs-18 me-1 align-text-bottom"></i> Security</a> --}}

                            <div class="dropdown-divider mb-0"></div>
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                                <i class="las la-power-off fs-18 me-1 align-text-bottom"></i> {{ __('Logout') }}
                            </a>
                            <form id="admin-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endauth
            </ul>
        </nav>
    </div>
</div>

<style>
    .search-input-group {
        position: relative;
        display: flex;
        align-items: center;
        width: 260px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .search-icon {
        position: absolute;
        left: 12px;
        font-size: 18px;
        color: #94a3b8;
        transition: color 0.3s ease;
        pointer-events: none;
    }

    .search-input {
        width: 100%;
        padding: 8px 12px 8px 38px;
        font-size: 13px;
        color: #1e293b;
        background-color: rgba(241, 245, 249, 0.8);
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        outline: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .search-input::placeholder {
        color: #94a3b8;
    }

    .search-input-group:hover .search-input {
        background-color: #fff;
        border-color: #cbd5e1;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .search-input:focus {
        background-color: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1), 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        width: 320px;
    }

    .search-input:focus+.search-icon,
    .search-input-group:focus-within .search-icon {
        color: #3b82f6;
    }

    /* Dark mode adjustments */
    [data-bs-theme="dark"] .search-input {
        background-color: rgba(30, 41, 59, 0.8);
        border-color: #334155;
        color: #f1f5f9;
    }

    [data-bs-theme="dark"] .search-input-group:hover .search-input {
        background-color: #1e293b;
        border-color: #475569;
    }

    [data-bs-theme="dark"] .search-input:focus {
        background-color: #1e293b;
        border-color: #60a5fa;
        box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.15);
    }
</style>
