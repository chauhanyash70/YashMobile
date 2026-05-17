<div class="startbar d-print-none">
    <div class="brand">
        <a href="{{ route('home') }}" class="logo">
            <span class="">
                <img src="{{ asset('assets/logo/yash-mobile-logo-small.png') }}" alt="logo-small" class="logo-sm"
                    style="display: none;">
            </span>
            <span class="">
                <img src="{{ asset('assets/logo/yash-mobile-logo.png') }}" alt="logo-large" class="logo-lg logo-light"
                    width="150">
                <img src="{{ asset('assets/logo/yash-mobile-logo.png') }}" alt="logo-large" class="logo-lg logo-dark"
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
                        <a class="nav-link {{ Route::is('products.*') ? 'active' : '' }}"
                            href="{{ route('mobiles.index') }}">
                            <i class="iconoir-smartphone-device menu-icon"></i>
                            <span>Mobiles</span>
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
                        <a class="nav-link {{ Route::is('customers.*') ? 'active' : '' }}"
                            href="{{ route('customers.index') }}">
                            <i class="iconoir-group menu-icon"></i>
                            <span>Customers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('suppliers.*') ? 'active' : '' }}"
                            href="{{ route('suppliers.index') }}">
                            <i class="iconoir-truck  menu-icon"></i>
                            <span>Suppliers</span>
                        </a>
                    </li>
                    {{-- <li class="nav-item">
                        <a class="nav-link {{ Route::is('purchases.*') ? 'active' : '' }}"
                            href="{{ route('purchases.index') }}">
                            <i class="iconoir-delivery-truck menu-icon"></i>
                            <span>Purchases</span>
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
