@extends('layouts.afterlogin')

@section('title', trans('wallets.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
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
                <h4 class="card-title">{{ trans('ui.search.filters') }}</h4>
                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                <div class="heading-elements">
                    <ul class="list-inline mb-0">
                        <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
                        <li><a data-action="close"><i class="feather icon-x"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="card-content collapse show">
                <div class="card-body">
                    <div class="users-list-filter">
                        <form>
                            <div class="row">
                                <div class="col-md">
                                    <label class="form-label">{{ trans('wallets.table.type') }}</label>
                                    <select id="filter-type" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('WalletTypeData') as $type => $data)
                                            <option value="{{ $type }}">{{ $data[0] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('wallets.table.address') }}</label>
                                    <input type="text" id="filter-address" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('wallets.table.status') }}</label>
                                    <select id="filter-status" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('StatusData') as $index => $status)
                                            <option value="{{ $index }}">{{ $status[0] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md col-xl-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" onclick="javascript:doSearch()" class="btn btn-primary btn-block">
                                        <i class="fa fa-search"></i>&nbsp;{{ trans('ui.button.search') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h1 class="text-danger">{{ $sel_currency }}</h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <button type="button" class="text-white btn btn-success mb-mobile-2" onclick="javascript:showAddModal();">
                                <i class="fa fa-plus"></i>&nbsp;{{ trans('ui.button.add') }}
                            </button>
                            <button type="button" class="text-white btn btn-primary mb-mobile-2" onclick="javascript:showDepositModal();">
                                <i class="fa fa-asterisk"></i>&nbsp;{{ trans('wallets.button.select_deposit') }}
                            </button>
                            <button type="button" class="text-white btn btn-danger mb-mobile-2" onclick="javascript:showWithdrawModal();">
                                <i class="fa fa-asterisk"></i>&nbsp;{{ trans('wallets.button.select_withdraw') }}
                            </button>
                            <button type="button" class="text-white btn btn-warning mb-mobile-2" onclick="javascript:showGasTankModal();" {{ $sel_currency == 'ETH' ? '' : 'disabled' }}>
                                <i class="fa fa-asterisk"></i>&nbsp;{{ trans('wallets.button.select_gastank') }}
                            </button>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs nav-fill" role="tablist">
                    @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                        <li class="nav-item">
                            <a class="nav-link {{ $currency == $sel_currency ? 'active' : '' }}" href="{{ route('wallets.list') }}?currency={{ $currency }}" aria-controls="{{ $currency }}">{{ $currency }}</a>
                        </li>
                    @endforeach
                </ul>

                <div class="row ml-1 mr-1">
                    <div class="table-responsive">
                        <table id="wallets-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('wallets.table.no') }}</th>
                                <th>{{ trans('wallets.table.wallet_id') }}</th>
                                <th>{{ trans('wallets.table.type') }}</th>
                                <th>{{ trans('wallets.table.specified') }}</th>
                                <th>{{ trans('wallets.table.address') }}</th>
                                <th>{{ trans('wallets.table.priv_key') }}</th>
                                <th>{{ trans('wallets.table.balance') }}</th>
                                <th>{{ trans('wallets.table.status') }}</th>
                                <th>{{ trans('wallets.table.remark') }}</th>
                                <th>{{ trans('wallets.table.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- users list ends -->

    <!-- Add wallet modal -->
    <div class="modal fade" id="modal-add-wallet">
        <div class="modal-dialog">
            <form id="frm-add-wallet" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ trans('wallets.add.title') }}
                        <br>
                        <small id="modal-title" class="text-muted">{{ trans('wallets.add.subtitle') }}</small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('wallets.table.currency') }}</label>
                            <select class="form-control" id="add-currency">
                                @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                                    <option value="{{ $currency }}" {{ $currency == $sel_currency ? 'selected' : '' }}>{{ $currency }}</option>
                                @endforeach
                            </select>
                            <small id="currency-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('wallets.table.address') }}</label>
                            <input type="text" id="add-address" class="form-control mr-sm-2 mb-2 mb-sm-0">
                            <small id="address-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('wallets.table.type') }}</label>
                            <select class="form-control" id="add-type">
                                @foreach (g_enum('WalletTypeData') as $type => $data)
                                    <option value="{{ $type }}">{{ $data[0] }}</option>
                                @endforeach
                            </select>
                            <small id="type-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('wallets.table.remark') }}</label>
                            <textarea rows="2" id="add-remark" class="form-control mr-sm-2 mb-2 mb-sm-0"></textarea>
                            <small id="remark-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                    <hr>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('wallets.add.result') }}</label>
                            <textarea rows="2" id="add-result" class="form-control mr-sm-2 mb-2 mb-sm-0" readonly></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                    <button type="button" class="btn btn-success" id="btn-add-confirm"><i class="fa fa-check"></i>&nbsp;{{ trans('ui.button.confirm') }}</button>
                    <button type="button" class="btn btn-danger" id="btn-add-submit" disabled><i class="fa fa-plus"></i>&nbsp;{{ trans('ui.button.add') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Add wallet modal -->

    <!-- Set private key -->
    <div class="modal fade" id="modal-set-private">
        <div class="modal-dialog">
            <form id="frm-set-private" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ trans('wallets.private.title') }}
                        <br>
                        <small id="modal-title" class="text-muted">{{ trans('wallets.private.subtitle') }}</small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('wallets.table.priv_key') }}</label>
                            <input type="text" id="private-key" class="form-control mr-sm-2 mb-2 mb-sm-0">
                            <small id="private-key-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="btn-private-submit"><i class="fa fa-plus"></i>&nbsp;{{ trans('ui.button.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Set private key -->

    <!-- Select Deposit Wallet -->
    <div class="modal fade" id="modal-select-deposit">
        <div class="modal-dialog">
            <form id="frm-select-deposit" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ trans('wallets.deposit.title') }}
                        <br>
                        <small id="modal-title" class="text-muted">{{ trans('wallets.deposit.subtitle') }}</small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body" id="select-deposit">
                    <div class="form-row">
                        <div v-for="(data, index) in list" class="form-group row">
                            <div class="wallets-list-radio">
                                <div class="vs-radio-con">
                                    <input type="radio" name="sel-deposit-radio" v-bind:checked="isSelected(index)" :value="data.id">
                                    <span class="vs-radio">
                                        <span class="vs-radio--border"></span>
                                        <span class="vs-radio--circle"></span>
                                    </span>
                                    <span>@{{ data.wallet_address }}</span>
                                </div>
                            </div>
                            <div class="wallets-list-status">
                                <span class="text-white badge badge-glow" :class="getWalletClass(index)">@{{ getWalletType(index) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="btn-select-deposit"><i class="fa fa-check"></i>&nbsp;{{ trans('ui.button.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Select Deposit Wallet -->

    <!-- Select Withdraw Wallet -->
    <div class="modal fade" id="modal-select-withdraw">
        <div class="modal-dialog">
            <form id="frm-select-withdraw" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ trans('wallets.withdraw.title') }}
                        <br>
                        <small id="modal-title" class="text-muted">{{ trans('wallets.withdraw.subtitle') }}</small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body" id="select-withdraw">
                    <div class="form-row">
                        <div v-for="(data, index) in list" class="form-group row">
                            <div class="wallets-list-radio">
                                <div class="vs-radio-con">
                                    <input type="radio" name="sel-withdraw-radio" v-bind:checked="isSelected(index)" :value="data.id">
                                    <span class="vs-radio">
                                        <span class="vs-radio--border"></span>
                                        <span class="vs-radio--circle"></span>
                                    </span>
                                    <span>@{{ data.wallet_address }}</span>
                                </div>
                            </div>
                            <div class="wallets-list-status">
                                <span class="text-white badge badge-glow" :class="getWalletClass(index)">@{{ getWalletType(index) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="btn-select-withdraw"><i class="fa fa-check"></i>&nbsp;{{ trans('ui.button.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Select Withdraw Wallet -->

    <!-- Select GasTank Wallet -->
    <div class="modal fade" id="modal-select-gastank">
        <div class="modal-dialog">
            <form id="frm-select-gastank" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ trans('wallets.gastank.title') }}
                        <br>
                        <small id="modal-title" class="text-muted">{{ trans('wallets.gastank.subtitle') }}</small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body" id="select-gastank">
                    <div class="form-row">
                        <div v-for="(data, index) in list" class="form-group row">
                            <div class="wallets-list-radio">
                                <div class="vs-radio-con">
                                    <input type="radio" name="sel-gastank-radio" v-bind:checked="isSelected(index)" :value="data.id">
                                    <span class="vs-radio">
                                        <span class="vs-radio--border"></span>
                                        <span class="vs-radio--circle"></span>
                                    </span>
                                    <span>@{{ data.wallet_address }}</span>
                                </div>
                            </div>
                            <div class="wallets-list-status">
                                <span class="text-white badge badge-glow" :class="getWalletClass(index)">@{{ getWalletType(index) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="btn-select-gastank"><i class="fa fa-check"></i>&nbsp;{{ trans('ui.button.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Select GasTank Wallet -->
@endsection

@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset('vendor/vue/vue.js') }}"></script>
    <script src="{{ cAsset("js/wallets-list.js") }}"></script>

    <?php echo('<script>let StatusData = ' . json_encode(g_enum('StatusData')) . ';</script>') ?>
    <?php echo('<script>let WalletTypeData = ' . json_encode(g_enum('WalletTypeData')) . ';</script>') ?>
    <?php echo('<script>let WalletSpecifiedData = ' . json_encode(g_enum('WalletSpecifiedData')) . ';</script>') ?>
    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <script>
        let selCurrency = '{{ $sel_currency }}';
        $('#btn-add-confirm').on('click', function() {
            let currency = $('#add-currency').val();
            let address = $('#add-address').val();

            if (address == '') {
                showToast('{{ trans('wallets.add.check_result') }}', '{{ trans('wallets.add.subtitle') }}', "warning");
                $('#add-address').focus();
                return;
            }

            $('#add-result').html('{{ trans('wallets.add.checking') }}');
            $.ajax({
                url: BASE_URL + 'ajax/wallets/checkAddress',
                type: 'POST',
                data: {
                    currency: currency,
                    address: address,
                },
                success: function(result) {
                    if (result.success != 0){
                        $('#add-result').html('{{ trans('wallets.add.invalid') }}');
                        showToast('{{ trans('wallets.add.check_result') }}', '{{ trans('wallets.add.invalid') }}', "warning");
                        return;
                    }
                    $('#add-result').html('{{ trans('wallets.add.confirmed') }}');
                    $('#btn-add-submit').attr('disabled', false);
                },
                error: function() {
                    $('#add-result').html('{{ trans('wallets.add.invalid') }}');
                    showToast('{{ trans('wallets.add.check_result') }}', '{{ trans('wallets.add.invalid') }}', "warning");
                }
            });
        });

        $('#btn-private-submit').on('click', function() {
            let private_key = $('#private-key').val();
            if (private_key == '') {
                showToast('{{ trans('wallets.add.check_result') }}', '{{ trans('wallets.private.subtitle') }}', "warning");
                return;
            }

            $.ajax({
                url: BASE_URL + 'ajax/wallets/setPrivateKey',
                type: 'POST',
                data: {
                    id: selectedId,
                    private_key: private_key,
                },
                success: function(result) {
                    $('#modal-set-private').modal('hide');
                    showToast('{{ trans('wallets.add.check_result') }}', '{{ trans('wallets.private.success') }}', "success");
                    listTable.ajax.reload();
                },
                error: function(err) {
                    console.log(err);
                }
            });
        });

        function deleteRecord(id) {
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
                            url: BASE_URL + 'ajax/wallets/delete',
                            type: 'POST',
                            data: {
                                id: id,
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

        function initTable() {
            listTable = $('#wallets-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/wallets/search',
                    type: 'POST',
                    data: {
                        currency: '{{ $sel_currency }}',
                    }
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
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "{{ trans('ui.table.all') }}"]],
                columnDefs: [{
                    targets: [9],
                    orderable: false,
                    searchable: false
                }],
                columns: [
                    {data: 'id'},
                    {data: 'id'},
                    {data: 'type'},
                    {data: 'specified'},
                    {data: 'wallet_address'},
                    {data: null},
                    {data: 'balance', className: 'text-right'},
                    {data: 'status'},
                    {data: 'remark'},
                    {data: null},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = listTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(1).html('').append(
                        WalletTypeData[data['type']][2] + data['id']
                    );
                    $('td', row).eq(2).html('').append(
                        '<span class="text-white badge-glow badge badge-' + WalletTypeData[data['type']][1] + '">' + WalletTypeData[data['type']][0] + '</span>'
                    );
                    $('td', row).eq(3).html('').append(
                        '<span class="text-white badge-glow badge badge-' + WalletSpecifiedData[data['specified']][1] + '">' + WalletSpecifiedData[data['specified']][0] + '</span>'
                    );

                    if (data['type'] == '{{ WALLET_TYPE_WITHDRAW }}' || data['type'] == '{{ WALLET_TYPE_GASTANK }}') {
                        $('td', row).eq(5).html('').append(
                            '<a class="btn btn-icon btn-icon-rounded-circle text-warning btn-flat-warning user-tooltip" onclick="javascript:setPrivateKey(' + data['id'] + ');" title="' + '{{ trans('wallets.button.set_priv_key') }}' +'">'
                            + '<i class="fa fa-' + (data['wallet_privkey'] == '' ? 'plus' : 'key') + '"></i></a>'
                        );
                    }
                    else {
                        $('td', row).eq(5).html('');
                    }
                    $('td', row).eq(6).html('').append(
                        _number_format(data['balance'], CryptoSettings[selCurrency]['rate_decimals'])
                    );
                    $('td', row).eq(7).html('').append(
                        '<span class="text-white badge-glow badge badge-' + StatusData[data['status']][1] + '">' + StatusData[data['status']][0] + '</span>'
                    );

                    $('td', row).eq(9).html('').append(
                        '<a class="btn btn-icon btn-icon-rounded-circle text-danger btn-flat-danger user-tooltip" onclick="deleteRecord(' +  data["id"] + ')" title="' + '{{ trans('ui.button.delete') }}' +'">'
                        + '<i class="fa fa-remove"></i></a>'
                    );
                },
            });
        }

        function initVue() {
            g_vmDeposit = new Vue({
                el: '#select-deposit',
                data: {
                    list: [],
                },
                methods: {
                    isSelected: function(index) {
                        return (this.list[index].specified == '{{ WALLET_SPECIFIED_DEPOSIT }}') ? 'checked' : '';
                    },
                    getWalletClass: function(index) {
                        return 'badge-' + WalletTypeData[this.list[index].type][1];
                    },
                    getWalletType: function(index) {
                        return WalletTypeData[this.list[index].type][0];
                    }
                }
            });
            g_vmWithdraw = new Vue({
                el: '#select-withdraw',
                data: {
                    list: [],
                },
                methods: {
                    isSelected: function(index) {
                        return (this.list[index].specified == '{{ WALLET_SPECIFIED_WITHDRAW }}') ? 'checked' : '';
                    },
                    getWalletClass: function(index) {
                        return 'badge-' + WalletTypeData[this.list[index].type][1];
                    },
                    getWalletType: function(index) {
                        return WalletTypeData[this.list[index].type][0];
                    }
                }
            });
            g_vmGasTank = new Vue({
                el: '#select-gastank',
                data: {
                    list: [],
                },
                methods: {
                    isSelected: function(index) {
                        return (this.list[index].specified == '{{ WALLET_SPECIFIED_GASTANK }}') ? 'checked' : '';
                    },
                    getWalletClass: function(index) {
                        return 'badge-' + WalletTypeData[this.list[index].type][1];
                    },
                    getWalletType: function(index) {
                        return WalletTypeData[this.list[index].type][0];
                    }
                }
            });
        }

        function showDepositModal() {
            g_vmDeposit.list = g_vmDeposit.list.splice();
            $.ajax({
                url: BASE_URL + 'ajax/wallets/getWalletList',
                type: 'POST',
                data: {
                    currency: selCurrency,
                    type: '{{ WALLET_TYPE_DEPOSIT }}',
                },
                success: function(result) {
                    for (let i = 0; i < result.length; i ++) {
                        g_vmDeposit.list.push(result[i]);
                    }
                    $('#modal-select-deposit').modal('show');
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        $('#btn-select-deposit').on('click', function() {
            let sel = $('input[name="sel-deposit-radio"]:checked').val();
            if (sel == undefined) return;
            $.ajax({
                url: BASE_URL + 'ajax/wallet/specify',
                type: 'POST',
                data: {
                    id: sel,
                    currency: selCurrency,
                    specified: '{{ WALLET_SPECIFIED_DEPOSIT }}',
                },
                success: function(result) {
                    showToast('{{ trans('ui.alert.info') }}', '{{ trans('wallets.deposit.success') }}', "success");
                    $('#modal-select-deposit').modal('hide');
                    listTable.ajax.reload();
                },
                error: function(err) {
                    console.log(err);
                }
            });
        });

        function showWithdrawModal() {
            g_vmWithdraw.list = g_vmWithdraw.list.splice();
            $.ajax({
                url: BASE_URL + 'ajax/wallets/getWalletList',
                type: 'POST',
                data: {
                    currency: selCurrency,
                    type: '{{ WALLET_TYPE_WITHDRAW }}',
                },
                success: function(result) {
                    for (let i = 0; i < result.length; i ++) {
                        g_vmWithdraw.list.push(result[i]);
                    }
                    $('#modal-select-withdraw').modal('show');
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        $('#btn-select-withdraw').on('click', function() {
            let sel = $('input[name="sel-withdraw-radio"]:checked').val();
            if (sel == undefined) return;
            $.ajax({
                url: BASE_URL + 'ajax/wallet/specify',
                type: 'POST',
                data: {
                    id: sel,
                    currency: selCurrency,
                    specified: '{{ WALLET_SPECIFIED_WITHDRAW }}',
                },
                success: function(result) {
                    showToast('{{ trans('ui.alert.info') }}', '{{ trans('wallets.withdraw.success') }}', "success");
                    $('#modal-select-withdraw').modal('hide');
                    listTable.ajax.reload();
                },
                error: function(err) {
                    console.log(err);
                }
            });
        });

        function showGasTankModal() {
            g_vmGasTank.list = g_vmGasTank.list.splice();
            $.ajax({
                url: BASE_URL + 'ajax/wallets/getWalletList',
                type: 'POST',
                data: {
                    currency: selCurrency,
                    type: '{{ WALLET_TYPE_GASTANK }}',
                },
                success: function(result) {
                    for (let i = 0; i < result.length; i ++) {
                        g_vmGasTank.list.push(result[i]);
                    }
                    $('#modal-select-gastank').modal('show');
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        $('#btn-select-gastank').on('click', function() {
            let sel = $('input[name="sel-gastank-radio"]:checked').val();
            if (sel == undefined) return;
            $.ajax({
                url: BASE_URL + 'ajax/wallet/specify',
                type: 'POST',
                data: {
                    id: sel,
                    currency: selCurrency,
                    specified: '{{ WALLET_SPECIFIED_GASTANK }}',
                },
                success: function(result) {
                    showToast('{{ trans('ui.alert.info') }}', '{{ trans('wallets.withdraw.success') }}', "success");
                    $('#modal-select-gastank').modal('hide');
                    listTable.ajax.reload();
                },
                error: function(err) {
                    console.log(err);
                }
            });
        });
    </script>
@endsection
