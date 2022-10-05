@extends('layouts.afterlogin')

@section('title', trans('event.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('app-assets/css/plugins/photoswipe/photoswipe.css') }}" rel="stylesheet">
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
                                <div class="col-md">
                                    <label class="form-label">{{ trans('event.table.title') }}</label>
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
            echo 'var StatusData = '.json_encode(g_enum('EventShowTypeData')).
            ';';
            echo 'var LangData = '.json_encode(g_enum('Languages')).
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
                        <i class="fa fa-plus"></i>&nbsp;{{ trans('ui.button.add') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="event-list" class="table">
                            <thead>
                                <tr>
                                    <th>{{ trans('event.table.no') }}</th>
                                    <th>{{ trans('event.table.title') }}</th>
                                    <th>{{ trans('event.table.img_main') }}</th>
                                    <th>{{ trans('event.table.img_slide') }}</th>
                                    <th>{{ trans('event.table.lang') }}</th>
                                    <th>{{ trans('event.table.status') }}</th>
                                    <th>{{ trans('event.table.actions') }}</th>
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
    <div class="modal fade" id="modal-event">
        <div class="modal-dialog">
            <form id="frm-event" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modal-title">{{ trans('event.add_title') }}</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="event-id" value="0">
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('event.table.title') }}</label>
                            <input type="text" id="title" class="form-control mr-sm-2 mb-2 mb-sm-0">
                            <small id="title-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>

                    <?php $languages = g_enum('Languages'); ?>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('event.table.lang') }}</label>
                            <select id="lang" class="form-control mr-sm-2 mb-2 mb-sm-0">
                                @foreach ($languages as $id => $value)
                                    <option value="{{ $id }}">{{ trans($value[0]) }}</option>
                                @endforeach
                            </select>
                            <small id="lang-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('event.table.slideimg') }}</label>
                            <input type="file" class="dis-none form-control" name="img-slide" id="img-slide">
                            <a class="mr-2 my-25" href="#">
                                <img src="{{ cAsset('app-assets/images/pages/no_slider.png') }}" alt="users img-slide"
                                    class="users-avatar-shadow rounded" id="slideimagePreview">
                            </a>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('event.table.mainimg') }}</label>
                            <input type="file" class="dis-none form-control" name="img-main" id="img-main">
                            <a class="mr-2 my-25" href="#">
                                <img src="{{ cAsset('app-assets/images/pages/no_main.png') }}" alt="users img-main"
                                    class="users-avatar-shadow rounded" id="mainimagePreview">
                            </a>
                        </div>
                    </div>

                    <?php $eventType = g_enum('EventShowTypeData'); ?>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('event.table.status') }}</label>
                            <select id="status" class="form-control mr-sm-2 mb-2 mb-sm-0">
                                @foreach ($eventType as $id => $value)
                                    <option value="{{ $id }}">{{ $value[0] }}</option>
                                @endforeach
                            </select>
                            <small id="status-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
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
    <form id="FILE_FORM" method="post" enctype="multipart/form-data" action="">
        @csrf
    </form>
    <!-- / Add Event modal -->
@endsection


