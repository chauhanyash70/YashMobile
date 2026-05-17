<div class="startbar d-print-none">
    <div class="brand">
        <a href="{{ route('home') }}" class="logo">
            <span class="logo-sm">
                <img src="{{ asset('assets/logo/yash-mobile-logo-small-white.png') }}" alt="logo-small" class="logo-light"
                    height="28">
                <img src="{{ asset('assets/logo/yash-mobile-logo-small.png') }}" alt="logo-small" class="logo-dark"
                    height="28">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/logo/yash-mobile-logo-white.png') }}" alt="logo-large" class="logo-light"
                    width="150">
                <img src="{{ asset('assets/logo/yash-mobile-logo.png') }}" alt="logo-large" class="logo-dark"
                    width="150">
            </span>
        </a>
    </div>
    <div class="startbar-menu">
        <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
            <div class="d-flex align-items-start flex-column w-100">
                <ul class="navbar-nav mb-auto w-100">
                    <li class="menu-label pt-0 mt-0">
                        <span>Main Menu</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="iconoir-home-simple menu-icon"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.invoice.*') ? 'active' : '' }}"
                            href="{{ route('invoice.index') }}">
                            <i class="iconoir-page-star menu-icon"></i>
                            <span>Invoice</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('mobiles.index') ? 'active' : '' }}"
                            href="{{ route('mobiles.index') }}">
                            <i class="iconoir-smartphone-device menu-icon"></i>
                            <span>All Mobiles</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('mobiles.available') ? 'active' : '' }}"
                            href="{{ route('mobiles.available') }}">
                            <i class="iconoir-smartphone-device menu-icon"></i>
                            <span>Available Mobiles</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('accessories.*') ? 'active' : '' }}"
                            href="{{ route('accessories.index') }}">
                            <i class="iconoir-headset menu-icon"></i>
                            <span>Accessories</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('repairs.*') ? 'active' : '' }}"
                            href="{{ route('repairs.index') }}">
                            <i class="iconoir-tools menu-icon"></i>
                            <span>Repairs</span>
                        </a>
                    </li>
                    {{-- <li class="nav-item">
						<a class="nav-link {{ Route::is('expenses.*') ? 'active' : '' }}"
							href="{{ route('expenses.index') }}">
							<i class="iconoir-coins menu-icon"></i>
							<span>Expenses</span>
						</a>
					</li> --}}
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('customers.*') ? 'active' : '' }}"
                            href="{{ route('customers.index') }}">
                            <i class="iconoir-group menu-icon"></i>
                            <span>Customers</span>
                        </a>
                    </li>

                    {{-- <li class="nav-item">
						<a class="nav-link {{ Route::is('transactions.*') ? 'active' : '' }}"
							href="{{ route('transactions.index') }}">
							<i class="iconoir-list menu-icon"></i>
							<span>Transactions</span>
						</a>
					</li> --}}


                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('brands.*') ? 'active' : '' }}"
                            href="{{ route('brands.index') }}">
                            <i class="iconoir-shop-four-tiles menu-icon"></i>
                            <span>Brands</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.upload.*') ? 'active' : '' }}"
                            href="{{ route('upload.index') }}">
                            <i class="iconoir-upload menu-icon"></i>
                            <span>Upload</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="startbar-overlay d-print-none"></div>
