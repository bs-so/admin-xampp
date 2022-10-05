@extends('layouts.afterlogin')

@section('title', trans('balance.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
    <link href="{{ cAsset('app-assets/vendors/css/charts/apexcharts.css') }}" rel="stylesheet">
    <link href="{{ cAsset('app-assets/css/core/colors/palette-gradient.css') }}" rel="stylesheet">
@endsection

@section('contents')
    @if ($message = Session::get('flash_message'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {{ trans($message) }}
        </div>
    @endif


    <!-- users list start -->
    <section class="users-list-wrapper">
        <div class="card">
            <div class="card-header">
                <h1 class="text-danger">{{ $sel_currency }}</h1>
            </div>
            <div class="card-body">
                <!--
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <a type="button" class="text-white btn btn-primary mb-mobile-2" href="javascript:refreshTotal();">
                                <i class="fa fa-refresh"></i>&nbsp;{{ trans('ui.button.refresh') }}
                            </a>
                        </div>
                    </div>
                </div>
                -->

                <ul class="nav nav-tabs nav-fill" role="tablist">
                    @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                        <li class="nav-item">
                            <a class="nav-link {{ $currency == $sel_currency ? 'active' : '' }}" href="{{ route('wallets.balance') }}?currency={{ $currency }}" aria-controls="{{ $currency }}">{{ $currency }}</a>
                        </li>
                    @endforeach
                </ul>

                <div class="row ml-1 mr-1" id="main-list">
                    <div class="col-sm-6">
                        <div class="table-responsive">
                            <table id="balance-list" class="table table-hover">
                                <thead>
                                <tr>
                                    <th>{{ trans('balance.table.no') }}</th>
                                    <th>{{ trans('balance.table.type') }}</th>
                                    <th>{{ trans('balance.table.count') }}</th>
                                    <th>{{ trans('balance.table.balance') }}</th>
                                    <th>{{ trans('balance.table.actions') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $CryptoSettings = Session::get('crypto_settings');
                                        $WalletTypeData = g_enum('WalletTypeData');
                                    ?>
                                    @foreach ($summary_data as $index => $data)
                                        <tr>
                                            <td>{{ $index + 1  }}</td>
                                            <td><span class="text-white badge badge-glow badge-{{ $WalletTypeData[$data->type][1] }}">{{ $WalletTypeData[$data->type][0] }}</span></td>
                                            <td>{{ $data->total_count }}</td>
                                            <td id="balance-{{ $data->currency }}-{{ $index }}">{{ _number_format($data->total_balance, (isset($CryptoSettings) ? $CryptoSettings[$data->currency]['rate_decimals'] : 18)) }}</td>
                                            <td><a class="btn btn-icon btn-icon-rounded-circle text-info btn-flat-info user-tooltip" href="{{ route('wallets.balance.detail') }}?currency={{ $sel_currency }}&type={{ $data->type }}" title="{{ trans('ui.button.detail') }}">
                                                <i class="fa fa-edit"></i></a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <div id="pie-chart" class="mx-auto"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- users list ends -->
@endsection

@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset('app-assets/vendors/js/charts/apexcharts.min.js') }}"></script>
    <script src="{{ cAsset('vendor/vue/vue.js') }}"></script>
    <script src="{{ cAsset("js/wallets-balance.js") }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <?php echo('<script>let StatusData = ' . json_encode(g_enum('StatusData')) . ';</script>') ?>
    <?php echo('<script>let WalletTypeData = ' . json_encode(g_enum('WalletTypeData')) . ';</script>') ?>
    <script>
        let selCurrency = '{{ $sel_currency }}';

        function refreshTotal() {
            showOverlay($('#main-list'), true, '{{ trans('ui.alert.updating') }}');

            $.ajax({
                url: BASE_URL + 'ajax/wallets/refreshBalance',
                type: 'POST',
                data: {
                    currency: '{{ $sel_currency }}',
                },
                success: function(result) {
                    showOverlay($('#main-list'), false, '{{ trans('ui.alert.updating') }}');
                    loadSummaryData();
                },
                error: function(err) {
                    showOverlay($('#main-list'), false, '{{ trans('ui.alert.updating') }}');
                    showToast('{{ trans('ui.alert.info') }}', '{{ trans('balance.message.failed') }}', "warning");
                    console.log(err);
                }
            });
        }

        function loadSummaryData() {
            $.ajax({
                url: BASE_URL + 'ajax/wallets/getBalanceSummary',
                type: 'POST',
                data: {
                    currency: '{{ $sel_currency }}',
                },
                success: function(result) {
                    let labels = [], series = [];
                    for (let i = 0; i < result.length; i ++) {
                        labels.push(WalletTypeData[result[i]['type']][0]);
                        series.push(BigNumber(result[i]['total_balance']).toNumber());
                        let balance = _number_format(result[i]['total_balance'], CryptoSettings[result[i]['currency']]['rate_decimals']);
                        $('#balance-' + result[i]['currency'] + '-' + i).html(balance);
                    }
                    initChart(labels, series);
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        function initChart(labels, series) {
            pieChartOptions = {
                chart: {
                    type: 'pie',
                    height: 350
                },
                colors: themeColors,
                labels: labels,
                series: series,
                legend: {
                    itemMargin: {
                        horizontal: 2
                    },
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 350
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            }
            pieChart = new ApexCharts(
                document.querySelector("#pie-chart"),
                pieChartOptions
            );
            pieChart.render();
        }
    </script>
@endsection
