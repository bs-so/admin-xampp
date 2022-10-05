@extends('layouts.afterlogin')

@section('title', trans('affiliate.settle.add_title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/jstree/themes/default/style.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{ cAsset('app-assets/vendors/css/pickers/pickadate/pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset('app-assets/css/plugins/forms/wizard.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset('app-assets/vendors/css/forms/select/select2.min.css') }}">
@endsection

@section('contents')
    <section id="icon-tabs">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 id="step-title" class="card-title">{{ trans('affiliate.settle.steps_title1') }}</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <form id="form-wizard" action="#" class="icons-tab-steps wizard-circle">
                                <!-- Step 1(Select CSV) -->
                                <h6><i class="step-icon feather icon-slack"></i> {{ trans('affiliate.settle.icon_title1') }}</h6>
                                <fieldset>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="begin_date">{{ trans('affiliate.settle.csv_file') }}</label>
                                                <input type="file" class="form-control format-picker" id="csv_file" accept=".csv">
                                                <small id="csv_file-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="table-responsive">
                                            <table id="csv-list" class="table">
                                                <thead>
                                                <tr>
                                                    <th>{{ trans('affiliate.settle.no') }}</th>
                                                    <th>{{ trans('affiliate.settle.userid') }}</th>
                                                    <th>{{ trans('affiliate.settle.currency') }}</th>
                                                    <th class="text-right">{{ trans('affiliate.settle.amount') }}</th>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </fieldset>

                                <!-- Step 2(Check Entry) -->
                                <h6><i class="step-icon feather icon-slack"></i> {{ trans('affiliate.settle.icon_title2') }}</h6>
                                <fieldset>
                                    <div class="row">
                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="text-primary">{{ trans('affiliate.settle.navs_chk_balance') }}</h3>
                                            </div>
                                            <div class="card-body">
                                                <div id="chk-balance-result"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="text-primary">{{ trans('affiliate.settle.navs_chk_user') }}</h3>
                                            </div>
                                            <div class="card-body">
                                                <div id="chk-user-result"></div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                <!-- Step 3(Calc Commission) -->
                                <h6><i class="step-icon fa fa-gift"></i> {{ trans('affiliate.settle.icon_title3') }}</h6>
                                <fieldset>
                                    <div class="row">
                                        <div class="table-responsive">
                                            <h1 class="text-danger text-white mb-2">{{ trans('affiliate.settle.summary') }}</h1>
                                            <table id="summary-list" class="table">
                                                <thead>
                                                <tr>
                                                    <th>{{ trans('affiliate.commission.currency') }}</th>
                                                    <th>{{ trans('affiliate.commission.balance') }}</th>
                                                    <th>{{ trans('affiliate.commission.total_commission') }}</th>
                                                    <th>{{ trans('affiliate.commission.percent') }}(%)</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="(data, currency) in list">
                                                    <td>@{{ currency }}</td>
                                                    <td>@{{ getFormatted(currency, data.system_balance) }}</td>
                                                    <td>@{{ getFormatted(currency, data.total_commission) }}</td>
                                                    <td>@{{ getFormatted(currency, data.percent, 2) }}</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <h1 class="text-danger text-white mb-2">{{ trans('affiliate.settle.navs_commission') }}</h1>
                                        <div class="table-responsive">
                                            <table id="commission-list" class="table">
                                                <thead>
                                                <tr>
                                                    <th rowspan="2">{{ trans('affiliate.commission.no') }}</th>
                                                    <th rowspan="2">{{ trans('affiliate.commission.userid') }}</th>
                                                    <th rowspan="2">{{ trans('affiliate.commission.nickname') }}</th>
                                                    <th class="text-right" colspan="{{ count($crypto_settings) }}">{{ trans('affiliate.commission.commission_curr') }}</th>
                                                    <th class="text-right" colspan="{{ count($crypto_settings) }}">{{ trans('affiliate.commission.commission_prev') }}</th>
                                                    <th rowspan="2">{{ trans('affiliate.commission.created_at') }}</th>
                                                </tr>
                                                <tr>
                                                    @foreach ($crypto_settings as $currency => $data)
                                                        <th class="text-right">{{ $currency }}</th>
                                                    @endforeach
                                                    @foreach ($crypto_settings as $currency => $data)
                                                        <th class="text-right">{{ $currency }}</th>
                                                    @endforeach
                                                </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </fieldset>

                                <!-- Step 4(Check Balances) -->
                                <h6><i class="step-icon fa fa-money"></i> {{ trans('affiliate.settle.icon_title4') }}</h6>
                                <fieldset>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <div class="table-responsive">
                                                    <table id="balance-list" class="table">
                                                        <thead>
                                                        <th>{{ trans('affiliate.balance.no') }}</th>
                                                        <th>{{ trans('affiliate.balance.userid') }}</th>
                                                        <th>{{ trans('affiliate.balance.nickname') }}</th>
                                                        <th>{{ trans('affiliate.balance.currency') }}</th>
                                                        <th>{{ trans('affiliate.balance.prev_balance') }}</th>
                                                        <th>{{ trans('affiliate.balance.next_balance') }}</th>
                                                        <th>{{ trans('affiliate.balance.created_at') }}</th>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                <!-- Step 5(Check Finish) -->
                                <h6><i class="step-icon feather icon-zap"></i> {{ trans('affiliate.settle.icon_title5') }}</h6>
                                <fieldset>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <fieldset>
                                                    <div class="vs-checkbox-con vs-checkbox-success">
                                                        <input id="use_announce" type="checkbox" checked="checked" value="{{ MAIL_ANNOUNCE_YES }}">
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-mail"></i>
                                                            </span>
                                                        </span>
                                                        <span class="">{{ trans('affiliate.settle.use_announce') }}</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="remark">{{ trans('affiliate.settle.remark') }}</label>
                                                <textarea type="text" class="form-control" id="remark" rows="5"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- users list ends -->

    <form id="FILE_FORM" method="post" enctype="multipart/form-data" action="">
        @csrf
    </form>
@endsection


@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/loadingoverlay/loadingoverlay.min.js') }}"></script>
    <script src="{{ cAsset('vendor/jstree/jstree.min.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset('app-assets/vendors/js/extensions/jquery.steps.min.js') }}"></script>
    <script src="{{ cAsset('app-assets/vendors/js/pickers/pickadate/picker.js') }}"></script>
    <script src="{{ cAsset('app-assets/vendors/js/pickers/pickadate/picker.date.js') }}"></script>
    <script src="{{ cAsset('app-assets/vendors/js/pickers/pickadate/picker.time.js') }}"></script>
    <script src="{{ cAsset('app-assets/vendors/js/pickers/pickadate/legacy.js') }}"></script>
    <script src="{{ cAsset('app-assets/vendors/js/ui/jquery.sticky.js') }}"></script>
    <script src="{{ cAsset('app-assets/vendors/js/forms/select/select2.full.min.js') }}"></script>
    <script src="{{ cAsset("js/affiliate_settle-add.js") }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <?php echo '<script>let SettleStatusData = ' . json_encode(g_enum('SettleStatusData')) . '</script>' ?>
    <?php echo '<script>let SalesStatusData = ' . json_encode(g_enum('SalesStatusData')) . '</script>' ?>
    <?php echo '<script>let EntrySelectStatusData = ' . json_encode(g_enum('EntrySelectStatusData')) . '</script>' ?>
    <script>
        let lastSettleId = '{{ $last_settle_id }}';
        let newSettleId = '{{ $new_settle_id }}';
        let waitCaption = '{{ trans('ui.alert.just_wait') }}';
        let failedCaption = '{{ trans('ui.alert.op_failed') }}';
        let StepTitles = [
            '{{ trans('affiliate.settle.steps_title1') }}',
            '{{ trans('affiliate.settle.steps_title2') }}',
            '{{ trans('affiliate.settle.steps_title3') }}',
            '{{ trans('affiliate.settle.steps_title4') }}',
            '{{ trans('affiliate.settle.steps_title5') }}',
        ];

        let wizard = $(".icons-tab-steps").steps({
            headerTag: "h6",
            bodyTag: "fieldset",
            transitionEffect: "fade",
            autoFocus: true,
            saveState: true,
            titleTemplate: '<span class="step">#index#</span> #title#',
            labels: {
                previous: '{{ trans('ui.button.prev') }}',
                next: '{{ trans('ui.button.next') }}',
                finish: '{{ trans('ui.button.submit') }}'
            },
            onStepChanging: function(event, currentIndex, newIndex) {
                let ret = true;
                switch (currentIndex) {
                    case 0:
                        ret = uploadCSVFile();
                        if (formatError != "") {
                            ret = false;
                        }
                        break;
                    case 1:
                        if (newIndex > 0) {
                            ret = csvStatus;
                            if (csvStatus == false) {
                                showToast('{{ trans('affiliate.settle.csv_file_invalid') }}', '{{ trans('ui.alert.info') }}', 'warning');
                            }
                        }
                        break;
                }
                localStorage.setItem('priorIndex', newIndex);
                return ret;
            },
            onStepChanged: function(event, currentIndex, priorIndex) {
                $('#step-title').html(StepTitles[currentIndex]);
                console.log('current :', priorIndex, '-->', currentIndex);
                localStorage.setItem('currentIndex', currentIndex);
                updateSettleStatus(currentIndex + 1);
                let ret = true;
                switch (currentIndex) {
                    case 0: // Load CSV File
                        break;
                    case 1: // Save CSV File
                        ret = checkCSVData();
                        break;
                    case 2: // Load Prev Tree
                        ret = saveSettleData();
                        break;
                    case 3: // Load Balances
                        ret = saveBalances();
                        break;
                }
                return ret;
            },
            onFinished: function (event, currentIndex) {
                doFinishSettle();
            },
        });
        var currentIndex = localStorage.getItem('currentIndex');
        var priorIndex = localStorage.getItem('priorIndex');

        function forwardSteps() {
            for (var i = 0; i < '{{ $current_status - 3 }}'; i++) {
                wizard.steps('next');
            }
        }

        function loadCSVData() {
            if (csvList != null) {
                csvList.destroy();
            }
            csvList = $('#csv-list').DataTable({
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
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "All"]],
                columnDefs: [],
                data: csvData,
                createdRow: function (row, data, index) {
                    $('td', row).eq(3).addClass('text-right');
                }
            });
        }

        function uploadCSVFile() {
            var input = document.getElementById('csv_file');
            if (!input.files) return false;
            let file = input.files[0];
            if (file == undefined) return false;

            let ret = false;
            var form = $('#FILE_FORM')[0];
            var formData = new FormData(form);

            formData.append("file", file, file.name);

            $.ajax({
                processData: false,
                contentType: false,
                url: BASE_URL + 'ajax/affiliate/settle/uploadCsvFile',
                type: 'POST',
                data: formData,
                success: function(result) {
                    ret = true;
                },
                error: function(err) {
                    console.log(err);
                },
                async: false,
            });

            return ret;
        }

        function checkCSVData() {
            let ret = false;
            let html = "";

            $.ajax({
                url: BASE_URL + 'ajax/affiliate/settle/checkCsvData',
                type: 'POST',
                success: function(result) {
                    html = "";
                    csvStatus = true;
                    for (let currency in result['balance']) {
                        let item = result['balance'][currency];
                        if (item.result == 0) {
                            html += '<strong class="text-success pl-1 mb-0">' + currency + ': ' + '{{ trans('affiliate.settle.no_error') }}' + '</strong>';
                        }
                        else {
                            csvStatus = false;
                            html += '<strong class="text-danger pl-1 mb-0">' + currency + ': ' + '{{ trans('affiliate.settle.error_balance') }}' + '</strong>';
                        }
                        html += '<p class="text-black pl-1 mb-0">' + '{{ trans('affiliate.settle.csv_balance_sum') }}' +
                            _number_format(item.csv_sum, Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[currency]['rate_decimals']))
                            + '</p>';
                        html += '<p class="text-black pl-1 mb-1">' + '{{ trans('affiliate.settle.current_balance') }}' +
                            _number_format(item.balance, Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[currency]['rate_decimals']))
                            + '</p>';
                    }
                    $('#chk-balance-result').html(html);

                    html = "";
                    if (result['user'].length == 0) {
                        html += '<p class="text-success pl-1 mb-1">' + '{{ trans('affiliate.settle.no_error') }}' + '</p>';
                    }
                    else {
                        csvStatus = false;
                        for (let i = 0; i < result['user'].length; i ++) {
                            let item = result['user'][i];
                            html += '<p class="text-danger pl-1 mb-0">' + item + '{{ trans('affiliate.settle.error_user') }}' + '</p>';
                        }
                    }
                    $('#chk-user-result').html(html);
                },
                error: function(err) {
                    console.log(err);
                    ret = false;
                },
                async: false,
            });

            return ret;
        }

        function loadCommission() {
            if (commissionList != null) {
                commissionList.draw();
                return;
            }
            let columns = [];
            columns.push({data: null});
            columns.push({data: 'userid'});
            columns.push({data: 'nickname'});
            for (let currency in CryptoSettings) {
                columns.push({data: 'curr_' + currency, className: "text-right"});
            }
            for (let currency in CryptoSettings) {
                columns.push({data: 'prev_' + currency, className: "text-right"});
            }
            columns.push({data: 'created_at'});

            let targets = [];
            for (let i = 0; i < columns.length; i ++) {
                targets.push(i);
            }

            commissionList = $('#commission-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/affiliate/settle/loadCommission',
                    type: 'POST',
                    data: {
                        settle_status: '{{ ENTRY_SETTLE_STATUS_NONE }}',
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
                columnDefs: [{
                    targets: targets,
                    orderable: false,
                    searchable: false
                }],
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "All"]],
                columns: columns,
                createdRow: function (row, data, index) {
                    var pageInfo = commissionList.page.info();

                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    let danger = false;
                    for (let currency in CryptoSettings) {
                        if (data['curr_' + currency] == data['prev_' + currency]) danger = true;
                    }
                    if (danger) {
                        $('td', row).addClass('bg-danger');
                        $('td', row).addClass('text-white');
                    }
                },
            });
        }

        function loadBalances() {
            if (balanceList != null) {
                balanceList.draw();
                return;
            }
            balanceList = $('#balance-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/affiliate/settle/loadBalances',
                    type: 'POST',
                    data: {
                        settle_status: '{{ ENTRY_SETTLE_STATUS_NONE }}',
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
                columnDefs: [],
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "All"]],
                columns: [
                    {data: 'id'},
                    {data: 'userid'},
                    {data: 'user_name'},
                    {data: 'currency'},
                    {data: 'prev_balance', class: 'text-right'},
                    {data: 'next_balance', class: 'text-right'},
                    {data: 'created_at'},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = balanceList.page.info();

                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(4).html('').append(
                        _number_format(data['prev_balance'], Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[data['currency']]['rate_decimals']))
                    );
                    $('td', row).eq(5).html('').append(
                        _number_format(data['next_balance'], Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[data['currency']]['rate_decimals']))
                    );
                },
            });
        }

        function doFinishSettle() {
            let ret = false;
            let use_announce = $('#use_announce').prop('checked');
            let remark = $('#remark').val();

            $.ajax({
                url: BASE_URL + 'ajax/affiliate/settle/finishSettle',
                type: 'POST',
                data: {
                    use_announce: use_announce == true ? 1 : 0,
                    remark: remark,
                },
                success: function() {
                    document.location.href = '{{ route('affiliate.settle') }}';
                },
                error: function(err) {
                    console.log(err);
                },
            });

            return ret;
        }

        function setEndDate() {
        }

        function readCSV(input) {
            if (!input.files) return;
            let file = input.files[0];
            if (file == undefined) return;

            formatError = "";
            var reader = new FileReader();
            reader.readAsText(file);
            csvData = [];

            reader.onload = function(event) {
                var csv = event.target.result;
                var rows = csv.split("\r\n");
                var itemCount = 3;

                for (let i = 1; i < rows.length; i++) {
                    var cols = rows[i].split('"');
                    if (cols.length <= 1) break;
                    let datas = [i];
                    if (cols.length != itemCount * 2 + 1) {
                        // format error
                        let error = '{{ trans('affiliate.settle.format_error1') }}' + i + '{{ trans('affiliate.settle.format_error2') }}' + "<br>";
                        formatError += error;
                    }
                    for (let j = 0; j < itemCount * 2 + 1; j ++) {
                        let val = (j >= cols.length) ? '{{ trans('affiliate.settle.no_item') }}' : cols[j];
                        if (val != "" && val != "," && datas.length < itemCount + 1) {
                            datas.push(val);
                        }
                    }

                    csvData.push(datas);
                }

                if (formatError == "") {
                    showToast('{{ trans('affiliate.settle.no_format_error') }}', '{{ trans('ui.alert.info') }}', 'success');
                }
                else {
                    showToast(formatError, '{{ trans('ui.alert.info') }}', 'warning');
                }

                loadCSVData();
            }
        }

        function initVue() {
            g_vmSummaryList = new Vue({
                el: '#summary-list',
                data: {
                    list: [],
                },
                methods: {
                    getFormatted: function(currency, value, decimals = 0) {
                        if (decimals == 0) {
                            return _number_format(value, CryptoSettings[currency]['rate_decimals']);
                        }
                        else {
                            return _number_format(value, 2);
                        }
                    }
                }
            });
        }
    </script>
@endsection
