@extends('layouts.afterlogin')

@section('title', trans('home.title'))

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ cAsset("app-assets/vendors/css/charts/apexcharts.css") }}">
    <link href="{{ cAsset('app-assets/vendors/css/extensions/swiper.min.css') }}" rel="stylesheet">
    <link href="{{ cAsset('app-assets/css/plugins/extensions/swiper.css') }}" rel="stylesheet">
@endsection

@section('scripts')
    <script src="{{ cAsset("app-assets/vendors/js/charts/apexcharts.min.js") }}"></script>
    <script src="{{ cAsset('app-assets/vendors/js/extensions/swiper.min.js') }}"></script>
    <script src="{{ cAsset('vendor/vue/vue.js') }}"></script>
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('js/home.js') }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <script>
        function initVue() {
            v_TransferFees = new Vue({
                el: '#transfer-fees',
                data: {
                    lists: []
                }
            });

            v_UserDepositTable = new Vue({
                el: '#user_deposit',
                data: {
                    lists: []
                }
            });

            v_UserWithdrawTable = new Vue({
                el: '#user_withdraw',
                data: {
                    lists: []
                }
            });

            v_UserTransferTable = new Vue({
                el: '#user_transfer',
                data: {
                    lists: []
                },
                methods: {
                    getFormatedText: function(currency, str) {
                        return _number_format(str, Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[currency]['rate_decimals']));
                    }
                }
            });

            v_SystemTransferTable = new Vue({
                el: '#system_transfer',
                data: {
                    lists: []
                },
                methods: {
                    getFormatedText: function(currency, str) {
                        return _number_format(str, Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[currency]['rate_decimals']));
                    }
                }
            });
        }

        function loadChartData() {
            $.ajax({
                url: BASE_URL + 'ajax/home/getRegisterData',
                type: 'POST',
                success: function(result) {
                    initChart(result);
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        function initChart(data) {
            var registerChartoptions = {
                chart: {
                    height: 100,
                    type: 'area',
                    toolbar: {
                        show: false,
                    },
                    sparkline: {
                        enabled: true
                    },
                    grid: {
                        show: false,
                        padding: {
                            left: 0,
                            right: 0
                        }
                    },
                },
                colors: [$primary],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2.5
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 0.9,
                        opacityFrom: 0.7,
                        opacityTo: 0.5,
                        stops: [0, 80, 100]
                    }
                },
                series: [{
                    name: '登録数',
                    data: data,
                }],

                xaxis: {
                    labels: {
                        show: false,
                    },
                    axisBorder: {
                        show: false,
                    }
                },
                yaxis: [{
                    y: 0,
                    offsetX: 0,
                    offsetY: 0,
                    padding: { left: 0, right: 0 },
                }],
                tooltip: {
                    x: { show: false }
                },
            }

            registerChart = new ApexCharts(
                document.querySelector("#register-chart"),
                registerChartoptions
            );

            registerChart.render();
        }
    </script>
@endsection

@section('contents')
    <?php $me = Auth::user(); ?>
    <section id="dashboard-analytics">
        <div class="row match-height">
            <div class="col-lg-6 col-md-12 col-sm-12">
				<!--
                <div class="card">
                    <div class="card-header mb-1">
                        <h4 class="card-title">{{ trans('home.section.transfer_fees') }}</h4>
                    </div>
                    <div class="card-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-dark mb-0" id="transfer-fees">
                                <thead>
                                <tr>
                                    <th>{{ trans('home.table.no') }}</th>
                                    <th>{{ trans('home.table.currency') }}</th>
                                    <th>{{ trans('home.table.fast') }}</th>
                                    <th>{{ trans('home.table.standard') }}</th>
                                    <th>{{ trans('home.table.safelow') }}</th>
                                </tr>
                                </thead>
                                <tr v-for="(list, name, index) in lists" v-cloak>
                                    <td>@{{ index + 1 }}</td>
                                    <td>@{{ name }}</td>
                                    <td>@{{ list[1] }}</td>
                                    <td>@{{ list[2] }}</td>
                                    <td>@{{ list[3] }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
				-->
				<div class="card bg-analytics text-white">
                    <div class="card-content">
                        <div class="card-body text-center">
                            <img src="{{ cAsset('app-assets/images/elements/decore-left.png') }}" class="img-left" alt="card-img-left">
                            <img src="{{ cAsset('app-assets/images/elements/decore-right.png') }}" class="img-right" alt="card-img-right">
                            <div class="avatar avatar-xl bg-primary shadow mt-0">
                                <div class="avatar-content">
                                    <i class="feather icon-award white font-large-1"></i>
                                </div>
                            </div>
                            <div class="text-center">
                                <?php
                                    $now = date('H');
                                    $type = 3;
                                    if ($now > 6 && $now <= 12) $type = 1;
                                    if ($now > 12 && $now < 18) $type = 2;
                                ?>
                                <h1 class="mb-2 text-white">{{ sprintf(trans('home.greet.title' . $type), $me->name) }}</h1>
                                <p class="m-auto w-75">&nbsp;</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-12">
                <div class="card">
                    <div class="card-header d-flex flex-column align-items-start pb-0">
                        <div class="avatar bg-rgba-primary p-50 m-0">
                            <div class="avatar-content">
                                <i class="feather icon-users text-primary font-medium-5"></i>
                            </div>
                        </div>
                        <div class="row" style="width: 100%">
                            <div class="col-sm-4">
                                <label>{{ trans('home.info.user_count') }}</label>
                                <h3>{{ number_format($user_count) }}</h3>
                            </div>
                            <div class="col-sm-4">
                                <label>{{ trans('home.info.access_count') }}</label>
                                <h3>{{ number_format($access_count) }}</h3>
                            </div>
                            <div class="col-sm-4">
                                <label>{{ trans('home.info.connections') }}</label>
                                <h3>{{ number_format($connections) }}</h3>
                            </div>
                        </div>
                        <div class="row" style="width: 100%">
                            <div class="col-sm-4">
                                <label>{{ trans('home.info.total_ram') }}</label>
                                <h3 id="total-ram">-</h3>
                            </div>
                            <div class="col-sm-4">
                                <label>{{ trans('home.info.free_ram') }}</label>
                                <h3 id="free-ram">-</h3>
                            </div>
                            <div class="col-sm-4">
                                <label>{{ trans('home.info.free_percent') }}</label>
                                <h3 id="free-percent">-</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card-content">
                        <div id="register-chart"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" id="table-striped-dark">
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-header mb-1">
                        <h4 class="card-title">{{ trans('home.section.user_deposit') }}</h4>
                    </div>
                    <div class="card-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-dark mb-0" id="user_deposit">
                                <thead>
                                    <tr>
                                        <th>{{ trans('home.table.no') }}</th>
                                        <th>{{ trans('home.table.currency') }}</th>
                                        <th>{{ trans('home.table.today_result') }}</th>
                                        <th>{{ trans('home.table.queue_count') }}</th>
                                        <th>{{ trans('home.table.last_updated') }}</th>
                                    </tr>
                                </thead>
                                <tr v-for="(list, name, index) in lists" v-cloak>
                                    <td>@{{ index + 1 }}</td>
                                    <td>@{{ name }}</td>
                                    <td>@{{ list['queue_count'] }} / @{{ list['today_count'] }}</td>
                                    <td>@{{ list['queue_count'] }}</td>
                                    <td>@{{ list['last_updated'] }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-header mb-1">
                        <h4 class="card-title">{{ trans('home.section.user_withdraw') }}</h4>
                    </div>
                    <div class="card-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-dark mb-0" id="user_withdraw">
                                <thead>
                                <tr>
                                    <th>{{ trans('home.table.no') }}</th>
                                    <th>{{ trans('home.table.currency') }}</th>
                                    <th>{{ trans('home.table.today_result') }}</th>
                                    <th>{{ trans('home.table.queue_count') }}</th>
                                    <th>{{ trans('home.table.last_updated') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(list, name, index) in lists" v-cloak>
                                    <td>@{{ index + 1 }}</td>
                                    <td>@{{ name }}</td>
                                    <td>@{{ list['queue_count'] }} / @{{ list['today_count'] }}</td>
                                    <td>@{{ list['queue_count'] }}</td>
                                    <td>@{{ list['last_updated'] }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-header mb-1">
                        <h4 class="card-title">{{ trans('home.section.user_transfer') }}</h4>
                    </div>
                    <div class="card-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-dark mb-0" id="user_transfer">
                                <thead>
                                <tr>
                                    <th>{{ trans('home.table.no') }}</th>
                                    <th>{{ trans('home.table.currency') }}</th>
                                    <th>{{ trans('home.table.today_result') }}</th>
                                    <th>{{ trans('home.table.last_updated') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(list, name, index) in lists" v-cloak>
                                    <td>@{{ index + 1 }}</td>
                                    <td>@{{ name }}</td>
                                    <td>@{{ list['today_count'] }} / @{{ getFormatedText(name, list['today_amount']) }}</td>
                                    <td>@{{ list['last_updated'] }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-header mb-1">
                        <h4 class="card-title">{{ trans('home.section.system_transfer') }}</h4>
                    </div>
                    <div class="card-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-dark mb-0" id="system_transfer">
                                <thead>
                                <tr>
                                    <th>{{ trans('home.table.no') }}</th>
                                    <th>{{ trans('home.table.currency') }}</th>
                                    <th>{{ trans('home.table.today_result') }}</th>
                                    <th>{{ trans('home.table.last_updated') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(list, name, index) in lists" v-cloak>
                                    <td>@{{ index + 1 }}</td>
                                    <td>@{{ name }}</td>
                                    <td>@{{ list['today_count'] }} / @{{ getFormatedText(name, list['today_amount']) }}</td>
                                    <td>@{{ list['last_updated'] }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
