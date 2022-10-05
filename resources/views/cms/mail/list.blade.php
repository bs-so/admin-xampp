@extends('layouts.afterlogin')

@section('title', trans('mail.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css') }}"
        rel="stylesheet" />
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('contents')
    <!-- users list start -->
    <section class="users-list-wrapper">
        <!-- users filter start -->
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
                                <div class="col-md col-xl-4">
                                    <label class="form-label">{{ trans('mail.table.title') }}</label>
                                    <input type="text" id="filter-title" class="form-control"
                                        placeholder="{{ trans('ui.search.any') }}">
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
        <!-- users filter end -->

        <?php
        echo '<script>
            ';
            echo 'var MailSendTypeData = '.json_encode(g_enum('MailSendTypeData')).
            ';';
            echo '

        </script>';
        ?>

        @if ($message = Session::get('flash_message'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {{ trans($message) }}
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="form-group">
                    <a type="button" class="text-white btn btn-primary" href="javascript:addEvent();">
                        <i class="fa fa-plus"></i>&nbsp;{{ trans('ui.button.create_new') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="mail-list" class="table">
                            <thead>
                                <tr>
                                    <th>{{ trans('mail.table.no') }}</th>
                                    <th>{{ trans('mail.table.title') }}</th>
                                    <th>{{ trans('mail.table.type') }}</th>
                                    <th>{{ trans('mail.table.total') }}</th>
                                    <th>{{ trans('mail.table.success') }}</th>
                                    <th>{{ trans('mail.table.process') }}</th>
                                    <th>{{ trans('mail.table.actions') }}</th>
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

    <!-- Add Event modal -->
    <div class="modal fade" id="modal-mail">
        <div class="modal-dialog">
            <form id="frm-mail" class="modal-content" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modal-title">{{ trans('mail.add_title') }}</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="mail-id" value="0">
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('mail.table.title') }}</label>
                            <input type="text" id="title" class="form-control mr-sm-2 mb-2 mb-sm-0">
                            <small id="title-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>

                    <?php $MailSendTypeData = g_enum('MailSendTypeData'); ?>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('mail.table.type') }}</label>
                            <select id="type" class="form-control mr-sm-2 mb-2 mb-sm-0">
                                @foreach ($MailSendTypeData as $id => $value)
                                    <option value="{{ $id }}">{{ trans($value[0]) }}</option>
                                @endforeach
                            </select>
                            <small id="type-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>

                    <div class="form-row" id="filter-date-container">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('mail.table.search_at') }}</label>
                            <input type="text" id="filter-date" class="form-control"
                                placeholder="{{ trans('ui.search.any') }}">
                            <small id="filter-date-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>

                    <div class="form-row" id="filter-user-csv">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('mail.table.userlist') }}</label>
                            <input type="file" id="user-csv" class="form-control"
                                placeholder="{{ trans('ui.search.any') }}">
                            <small id="user-csv-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>

                    <div class="form-row" id="filter-user-speciallist">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('mail.table.speciallist') }}</label>
                            <input type="text" id="user-speciallist" class="form-control"
                                placeholder="{{ trans('ui.search.sperator') }}">
                            <small id="user-speciallist-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('mail.table.content') }}</label>
                            <textarea id="content" class="summernote" rows="8"></textarea>
                            <small id="content-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i
                            class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                    <button type="button" class="btn btn-success" id="btn-modal-submit"><i
                            class="fa fa-save"></i>&nbsp;{{ trans('ui.button.send') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Add Event modal -->

    <form id="FILE_FORM" method="post" enctype="multipart/form-data" action="">
        @csrf
    </form>
@endsection


@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset('https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js') }}"></script>
    <script src="{{ cAsset('js/mail-list.js') }}"></script>

    <script>
        var isRtl;

        var filterDates = [];
        let badgeClasses = ['primary', 'info', 'success', 'danger', 'warning'];

        let MailSendTypeDataString = [];
        MailSendTypeDataString['mail.usertype.all'] = '{{ trans('mail.usertype.all') }}';
        MailSendTypeDataString['mail.usertype.login'] = '{{ trans('mail.usertype.login') }}';
        MailSendTypeDataString['mail.usertype.reg'] = '{{ trans('mail.usertype.reg') }}';
        MailSendTypeDataString['mail.usertype.csv'] = '{{ trans('mail.usertype.csv') }}';
        MailSendTypeDataString['mail.usertype.special'] = '{{ trans('mail.usertype.special') }}';

        isRtl = $('body').attr('dir') === 'rtl' || $('html').attr('dir') === 'rtl';

        // Date
        $('#filter-date').daterangepicker({
                opens: isRtl ? 'right' : 'left',
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD'
                }
            },
            function(start, end, label) {
                var startDate = moment(start).format('YYYY-MM-DD');
                var endDate = moment(end).format('YYYY-MM-DD');
                filterDates = [startDate, endDate];
            }
        );
        $('#filter-date').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' ~ ' + picker.endDate.format('YYYY-MM-DD'));
        });
        $('#filter-date').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            filterDates = [];
        });

        $('#type').on('change', function() {
            switch ($(this).val()) {
                case '1':
                    $('#filter-date-container').css("display", "none");
                    $('#filter-user-csv').css("display", "none");
                    $('#filter-user-speciallist').css("display", "none");
                    break;
                case '2':
                case '3':
                    $('#filter-date-container').css("display", "block");
                    $('#filter-user-csv').css("display", "none");
                    $('#filter-user-speciallist').css("display", "none");
                    break;
                case '4':
                    $('#filter-user-csv').css("display", "block");
                    $('#filter-date-container').css("display", "none");
                    $('#filter-user-speciallist').css("display", "none");
                    break;
                case '5':
                    $('#filter-date-container').css("display", "none");
                    $('#filter-user-csv').css("display", "none");
                    $('#filter-user-speciallist').css("display", "block");
                    break;
                default:
                break;
            }
        });

        function addEvent() {
            $('#title').val();
            $('#type').val('1');
            $('#content').val();
            $('#filter-date').val();
            $('#modal-title').html('{{ trans('mail.add_title') }}');
            $('#mail-id').val(0);
            $('#filter-date-container').css("display", "none");
            $('#filter-user-csv').css("display", "none");
            $('#filter-user-speciallist').css("display", "none");

            $('#modal-mail').modal('show');
        }

        $('#btn-modal-submit').on('click', function() {
            $.LoadingOverlay("show");

            let id = $('#mail-id').val();
            let title = $('#title').val();
            let type = $('#type').val();
            let content = $('#content').val();
            let userCSV = document.getElementById('user-csv');
            let userSpec = $('#user-speciallist').val();

            let form = $('#FILE_FORM')[0];
            let formData = new FormData(form);
            let csvFile = userCSV.files[0];

            formData.append('id', id);
            formData.append('title', title);
            formData.append('type', type);
            formData.append('content', content);
            formData.append('userSpec', userSpec);
            formData.append('filterDates', filterDates.join(':'));

            if (csvFile != undefined) {
                formData.append('csvFile', csvFile, csvFile.name);
            }

            $.ajax({
                processData: false,
                contentType: false,
                url: BASE_URL + 'ajax/cms/mail/' + ((id == 0) ? 'add' : 'edit'),
                type: 'POST',
                data: formData,
                stateSave: true,
                success: function(result) {
                    $.LoadingOverlay("hide");
                    $('#modal-mail').modal('hide');
                    listTable.ajax.reload();
                },
                error: function(err) {
                    $.LoadingOverlay("hide");
                    var errorMsg = err['responseJSON']['errors'];
                    $('#frm-mail').find('select').removeClass('is-invalid');
                    $('#frm-mail').find('input').removeClass('is-invalid');
                    $('#frm-mail').find('textarea').removeClass('is-invalid');
                    $('#content-error').css('display', 'none');

                    if (errorMsg['title'] != null) {
                        $('#title').addClass('is-invalid');
                        document.getElementById('title-error').innerHTML = errorMsg['title'];
                    }
                    if (errorMsg['filter-date'] != null) {
                        $('#filter-date').addClass('is-invalid');
                        document.getElementById('filter-date-error').innerHTML = errorMsg[
                            'filter-date'];
                    }
                    if (errorMsg['content'] != null) {
                        $('#content').addClass('is-invalid');
                        $('#content-error').css('display', 'block');
                        document.getElementById('content-error').innerHTML = errorMsg['content'];
                    }

                    if (errorMsg['user-csv'] != null) {
                        $('#user-csv').addClass('is-invalid');
                        $('#user-csv-error').css('display', 'block');
                        document.getElementById('user-csv-error').innerHTML = errorMsg['content'];
                    }
                }
            });
        });

        function initTable() {
            listTable = $('#mail-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/cms/mail/search',
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
                    targets: [5],
                    orderable: false,
                    searchable: false
                }],
                processing: false,
                columns: [{
                        data: 'id',
                        width: '5%'
                    },
                    {
                        data: 'title',
                        width: '35%'
                    },
                    {
                        data: 'type'
                    },
                    {
                        data: 'total'
                    },
                    {
                        data: 'success'
                    },
                    {
                        data: null,
                        width: '20%'
                    },
                    {
                        data: null,
                        className: 'text-center'
                    },
                ],
                createdRow: function(row, data, index) {
                    var pageInfo = listTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );
                    $('td', row).eq(1).html('').append(data['title']);
                    $('td', row).eq(2).html('').append(
                        '<span class="text-white badge-glow badge badge-' + MailSendTypeData[data[
                            'type']][1] +
                        '">' + MailSendTypeDataString[MailSendTypeData[data['type']][0]] + '</span>'
                    );

                    $('td', row).eq(3).html('').append(data['total']);
                    $('td', row).eq(4).html('').append(data['success']);

                    percent = (Math.floor((data['success'] / data['total']) * 100)).toString();
                    $('td', row).eq(5).html('').append(
                        '<div class="text-center" id="example-caption-2">進捗&hellip; ' +
                        percent + '%</div>' +
                        '<div class="progress progress-bar-primary"><div class="progress-bar" role="progressbar" aria-valuenow="' +
                        percent + '" aria-valuemin="' + percent +
                        '" aria-valuemax="100" style="width:' + percent +
                        '%" aria-describedby="example-caption-2"></div></div>'
                    );

                    $('td', row).eq(6).html('').append(
                        '<a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip" href="' + BASE_URL + 'cms/mail/detail?id=' +  data["id"] + '" title="' + $('#edit-caption').val() + '">'
                        + '<i class="fa fa-edit"></i></a>'
                    );
                },
                fnDrawCallback: function() {
                    console.log('draw');
                }
            });
        }

    </script>
@endsection
