@extends('layouts.afterlogin')

@section('title', trans('setting.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
    <link href="{{ cAsset('https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css') }}"
        rel="stylesheet" />
    <style>
        .min-width-100 {
            min-width: 100px;
        }

    </style>
@endsection

@section('contents')
    <section class="users-list-wrapper">
        @if ($message = Session::get('flash_message'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {{ trans($message) }}
            </div>
        @endif
        @if ($errors->any())
            <div class="card-body">
                <div class="alert alert-danger">
                    <ul class="m-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#nav-master" aria-controls="master"
                                role="tab" aria-selected="false">{{ trans('setting.nav.master') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#nav-crypto" aria-controls="crypto" role="tab"
                                aria-selected="false">{{ trans('setting.nav.crypto') }}</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#nav-maintenance" aria-controls="maintenance" role="tab" aria-selected="false">{{ trans('setting.nav.maintenance') }}</a>
                        </li>

                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="nav-master" aria-labelledby="nav-master" role="tabpanel">
                            <form method="post" action="{{ route('setting.update.master') }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card">
                                    <div class="card-body pb-2">
                                        @foreach ($datas as $index => $data)
                                            @if ($data->option != MAINTENANCE_MODE && $data->option != PRNG_PRIME_VALUE)
                                            <div class="form-group row">
                                                <label class="col-form-label col-sm-3 text-sm-right">
                                                    {{ $data->option }}
                                                </label>
                                                <div class="col-sm-8">
                                                    @if ($data->option == AUTO_USER_WITHDRAW)
                                                        <select
                                                            class="form-control @error($data->option) is-invalid @enderror"
                                                            name="{{ $data->option }}">
                                                            @foreach (g_enum('StatusData') as $status => $value)
                                                                <option value="{{ $status }}"
                                                                    {{ $status == old($data->option, $data->value) ? 'selected' : '' }}>
                                                                    {{ $value[0] }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif ($data->option == GAS_PRICE_MODE)
                                                        <select
                                                            class="form-control @error($data->option) is-invalid @enderror"
                                                            name="{{ $data->option }}">
                                                            @foreach (g_enum('GasPriceModes') as $status => $value)
                                                                <option value="{{ $status }}"
                                                                    {{ $status == old($data->option, $data->value) ? 'selected' : '' }}>
                                                                    {{ $value[0] }}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <input type="text"
                                                            class="form-control @error($data->option) is-invalid @enderror"
                                                            name="{{ $data->option }}"
                                                            value="{{ old($data->option, $data->value) }}">
                                                    @endif
                                                    @error($data->option)
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                                @if (isset($data->suffix) && $data->suffix != '')
                                                    <div class="col-sm-1 mt-1">
                                                        <label class="form-label">{{ $data->suffix }}</label>
                                                    </div>
                                                @endif
                                            </div>
                                            @endif
                                        @endforeach
                                        <hr class="border-light m-0">
                                    </div>
                                    <div class="text-center mt-1 mb-2">
                                        <button type="submit" class="btn btn-primary"><span
                                                class="fa fa-save"></span>&nbsp;{{ trans('ui.button.update') }}</button>&nbsp;
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="nav-crypto" aria-labelledby="nav-crypto" role="tabpanel">
                            <div class="table-responsive">
                                <form action="{{ route('setting.update.crypto') }}" method="POST">
                                    @csrf
                                    <div class="col-sm-12">
                                        <table class="table">
                                            <thead>
                                                <th>{{ trans('setting.table.no') }}</th>
                                                <th class="min-width-100">{{ trans('setting.table.currency') }}</th>
                                                <th class="min-width-100">{{ trans('setting.table.currency_name') }}</th>
                                                <th class="min-width-100">{{ trans('setting.table.unit') }}</th>
                                                <th class="min-width-100">{{ trans('setting.table.rate_decimals') }}</th>
                                                <th class="min-width-100">{{ trans('setting.table.min_deposit') }}</th>
                                                <th class="min-width-100">{{ trans('setting.table.min_transfer') }}</th>
                                                <th class="min-width-100">{{ trans('setting.table.min_withdraw') }}</th>
                                                <th class="min-width-100">{{ trans('setting.table.transfer_fee') }}</th>
                                                <th class="min-width-100">{{ trans('setting.table.gas_price') }}</th>
                                                <th class="min-width-100">{{ trans('setting.table.gas_limit') }}</th>
                                                <th class="min-width-100">{{ trans('setting.table.status') }}</th>
                                            </thead>
                                            <tbody>
                                                <?php $index = 1; ?>
                                                @foreach ($cryptoSettings as $currency => $record)
                                                    <tr>
                                                        <td>{{ $index++ }}</td>
                                                        <td>{{ $currency }}</td>
                                                        <td><input type="text" class="form-control"
                                                                name="{{ $currency }}-name"
                                                                value="{{ $record['name'] }}"></td>
                                                        <td><input type="text" class="form-control"
                                                                name="{{ $currency }}-unit"
                                                                value="{{ $record['unit'] }}"></td>
                                                        <td><input type="text" class="form-control"
                                                                name="{{ $currency }}-rate_decimals"
                                                                value="{{ $record['rate_decimals'] }}"></td>
                                                        <td><input type="text" class="form-control"
                                                                name="{{ $currency }}-min_deposit"
                                                                value="{{ str_replace(',', '', _number_format($record['min_deposit'], $record['rate_decimals'])) }}">
                                                        </td>
                                                        <td><input type="text" class="form-control"
                                                               name="{{ $currency }}-min_transfer"
                                                               value="{{ str_replace(',', '', _number_format($record['min_transfer'], $record['rate_decimals'])) }}">
                                                        </td>
                                                        <td><input type="text" class="form-control"
                                                                name="{{ $currency }}-min_withdraw"
                                                                value="{{ str_replace(',', '', _number_format($record['min_withdraw'], $record['rate_decimals'])) }}">
                                                        </td>
                                                        <td><input type="text" class="form-control"
                                                                name="{{ $currency }}-transfer_fee"
                                                                value="{{ str_replace(',', '', _number_format($record['transfer_fee'], $record['rate_decimals'])) }}">
                                                        </td>
                                                        <td><input type="text" class="form-control"
                                                                name="{{ $currency }}-gas_price"
                                                                value="{{ str_replace(',', '', _number_format($record['gas_price'], $record['rate_decimals'])) }}">
                                                        </td>
                                                        <td><input type="text" class="form-control"
                                                                name="{{ $currency }}-gas_limit"
                                                                value="{{ str_replace(',', '', _number_format($record['gas_limit'], $record['rate_decimals'])) }}">
                                                        </td>
                                                        <td>
                                                            <select class="form-control" id="{{ $currency }}-status">
                                                                @foreach (g_enum('StatusData') as $status => $data)
                                                                    <option value="{{ $status }}"
                                                                        {{ $status == $record['status'] ? 'selected' : '' }}>
                                                                        {{ $data[0] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="text-center mt-1 mb-2">
                                            <button type="submit" class="btn btn-primary"><span
                                                    class="fa fa-save"></span>&nbsp;{{ trans('ui.button.update') }}</button>&nbsp;
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="tab-pane" id="nav-maintenance" aria-labelledby="nav-maintenance" role="tabpanel">
                            <form method="post" action="{{ route('setting.update.maintenance') }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card">
                                    <div class="card-body pb-2">
                                        <div class="form-group row">
                                            <label class="col-form-label col-sm-3 text-sm-right">
                                                {{ trans('setting.maintenance.label') }}
                                            </label>
                                            <div class="col-sm-8">
                                                <div class="custom-control custom-switch mr-2 mb-1">
                                                    <input type="checkbox" class="custom-control-input" name="MAINTENANCE_MODE" id="MAINTENANCE_MODE" {{$datas[0]->value == 0? '' : 'checked'}}>
                                                    <label class="custom-control-label" for="MAINTENANCE_MODE">
                                                        <span class="switch-text-left">On</span>
                                                        <span class="switch-text-right">Off</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-sm-3 text-sm-right">
                                                {{ trans('setting.maintenance.lang') }}
                                            </label>
                                            <div class="col-sm-8">
                                                <div class="form-group">
                                                    <?php echo '<script>let contents=[];</script>'; ?>
                                                    <select class="form-control" id="lang" name="lang">
                                                        @foreach (g_enum('Languages') as $key => $language)
                                                            @if ($language[1] == $lang)
                                                                <option value="{{ $language[1] }}" selected>{{ $language[3] }}</option>
                                                            @else
                                                                <option value="{{ $language[1] }}">{{ $language[3] }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-sm-3 text-sm-right">
                                                {{ trans('setting.maintenance.content') }}
                                            </label>
                                            <div class="col-sm-8">
                                                <textarea name="content" id="content" class="summernote">{{ $maintenanceContent[$lang] }}</textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row {{ $datas[0]->value == 0 ? 'd-none' : '' }}">
                                            <label class="col-form-label col-sm-3 text-sm-right">
                                                {{ trans('setting.maintenance.access_url') }}
                                            </label>
                                            <div class="col-sm-8">
                                                <input type="text" id="token" class="form-control" readonly value="{{ USER_SITE_URL . '?access_token=' . $maintenanceToken }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center mt-1 mb-2">
                                        <button type="submit" class="btn btn-primary"><span
                                                class="fa fa-save"></span>&nbsp;{{ trans('ui.button.update') }}</button>&nbsp;
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Add bank modal -->
    <div class="modal fade" id="modal-bank">
        <div class="modal-dialog">
            <form id="frm-bank" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modal-title">{{ trans('setting.bank.add_title') }}</span>
                        <br>
                        <small id="modal-title" class="text-muted">{{ trans('setting.bank.subtitle') }}</small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('setting.bank.name') }}</label>
                            <input type="text" id="bank-name" class="form-control mr-sm-2 mb-2 mb-sm-0">
                            <small id="bank-name-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('setting.bank.status') }}</label>
                            <select id="bank-status" class="form-control mr-sm-2 mb-2 mb-sm-0">
                                @foreach (g_enum('StatusData') as $status => $data)
                                    <option value="{{ $status }}">{{ $data[0] }}</option>
                                @endforeach
                            </select>
                            <small id="bank-status-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i
                            class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                    <button type="button" class="btn btn-success" id="btn-modal-submit"><i
                            class="fa fa-save"></i>&nbsp;{{ trans('ui.button.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Add bank modal -->
@endsection

@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset('https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js') }}"></script>
    <script src="{{ cAsset('js/bank-list.js') }}"></script>

    <?php echo '<script>let StatusData = '.json_encode(g_enum('StatusData')).';</script>';?>
    <?php echo '<script>let Contents='.json_encode($maintenanceContent).';</script>';?>
    <script>
        function addBank() {
            $('#frm-bank').find('select').removeClass('is-invalid');
            $('#frm-bank').find('input').removeClass('is-invalid');

            _bankId = 0;
            $('#modal-title').html('{{ trans('setting.bank.add_title') }}');
            $('#bank-name').val('');
            $('#bank-status').val('{{ STATUS_ACTIVE }}');
            $('#modal-bank').modal('show');
        }

        function editBank(id) {
            $.ajax({
                url: BASE_URL + 'ajax/bank/getInfo',
                type: 'POST',
                data: {
                    id: id,
                },
                success: function(result) {
                    $('#frm-bank').find('select').removeClass('is-invalid');
                    $('#frm-bank').find('input').removeClass('is-invalid');

                    _bankId = id;
                    $('#modal-title').html('{{ trans('setting.bank.edit_title') }}' + '(' + result['name'] +
                        ')');
                    $('#bank-name').val(result['name']);
                    $('#bank-status').val(result['status']);
                    $('#modal-bank').modal('show');
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        function deleteBank(id) {
            bootbox.confirm({
                message: "{{ trans('ui.alert.ask_delete') }}",
                buttons: {
                    cancel: {
                        className: 'btn btn-light',
                        label: '<i class="fa fa-times"></i> {{ trans('ui.button.cancel') }}'
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> {{ trans('ui.button.confirm') }}'
                    }
                },
                callback: function(result) {
                    if (result) {
                        $.ajax({
                            url: BASE_URL + 'ajax/bank/delete',
                            type: 'POST',
                            data: {
                                'id': id,
                            },
                            success: function(result) {
                                listTable.ajax.reload();
                            },
                            error: function(err) {
                                bootbox.alert("{{ trans('ui.alert.delete_failed') }}");
                                console.log(err);
                            }
                        });
                    }
                }
            });
        }

        $('#btn-modal-submit').on('click', function() {
            $.ajax({
                url: BASE_URL + 'ajax/bank/' + (_bankId == 0 ? 'add' : 'edit'),
                type: 'POST',
                data: {
                    id: _bankId,
                    name: $('#bank-name').val(),
                    status: $('#bank-status').val(),
                },
                success: function() {
                    listTable.ajax.reload();
                    $('#modal-bank').modal('hide');
                },
                error: function(err) {
                    var errorMsg = err['responseJSON']['errors'];
                    $('#frm-bank').find('select').removeClass('is-invalid');
                    $('#frm-bank').find('input').removeClass('is-invalid');

                    if (errorMsg['name'] != null) {
                        $('#bank-name').addClass('is-invalid');
                        document.getElementById('bank-name-error').innerHTML = errorMsg['name'];
                    }
                    if (errorMsg['status'] != null) {
                        $('#bank-status').addClass('is-invalid');
                        document.getElementById('bank-status-error').innerHTML = errorMsg['status'];
                    }
                }
            });
        });

        function initTable() {
            listTable = $('#bank-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/bank/search',
                    type: 'POST',
                },
                language: {
                    paginate: {
                        previous: '&nbsp;',
                        next: '&nbsp;',
                    },
                    sLengthMenu: "{{ trans('ui.table.sLengthMenu') }}",
                    zeroRecords: "{{ trans('ui.table.zeroRecords') }}",
                    info: "{{ trans('ui.table.info') }}",
                    infoEmpty: "{{ trans('ui.table.infoEmpty') }}",
                    infoFiltered: "{{ trans('ui.table.infoFiltered') }}",
                },
                lengthMenu: [
                    [10, 25, 50, 100, 1000, 2500, -1],
                    [10, 25, 50, 100, 1000, 2500, "{{ trans('ui.table.all') }}"]
                ],
                columnDefs: [{
                    targets: [3],
                    orderable: false,
                    searchable: false
                }],
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: null
                    },
                ],
                createdRow: function(row, data, index) {
                    var pageInfo = listTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(2).html('').append(
                        '<span class="text-white badge-glow badge badge-' + StatusData[data['status']][1] +
                        '">' + StatusData[data['status']][0] + '</span>'
                    );

                    $('td', row).eq(3).html('').append(
                        '<a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip" href="javascript:editBank(' +
                        data["id"] + ');" title="' + '{{ trans('ui.button.edit') }}' + '">' +
                        '<i class="fa fa-edit"></i></a>' +
                        '<a class="btn btn-icon btn-icon-rounded-circle text-danger btn-flat-danger user-tooltip" href="javascript:deleteBank(' +
                        data["id"] + ');" title="' + '{{ trans('ui.button.delete') }}' + '">' +
                        '<i class="fa fa-remove"></i></a>'
                    );
                },
            });
        }

        $(document).on('change', '#lang', function () {
            lang = $(this).val();
            summernote.summernote('code', Contents[lang]);
        });
    </script>
@endsection