@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('js/event-list.js') }}"></script>
    <script src="{{ cAsset('vendor/photoswipe/jquery.photoswipe.js') }}"></script>

    <script>
        let badgeClasses = ['primary', 'info', 'success', 'danger', 'warning'];

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
                            url: BASE_URL + 'ajax/cms/event/delete',
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

        function addEvent() {
            $('#title').val('');
            $('#lang').val('jp');
            $('#status').val('1');
            $('#modal-title').html('{{ trans('event.add_title') }}');
            $('#slideimagePreview').attr("src", "{{ cAsset('app-assets/images/pages/no_slider.png') }}");
            $('#mainimagePreview').attr("src", "{{ cAsset('app-assets/images/pages/no_main.png') }}");
            $('#img-main').val('');
            $('#img-slide').val('');
            $('#event-id').val(0);

            $('#modal-event').modal('show');
        }

        function editEvent(id) {
            $.ajax({
                url: BASE_URL + 'ajax/cms/event/getInfo',
                type: 'POST',
                data: {
                    id: id,
                },
                success: function(result) {
                    $('#title').val(result['title']);
                    $('#lang').val(result['lang']);
                    $('#status').val(result['status']);
                    $('#slideimagePreview').attr("src", result['img_slide']);
                    $('#mainimagePreview').attr("src", result['img_main']);
                    $('#img-main').val('');
                    $('#img-slide').val('');
                    $('#modal-title').val('{{ trans('event.edit_title') }}');
                    $('#event-id').val(result['id']);

                    $('#modal-event').modal('show');
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        $('#btn-modal-submit').on('click', function() {
            $.LoadingOverlay("show");

            let id = $('#event-id').val();
            let title = $('#title').val();
            let lang = $('#lang').val();
            let status = $('#status').val();
            let mainImage = document.getElementById('img-main');
            let slideImage = document.getElementById('img-slide');
            let form = $('#FILE_FORM')[0];
            let formData = new FormData(form);

            let mainFile = mainImage.files[0];
            let slideFile = slideImage.files[0];

            formData.append('id', id);
            formData.append('title', title);
            formData.append('lang', lang);
            formData.append('status', status);

            if (mainFile != undefined) {
                formData.append('mainImage', mainFile, mainFile.name);
            }

            if (slideFile != undefined) {
                formData.append('slideImage', slideFile, slideFile.name);
            }

            $.ajax({
                processData: false,
                contentType: false,
                url: BASE_URL + 'ajax/cms/event/' + ((id == 0) ? 'add' : 'edit'),
                type: 'POST',
                data: formData,
                success: function(result) {
                    $.LoadingOverlay("hide");
                    $('#modal-event').modal('hide');
                    listTable.ajax.reload();
                },
                error: function(err) {
                    $.LoadingOverlay("hide");
                    var errorMsg = err['responseJSON']['errors'];
                    $('#frm-event').find('select').removeClass('is-invalid');
                    $('#frm-event').find('input').removeClass('is-invalid');

                    if (errorMsg['title'] != null) {
                        $('#title').addClass('is-invalid');
                        document.getElementById('title-error').innerHTML = errorMsg['title'];
                    }
                }
            });
        });

        function initTable() {
            listTable = $('#event-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/cms/event/search',
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
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: null
                    },
                    {
                        data: null
                    },
                    {
                        data: 'lang'
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

                    $('td', row).eq(1).html('').append(data['title']);

                    $('td', row).eq(2).html('').append(
                        '<a href="' + data['img_main'] +
                        '" class="swipe" rel="group' + index.toString() +
                        '"><img class="main-img-item slide-item" src="' + data[
                            'img_main'] + '"></a>'
                    );

                    $('td', row).eq(3).html('').append(
                        '<a href="' + data['img_slide'] +
                        '" class="swipe" rel="group' + index.toString() +
                        '"><img class="main-img-item slide-item" src="' + data[
                            'img_slide'] + '"></a>'
                    );

                    $('td', row).eq(4).html('').append(
                        '<span class="text-white badge-glow badge badge-' + LangData[data[
                            'lang']][2] +
                        '">' + LangData[data['lang']][3] + '</span>'
                    );
                    $('td', row).eq(5).html('').append(
                        '<span class="text-white badge-glow badge badge-' + StatusData[data['status']][1] +
                        '">' + StatusData[data['status']][0] + '</span>'
                    );

                    $('td', row).eq(6).html('').append(
                        '<a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip" href="javascript:editEvent(' +
                        data["id"] + ');" title="' + '{{ trans('ui.button.edit') }}' + '">' +
                        '<i class="fa fa-edit"></i></a>' +
                        '<a class="btn btn-icon btn-icon-rounded-circle text-danger btn-flat-danger user-tooltip" onclick="javascript:deleteRecord(' +
                        data["id"] + ');" title="' + '{{ trans('ui.button.delete') }}' + '">' +
                        '<i class="fa fa-remove"></i></a>'
                    );
                },
                fnDrawCallback: function() {
                    $('a.swipe').photoSwipe();
                }
            });
        }

    </script>
@endsection
