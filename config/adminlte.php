<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'TIVATECH',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>TIVATECH</b>CO,LTD',
    'logo_img' => '/logo-hv.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'TIVATECH Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => '/logo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'logo-hv.png',
            'alt' => 'Tivatech',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'nav-icon fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => true,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */
    'menu' => [
        // Mục Dashboard, hiển thị cho tất cả người dùng có permission 'view dashboard'
        [
            'text' => 'Dashboard',
            'route'  => 'dashboard',
            'icon' => 'nav-icon fas fa-tachometer-alt',
        ],
        // QUẢNG LÝ CHẤM CÔNG
        ['header' => 'QUẢN LÝ CHẤM CÔNG'],

        // Mục cho  DS Chấm Công theo ngày
        [
            'text' => 'DANH SÁCH THEO NGÀY',
            'route'  => 'attendance.showlist',
            'icon' => 'nav-icon fas fa-calendar-check',
            'can'  => 'manager attendance',
        ],
        // Mục cho  công nhân viên
        [
            'text' => 'DANH SÁCH NHÂN VIÊN',
            'route'  => 'attendance.listByEmployeeAndMonth',
            'icon' => 'nav-icon fas fa-calendar-check',
            'can'  => 'manager attendance',
        ],
        // Mục cho Chấm Công
        [
            'text' => 'CHẤM CÔNG',
            'route'  => 'attendance.index',
            'icon' => 'nav-icon fas fa-calendar-check',
        ],
        // kỹ thuật google ads
        ['header' => 'TECH GG ADS', 'can'  => 'google ads'],
        // Mục cho ngân sách
        [
            'text' => 'Vượt Ngân Sách',
            'route'  => 'intradayPerformance',
            'icon' => 'nav-icon fas fa-chart-line',
            'can'  => 'google ads',
        ],
        // Mục cho quản lý chiến dịch
        [
            'text' => 'Quản Lý Campaign',
            'route'  => 'campaigns',
            'icon' => 'nav-icon fas fa-ad',
            'can'  => 'google ads',
        ],
        [
            'text' => 'Hiệu Suất',
            'route'  => 'datePerformance',
            'icon' => 'nav-icon fas fa-ad',
            'can'  => 'google ads',
        ],
        // Phân cách
        ['header' => 'GOOGLE ADS'],

        // Mục cho Saler
        [
            'text' => 'Yêu Cầu Báo Giá',
            'route'  => 'quote-requests.index',
            'icon' => 'nav-icon fas fa-file-alt',
            'can'  => 'manage own quote requests',
        ],
        [
            'text' => 'Tất Cả Yêu Cầu Báo Giá',
            'route'  => 'quote-requests.all',
            'icon' => 'nav-icon fas fa-file-invoice',
            'can'  => 'manage all quote requests',
        ],
        [
            'text' => 'Từ khoá',
            'route'  => 'campaigns.search',
            'icon' => 'nav-icon fas fa-file-invoice',
            'can'  => 'manage all quote requests',
        ],
        [
            'text'    => 'DATA',
            'icon'    => 'nav-icon fas fa-database',
            'submenu' => [
                // Mục cho Saler check domain
                [
                    'text' => 'Kiểm tra website',
                    'route'  => 'websites.check',
                    'icon' => 'nav-icon fas fa-file-word',
                    'can'  => 'manage own quote requests',
                ],
                // Mục cho Saler khách hàng
                [
                    'text' => 'Khách hàng',
                    'route'  => 'dataCustomers.index',
                    'icon' => 'nav-icon fas fa-file-word',
                    'can'  => 'manage own quote requests',
                ],
                // Mục cho Saler
                [
                    'text' => 'Data theo từ khoá',
                    'route'  => 'serpapi.form',
                    'icon' => 'nav-icon fas fa-file-word',
                    'can'  => 'manage own quote requests',
                ],

                // Mục cho Saler
                [
                    'text' => 'Data 3 Tháng',
                    'route'  => 'websites.inactive-campaigns',
                    'icon' => 'nav-icon fas fa-dice-six',
                    'can'  => 'manage own quote requests',
                ],
            ],
        ],

        // QUẢNG LÝ Quảng cáo
        [
            'text'    => 'Quản Lý Quảng Cáo',
            'icon'    => 'nav-icon fab fa-google',
            'submenu' => [
                [
                    'text' => 'Website ADS',
                    'url'  => 'websites',
                    'icon' => 'nav-icon fas fa-user-plus',
                    'can'  => 'manage own quote requests',

                ],
                [
                    'text' => 'Setup ADS',
                    'url'  => '/campaigns/setups',
                    'icon' => 'nav-icon fas fa-cogs',
                ],
                [
                    'text' => 'Chiến Dịch Hoạt Động',
                    'url'  => 'campaigns',
                    'icon' => 'nav-icon fas fa-ad',
                    'can'  => 'manage own quote requests',

                ],
                [
                    'text' => 'Chiến Dịch Tính Lương',
                    'url'  => '/campaigns/monthly-sales',
                    'icon' => 'nav-icon fas fa-ad',
                    'can'  => 'manage own quote requests',

                ],
            ],
        ],
        [
            'text' => 'Quản lý Từ Khoá',
            'url'  => 'keywords',
            'icon' => 'fas fa-key',
            'can' => 'manage users', // Chỉ hiển thị cho role:admin
        ],
        ['header' => 'QUẢN LÝ THIẾT KẾ WEBSITE',
        'can' => 'designer|admin', // Chỉ hiển thị cho role:admin

        ],

        [
            'text' => 'Quản lý Website',
            'url'  => 'design-websites',
            'icon' => 'fas fa-globe',
            'can' => 'designer|admin', // Chỉ hiển thị cho role:admin

        ],
        // Phân cách
        [
            'header' => 'LƯƠNG NHÂN VIÊN',
        ],
        // Mục Quản Lý Người Dùng, chỉ hiển thị cho những người có permission 'manage users'
        [
            'text' => 'TÍNH BẢNG LƯƠNG',
            'route'  => 'salaries.show.calculate',
            'icon' => 'nav-icon fas fa-users',
            'can' => 'manage users'

        ],
        [
            'text' => 'BẢNG LƯƠNG',
            'route'  => 'salaries.index',
            'icon' => 'nav-icon fas fa-users',
        ],
        // Phân cách
        [
            'header' => 'QUẢN LÝ PHÂN QUYỀN',
            'can' => 'manage users'
        ],

        // Mục Quản Lý Người Dùng, chỉ hiển thị cho những người có permission 'manage users'
        [
            'text' => 'Quản Lý Người Dùng',
            'route'  => 'admin.users.index',
            'icon' => 'nav-icon fas fa-users',
            'can' => 'manage users',
        ],

        // Mục Quản Lý Roles, chỉ hiển thị cho những người có permission 'manage roles'
        [
            'text' => 'Quản Lý Roles',
            'route'  => 'admin.roles.index',
            'icon' => 'nav-icon fas fa-user-tag',
            'can' => 'manage roles',
        ],

        // Mục Quản Lý Permissions, chỉ hiển thị cho những người có permission 'manage permissions'
        [
            'text' => 'Quản Lý Permissions',
            'route'  => 'admin.permissions.index',
            'icon' => 'nav-icon fas fa-lock',
            'can' => 'manage permissions',
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
        'icheck' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'https://cdn.jsdelivr.net/npm/icheck-bootstrap@3.0.1/icheck-bootstrap.min.css',
                ],
            ],
        ],
        'Toastr' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/toastr/toastr.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/toastr/toastr.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
