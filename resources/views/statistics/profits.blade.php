@extends('layouts.afterlogin')

@section('title', trans('statistics.profits.title' . $type))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
    <link href="{{ cAsset('app-assets/vendors/css/charts/apexcharts.css') }}" rel="stylesheet">
@endsection

@section('contents')
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
                        @foreach ($CryptoSettings as $currency => $data)
                            <th>{{ $currency }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                        <?php $index = 1; ?>
                        @foreach ($total_data as $stat_date => $data)
                            <tr>
                                <td>{{ $index ++ }}</td>
                                <td>{{ $stat_date }}</td>
                                @foreach ($CryptoSettings as $currency => $currency_data)
                                    @if (isset($data[$currency]))
                                        <td>
                                            <a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip"
                                               href="{{ route('statistics.profits.detail') }}?date={{ $stat_date }}&currency={{ $currency }}&type={{ $type }}"
                                               title="{{ trans('ui.button.detail') }}"
                                               style="text-decoration: underline;"
                                            >
                                                {{ _number_format($data[$currency], min(MINIMUM_BALANCE_DECIMALS, $currency_data['rate_decimals'])) }}
                                            </a>
                                        </td>
                                    @else
                                        <td>0</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                        @if (count($total_data) == 0)
                            <tr>
                                <td colspan="{{ 3 + count($CryptoSettings) }}" class="text-center">{{ trans('ui.table.zeroRecords') }}</td>
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
