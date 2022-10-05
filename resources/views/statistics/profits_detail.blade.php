@extends('layouts.afterlogin')

@section('title', sprintf(trans('statistics.profits.detail_title' . $sel_type), $sel_date, $sel_currency))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
    <link href="{{ cAsset('app-assets/vendors/css/charts/apexcharts.css') }}" rel="stylesheet">
@endsection

@section('contents')
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <a type="button" class="text-white btn btn-secondary" href="{{ route('statistics.profits.' . ($sel_type == SYSTEM_PROFIT_TYPE_CASINO ? 'casino' : 'wallet')) }}">
                    <i class="fa fa-arrow-right"></i>&nbsp;{{ trans('ui.button.back') }}
                </a>
            </div>
        </div>
    </div>

    <?php $CryptoSettings = Session::get('crypto_settings'); ?>
    <section class="users-list-wrapper">
        <div class="card">
            <div class="card-body">
            <div class="table-responsive">
                <table id="profits-list" class="table table-hover">
                    <thead>
                    <tr>
                        <th>{{ trans('statistics.profits.no') }}</th>
                        <th>{{ trans('statistics.profits.date') }}</th>
                        <th>{{ trans('statistics.profits.total') }}({{ $sel_currency }})</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php $index = 1; ?>
                        @foreach ($month_data as $stat_date => $total)
                            <tr>
                                <td>{{ $index ++ }}</td>
                                <td>{{ $stat_date }}</td>
                                @if (isset($total))
                                    <td>
                                        <a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip"
                                           href="{{ route('statistics.profits.all') }}?date={{ $stat_date }}&currency={{ $sel_currency }}&type={{ $sel_type }}"
                                           title="{{ trans('ui.button.detail') }}"
                                           style="text-decoration: underline;"
                                        >
                                            {{ _number_format($total, min(MINIMUM_BALANCE_DECIMALS, $CryptoSettings[$sel_currency]['rate_decimals'])) }}
                                        </a>
                                    </td>
                                @else
                                    <td>0</td>
                                @endif
                            </tr>
                        @endforeach
                        @if (count($month_data) == 0)
                            <tr>
                                <td colspan="3" class="text-center">{{ trans('ui.table.zeroRecords') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </section>
@endsection


@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset('app-assets/vendors/js/charts/apexcharts.min.js') }}"></script>
    <script src="{{ cAsset("js/profits-list.js") }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <script>

    </script>
@endsection
