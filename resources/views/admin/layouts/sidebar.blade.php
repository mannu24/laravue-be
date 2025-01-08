<?php
$sideLinks = [
    [
        'name' => 'Dashboard',
        'href' => '/admin/dashboard',
        'icon' => 'bi bi-table',
    ],
    [
        'name' => 'Blogs',
        'href' => '/admin/blogs',
        'icon' => 'bi bi-table',
        'subLinks' => [
            [
                'name' => 'Add Blog',
                'href' => '/admin/blogs/create',
            ],
            [
                'name' => 'View Blog',
                'href' => '/admin/blogs',
            ],
        ],
    ],
];
?>


<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand">
        <!--begin::Brand Link-->
        <a href="./index.html" class="brand-link">
            <!--begin::Brand Image-->
            <img src="../../dist/assets/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image opacity-75 shadow" />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">AdminLTE 4</span>
            <!--end::Brand Text-->
        </a>
        <!--end::Brand Link-->
    </div>
    <!--end::Sidebar Brand-->
    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                <!--begin::Sidebar Menu Item-->
                @foreach ($sideLinks as $sideLink)
                    @php
                        $isSubLink = isset($sideLink['subLinks']) && count($sideLink['subLinks']) > 0;
                    @endphp
                    <li class="nav-item {{ Str::contains(request()->url(), $sideLink) ? 'menu-open' : '' }}">
                        <a href={{ $isSubLink ? '#' : $sideLink['href'] }} class="nav-link">
                            <i class="nav-icon {{ $sideLink['icon'] }}"></i>
                            <p>
                                {{ $sideLink['name'] }}
                                @if ($isSubLink)
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                @endif
                            </p>
                        </a>
                        @if ($isSubLink)
                            <ul class="nav nav-treeview">
                                @foreach ($sideLink['subLinks'] as $subLink)
                                    <li class="nav-item">
                                        <a href="{{ $subLink['href'] }}"
                                            class="nav-link {{ '/' . request()->path() == $subLink['href'] ? 'active' : '' }}">
                                            <i class="nav-icon bi bi-circle"></i>
                                            <p>{{ $subLink['name'] }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
            <!--end::Sidebar Menu-->
        </nav>
    </div>
    <!--end::Sidebar Wrapper-->
</aside>
