<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="Vuexy admin is super flexible, powerful, clean &amp; modern responsive bootstrap 4 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Vuexy admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="PIXINVENT">
    <title>{{ env('APP_NAME') }}&nbsp;|&nbsp;@yield('title')</title>
    <link rel="apple-touch-icon" href="{{ cAsset("app-assets/images/ico/apple-icon-120.png") }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ cAsset("favicon.png") }}">

    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/vendors/css/vendors.min.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/vendors/css/extensions/toastr.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/vendors/css/extensions/tether-theme-arrows.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/vendors/css/extensions/tether.min.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/vendors/css/extensions/shepherd-theme-default.css") }}">
    <!-- END: Vendor CSS-->

    <!-- BEGIN: Theme CSS-->
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/css/bootstrap.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/css/bootstrap-extended.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/css/colors.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/css/components.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/css/themes/dark-layout.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/css/themes/semi-dark-layout.css") }}">

    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/css/core/menu/menu-types/vertical-menu.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/css/core/colors/palette-gradient.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/css/pages/dashboard-analytics.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/css/pages/card-analytics.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/css/plugins/tour/tour.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset('app-assets/css/pages/app-user.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset('app-assets/css/plugins/extensions/toastr.css') }}">
    <!-- END: Page CSS-->

    <link rel="stylesheet" type="text/css" href="{{ cAsset('app-assets/css/custom.css') }}">

    @yield('styles')
    <style>
        .dataTables_filter {
            display:none;
        }
        [v-cloak] {display: none !important;}
    </style>
</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern semi-dark-layout 2-columns  navbar-floating footer-static  "
data-open="click" data-menu="vertical-menu-modern" data-col="2-columns" data-layout="semi-dark-layout">
<?php $user = Auth::user(); ?>
<!-- BEGIN: Header-->
<nav class="header-navbar navbar-expand-lg navbar navbar-with-menu bg-success floating-nav navbar-light navbar-shadow">
    <div class="navbar-wrapper">
        <div class="navbar-container content">
            <div class="navbar-collapse" id="navbar-mobile">
                <div class="mr-auto float-left bookmark-wrapper d-flex align-items-center">
                        <ul class="nav navbar-nav">
                            <li class="nav-item mobile-menu d-xl-none mr-auto"><a
                                    class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i
                                        class="ficon feather icon-menu"></i></a></li>
                        </ul>
                        <ul class="nav navbar-nav bookmark-icons">
                            <li class="nav-item d-none d-lg-block"><a class="nav-link" href="{{ route('setting') }}"
                                    data-toggle="tooltip" data-placement="top"
                                    title="{{ trans('ui.sidebar.setting') }}"><i
                                        class="ficon feather icon-settings"></i></a></li>
                            <li class="nav-item d-none d-lg-block"><a class="nav-link" href="{{ route('users') }}"
                                    data-toggle="tooltip" data-placement="top"
                                    title="{{ trans('ui.sidebar.users.list') }}"><i
                                        class="ficon feather icon-users"></i></a></li>
                            <li class="nav-item d-none d-lg-block"><a class="nav-link"
                                    href="{{ route('requests.kyc') }}" data-toggle="tooltip" data-placement="top"
                                    title="{{ trans('ui.sidebar.requests.identities') }}"><i
                                        class="ficon fa fa-user-md"></i></a></li>
                            <li class="nav-item d-none d-lg-block"><a class="nav-link"
                                    href="{{ route('wallets.balance') }}" data-toggle="tooltip" data-placement="top"
                                    title="{{ trans('ui.sidebar.coldwallet.balance') }}"><i
                                        class="ficon fa fa-btc"></i></a></li>
                            <li class="nav-item d-none d-lg-block"><a class="nav-link"
                                    href="{{ route('affiliate.settle') }}" data-toggle="tooltip" data-placement="top"
                                    title="{{ trans('ui.sidebar.staff.affiliate_settle') }}"><i
                                        class="ficon feather icon-zap"></i></a></li>
                        </ul>
                </div>
                <ul class="nav navbar-nav float-right" id="rate-panel">
                    <template v-for="(data, index) in rates">
                        <li class="nav-item d-none d-lg-block">
                            <span class="nav-link dropdown-user-link rate-panel">
                                <div class="user-nav d-sm-flex d-none">
                                    <span class="user-name text-bold-600">@{{ data.price }}</span>
                                    <span class="user-status">@{{ data.currency }}</span>
                                </div>
                            </span>
                        </li>
                    </template>

                    <?php
                        $notifyTypeData = g_enum('NotifyTypeData');
                        $unreadNotifications = $user->unreadNotifications()->get();
                        $total_notify_count = count($unreadNotifications);
                    ?>

                    @if ($total_notify_count > 0)
                    <li class="dropdown dropdown-notification nav-item"><a class="nav-link nav-link-label" href="#" data-toggle="dropdown"><i class="ficon feather icon-bell"></i><span class="badge badge-pill badge-glow badge-primary badge-up">{{ $total_notify_count }}</span></a>
                        <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                            <li class="dropdown-menu-header">
                                <div class="dropdown-header m-0 p-2">
                                    <h3 class="white">{{ sprintf(trans('notify.count_title'), $total_notify_count) }}</h3><span class="notification-title">{{ trans('notify.title') }}</span>
                                </div>
                            </li>
                            <li class="scrollable-container media-list">
                                @foreach ($unreadNotifications as $index => $notification)
                                    @if ($index < 5)
                                    <a class="d-flex justify-content-between" href="{{ route('notify.mark') }}?id={{ $notification->id }}">
                                        <div class="media d-flex align-items-start">
                                            <div class="media-left"><i class="feather {{ $notifyTypeData[$notification->data['type']][0] }} font-medium-5"></i></div>
                                            <div class="media-body">
                                                <h6 class="{{ $notifyTypeData[$notification->data['type']][1] }} media-heading">{{ $notification->data['title'] }}</h6>
                                                <small class="notification-text">{{ $notification->data['message'] }}</small>
                                                <br>
                                                <small class="notification-text"><?php echo($notification->data['subdata']) ?></small>
                                            </div><small>
                                                <time class="media-meta">{{ $notification->created_at->diffForHumans() }}</time></small>
                                        </div>
                                    </a>
                                    @endif
                                @endforeach
                            </li>
                            <li class="dropdown-menu-footer"><a class="dropdown-item p-1 text-center" href="{{ route('notifications.list') }}">{{ trans('notify.view_all') }}</a></li>
                        </ul>
                    </li>
                    @endif
                    <li class="dropdown dropdown-user nav-item">
                            <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">
                                <div class="user-nav d-sm-flex d-none"><span
                                        class="user-name text-bold-600">{{ $user->login_id }}</span><span
                                        class="user-status">{{ $user->name }}</span></div><span><img class="round"
                                        src="{{ cUrl('uploads/avatars') }}/{{ $user->avatar == '' ? '_none.png' : $user->avatar }}"
                                        alt="avatar" height="40" width="40"></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item"
                                    href="{{ route('profile') }}"><i class="feather icon-user"></i>
                                    {{ trans('ui.topbar.profile') }}</a>
                                <div class="dropdown-divider"></div><a class="dropdown-item"
                                    href="{{ route('logout') }}"><i class="feather icon-power"></i>
                                    {{ trans('ui.topbar.logout') }}</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<!-- END: Header-->


<!-- BEGIN: Main Menu-->
<div class="main-menu menu-fixed menu-dark menu-accordion menu-shadow" data-scroll-to-active="true">
    <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
            <li class="nav-item mr-auto">
                <a class="navbar-brand" href="{{ route('home') }}">
                    <img width="30" src="{{ cAsset('app-assets/images/logo.svg') }}">
                    <h2 class="brand-text text-success mb-0">{{ env('APP_NAME') }}</h2>
                </a></li>
            <li class="nav-item nav-toggle"><a class="nav-link modern-nav-toggle pr-0" data-toggle="collapse"><i class="feather icon-x d-block d-xl-none primary font-medium-4 toggle-icon"></i><i class="toggle-icon feather icon-disc font-medium-4 d-none d-xl-block collapse-toggle-icon success" data-ticon="icon-disc"></i></a></li>
        </ul>
    </div>
    <div class="shadow-bottom"></div>
    <?php
        $routeName = Route::currentRouteName();
        $type = Request::get('type', 0);
    ?>
    <div class="main-menu-content">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
            <li class=" nav-item {{ strpos($routeName, 'home') === 0 ? 'active' : '' }}">
                <a href="{{ route('home') }}"><i class="feather icon-home"></i><span class="menu-title" data-i18n="Home">{{ trans('ui.sidebar.home') }}</span></a>
            </li>
            @if ($user->role == USER_ROLE_ADMIN || $user->role == USER_ROLE_CASINO)
                <li class=" nav-item {{ strpos($routeName, 'setting') === 0 ? 'active' : '' }}">
                    <a href="{{ route('setting') }}"><i class="feather icon-settings"></i><span class="menu-title" data-i18n="Setting">{{ trans('ui.sidebar.setting') }}</span></a>
                </li>
                <li class=" nav-item {{ strpos($routeName, 'closing') === 0 ? 'active' : '' }}">
                    <a href="{{ route('closing') }}"><i class="fa fa-window-close-o"></i><span class="menu-title" data-i18n="Closing">{{ trans('ui.sidebar.closing') }}</span></a>
                </li>
                <li class=" nav-item {{ strpos($routeName, 'games') === 0 ? 'active' : '' }}">
                    <a href="{{ route('games') }}"><i class="fa fa-gamepad"></i><span class="menu-title" data-i18n="Games">{{ trans('ui.sidebar.games') }}</span></a>
                </li>
            @endif

            <li class="text-light navigation-header navi-custom-header"><span>{{ trans('ui.sidebar.staff.title') }}</span>
            </li>
            @if ($user->role == USER_ROLE_ADMIN || $user->role == USER_ROLE_CASINO)
            <li class=" nav-item {{ strpos($routeName, 'staff') === 0 ? 'active' : '' }}">
                <a href="{{ route('staff') }}"><i class="fa fa-user-secret"></i><span class="menu-title" data-i18n="Staff">{{ trans('ui.sidebar.staff.list') }}</span></a>
            </li>
            @endif
            <li class=" nav-item {{ ($routeName == 'system.balance') ? 'active' : '' }}">
                <a href="{{ route('system.balance') }}"><i class="fa fa-money"></i><span class="menu-title" data-i18n="System Balances">{{ trans('ui.sidebar.staff.balances') }}</span></a>
            </li>
            @if ($user->role == USER_ROLE_ADMIN || $user->role == USER_ROLE_CASINO)
            <li class=" nav-item {{ ($routeName == 'system.transfer') ? 'active' : '' }}">
                <a href="{{ route('system.transfer') }}"><i class="feather icon-repeat"></i><span class="menu-title" data-i18n="System Balances">{{ trans('ui.sidebar.staff.transfer') }}</span></a>
            </li>
            @endif

            @if ($user->role == USER_ROLE_ADMIN || $user->role == USER_ROLE_CASINO)
            <li class="text-light navigation-header navi-custom-header"><span>{{ trans('ui.sidebar.users.title') }}</span>
            </li>
                <li class=" nav-item {{ ($routeName == 'users' || $routeName == 'users.detail') ? 'active' : '' }}">
                    <a href="{{ route('users') }}"><i class="feather icon-users"></i><span class="menu-title" data-i18n="Users">{{ trans('ui.sidebar.users.list') }}</span></a>
                </li>
                <li class="nav-item has-sub {{ strpos($routeName, 'history-users') === 0 ? 'sidebar-group-active open' : '' }}">
                    <a href="">
                        <i class="fa fa-list"></i>
                        <span class="menu-title" data-i18n="">{{ trans('ui.sidebar.users.history') }}</span>
                    </a>
                    <ul class="menu-content" style="">
                        <li class=" nav-item {{ ($routeName == 'history-users-deposit') ? 'active' : '' }}">
                            <a href="{{ route('history-users-deposit') }}">
                                <i class="feather icon-circle"></i>
                                <span class="menu-title" data-i18n="">{{ trans('ui.sidebar.users.deposit') }}</span>
                            </a>
                        </li>
                        <li class=" nav-item {{ ($routeName == 'history-users-withdraw') ? 'active' : '' }}">
                            <a href="{{ route('history-users-withdraw') }}">
                                <i class="feather icon-circle"></i>
                                <span class="menu-title" data-i18n="">{{ trans('ui.sidebar.users.withdraw') }}</span>
                            </a>
                        </li>
                        <li class=" nav-item {{ ($routeName == 'history-users-transfer') ? 'active' : '' }}">
                            <a href="{{ route('history-users-transfer') }}">
                                <i class="feather icon-circle"></i>
                                <span class="menu-title" data-i18n="">{{ trans('ui.sidebar.users.transfer') }}</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            @if ($user->role == USER_ROLE_ADMIN || $user->role == USER_ROLE_CASINO)
                <li class="text-light navigation-header navi-custom-header"><span>{{ trans('ui.sidebar.requests.title') }}</span>
                </li>
                <li class=" nav-item {{ ($routeName == 'requests.kyc') ? 'active' : '' }}">
                    <a href="{{ route('requests.kyc') }}"><i class="fa fa-user-md"></i><span class="menu-title" data-i18n="Identities">{{ trans('ui.sidebar.requests.identities') }}</span></a>
                </li>
                <li class=" nav-item {{ ($routeName == 'traderwithdraw.request-outline' || $routeName == 'crypto.withdraw.request-list') ? 'active' : '' }}">
                    <a href="{{ route('traderwithdraw.request-outline') }}"><i class="fa fa-location-arrow"></i><span class="menu-title" data-i18n="Withdraw">{{ trans('ui.sidebar.requests.withdraw') }}</span></a>
                </li>
            @endif

            @if ($user->role == USER_ROLE_ADMIN || $user->role == USER_ROLE_CASINO)
                <li class="text-light navigation-header navi-custom-header"><span>{{ trans('ui.sidebar.statistics.title') }}</span>
                </li>
                <li class="nav-item has-sub {{ ($routeName == 'statistics.profits.casino' || $routeName == 'statistics.profits.wallet' || $routeName == 'statistics.profits.detail' || $routeName == 'statistics.profits.all') ? 'sidebar-group-active open' : '' }}">
                    <a href="">
                        <i class="fa fa-jpy"></i>
                        <span class="menu-title" data-i18n="">{{ trans('ui.sidebar.statistics.profits') }}</span>
                    </a>
                    <ul class="menu-content" style="">
                        @if ($user->role == USER_ROLE_ADMIN)
                        <li class=" nav-item {{ ($routeName == 'statistics.profits.casino' || ($type == SYSTEM_PROFIT_TYPE_CASINO && ($routeName == 'statistics.profits.detail' || $routeName == 'statistics.profits.all'))) ? 'active' : '' }}">
                            <a href="{{ route('statistics.profits.casino') }}">
                                <i class="feather icon-circle"></i>
                                <span class="menu-title" data-i18n="">{{ trans('ui.sidebar.statistics.profits_casino') }}</span>
                            </a>
                        </li>
                        @endif
                            <li class=" nav-item {{ ($routeName == 'statistics.profits.wallet' || ($type == SYSTEM_PROFIT_TYPE_WALLET && ($routeName == 'statistics.profits.detail' || $routeName == 'statistics.profits.all'))) ? 'active' : '' }}">
                            <a href="{{ route('statistics.profits.wallet') }}">
                                <i class="feather icon-circle"></i>
                                <span class="menu-title" data-i18n="">{{ trans('ui.sidebar.statistics.profits_wallet') }}</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class=" nav-item {{ ($routeName == 'statistics.gas_usage') ? 'active' : '' }}">
                    <a href="{{ route('statistics.gas_usage') }}"><i class="fa fa-fire"></i><span class="menu-title" data-i18n="Withdraw">{{ trans('ui.sidebar.statistics.gas_usage') }}</span></a>
                </li>
            @endif

            @if ($user->role == USER_ROLE_ADMIN || $user->role == USER_ROLE_CASINO)
                <li class="text-light navigation-header navi-custom-header">
                        <span>{{ trans('ui.sidebar.cms.title') }}</span>
                    </li>
                    <li class=" nav-item {{ $routeName == 'cms.notify' ? 'active' : '' }}">
                        <a href="{{ route('cms.notify') }}"><i class="fa fa-exclamation-circle"></i><span
                                class="menu-title" data-i18n="FAQ">{{ trans('ui.sidebar.cms.notify') }}</span></a>
                    </li>
                    <li class=" nav-item {{ $routeName == 'cms.mail' ? 'active' : '' }}">
                        <a href="{{ route('cms.mail') }}"><i class="fa fa-envelope-o"></i><span class="menu-title"
                                data-i18n="FAQ">{{ trans('ui.sidebar.cms.mail') }}</span></a>
                    </li>
                    <li class=" nav-item {{ $routeName == 'cms.event' ? 'active' : '' }}">
                        <a href="{{ route('cms.event') }}"><i class="fa fa-gift"></i><span class="menu-title"
                                data-i18n="FAQ">{{ trans('ui.sidebar.cms.event') }}</span></a>
                    </li>
                    <li class=" nav-item {{ ($routeName == 'cms.faq' || $routeName == 'cms.faq_categories') ? 'active' : '' }}">
                        <a href="{{ route('cms.faq') }}"><i class="fa fa-question-circle-o"></i><span
                                class="menu-title" data-i18n="FAQ">{{ trans('ui.sidebar.cms.faq') }}</span></a>
                    </li>
                    <li class=" nav-item {{ $routeName == 'cms.inquiry' ? 'active' : '' }}">
                        <a href="{{ route('cms.inquiry') }}"><i class="fa fa-comment"></i><span class="menu-title"
                                data-i18n="Inquiry">{{ trans('ui.sidebar.cms.inquiry') }}</span></a>
                    </li>
            @endif

            @if ($user->role == USER_ROLE_ADMIN)
                <li class="text-light navigation-header navi-custom-header"><span>{{ trans('ui.sidebar.coldwallet.title') }}</span>
                </li>
                <li class=" nav-item {{ ($routeName == 'wallets.balance' || $routeName == 'wallets.balance.detail') ? 'active' : '' }}">
                    <a href="{{ route('wallets.balance') }}"><i class="fa fa-btc"></i><span class="menu-title" data-i18n="Balance">{{ trans('ui.sidebar.coldwallet.balance') }}</span></a>
                </li>
                <li class=" nav-item {{ ($routeName == 'wallets.list') ? 'active' : '' }}">
                    <a href="{{ route('wallets.list') }}"><i class="fa fa-shopping-bag"></i><span class="menu-title" data-i18n="Wallets">{{ trans('ui.sidebar.coldwallet.wallets') }}</span></a>
                </li>
                <li class=" nav-item {{ ($routeName == 'transactions') ? 'active' : '' }}">
                    <a href="{{ route('transactions') }}"><i class="fa fa-leanpub"></i><span class="menu-title" data-i18n="Transactions">{{ trans('ui.sidebar.coldwallet.transactions') }}</span></a>
                </li>
                <li class=" nav-item {{ ($routeName == 'transfer') ? 'active' : '' }}">
                    <a href="{{ route('transfer') }}"><i class="fa fa-mars-double"></i><span class="menu-title" data-i18n="Transfer">{{ trans('ui.sidebar.coldwallet.transfer') }}</span></a>
                </li>
            @endif
        </ul>
    </div>
</div>
<!-- END: Main Menu-->

<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0">@yield('title')</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            @yield('contents')
        </div>
    </div>
</div>
<!-- END: Content-->

<div class="sidenav-overlay"></div>
<div class="drag-target"></div>

<!-- BEGIN: Footer-->
<footer class="footer footer-static footer-light">
    <p class="clearfix blue-grey lighten-2 mb-0"><span class="float-md-left d-block d-md-inline-block mt-25">COPYRIGHT &copy; {{ date('Y') }}<a class="text-bold-800 grey darken-2" href="{{ route('home') }}" target="_blank">{{ env('APP_NAME') }},</a>All rights Reserved</span>
        <button class="btn btn-primary btn-icon scroll-top" type="button"><i class="feather icon-arrow-up"></i></button>
    </p>
</footer>
<!-- END: Footer-->


<!-- BEGIN: Vendor JS-->
<script src="{{ cAsset("app-assets/vendors/js/vendors.min.js") }}"></script>
<!-- BEGIN Vendor JS-->

<!-- BEGIN: Page Vendor JS-->
<!--<script src="{{ cAsset("app-assets/vendors/js/charts/apexcharts.min.js") }}"></script>-->
<script src="{{ cAsset("app-assets/vendors/js/extensions/tether.min.js") }}"></script>
<!--<script src="{{ cAsset("app-assets/vendors/js/extensions/shepherd.min.js") }}"></script>-->
<script src="{{ cAsset('app-assets/vendors/js/extensions/toastr.min.js') }}"></script>
<!-- END: Page Vendor JS-->

<!-- BEGIN: Theme JS-->
<script src="{{ cAsset("app-assets/js/core/app-menu.js") }}"></script>
<script src="{{ cAsset("app-assets/js/core/app.js") }}"></script>
<script src="{{ cAsset("app-assets/js/scripts/components.js") }}"></script>
<!-- END: Theme JS-->

<!-- BEGIN: Page JS-->
<script src="{{ cAsset('vendor/bootbox/bootbox.js') }}"></script>
<script src="{{ cAsset('vendor/loadingoverlay/loadingoverlay.min.js') }}"></script>
<script src="{{ cAsset('js/__common.js') }}"></script>
<script src="{{ cAsset('js/bignumber.js') }}"></script>
<script src="{{ cAsset('js/socket.io.js') }}"></script>
<script src="{{ cAsset('vendor/vue/vue.js') }}"></script>
<script src="{{ cAsset('vendor/vue/vue-numeral-filter.min.js') }}"></script>
<!-- END: Page JS-->

<script>
    var PUBLIC_URL = '{{ cAsset('/') . '/' }}';
    var BASE_URL = PUBLIC_URL;
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });
</script>

@yield('scripts')
@include('layouts/__common')

</body>
<!-- END: Body-->

</html>
