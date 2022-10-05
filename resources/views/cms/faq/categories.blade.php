@extends('layouts.afterlogin')

@section('title', trans('faq_category.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('contents')
    <!-- users list start -->
    <section class="users-list-wrapper">
        <?php
            echo '<script>';
            echo 'var StatusData = ' . json_encode(g_enum('StatusData')) . ';';
            echo 'var Languages = ' . json_encode(g_enum('Languages')) . ';';
            echo '</script>';
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
                    <a type="button" class="text-white btn btn-primary" href="javascript:addFAQCategory();">
                        <i class="fa fa-plus"></i>&nbsp;{{ trans('ui.button.add') }}
                    </a>
                    <a type="button" class="text-white btn btn-secondary" href="{{ route('cms.faq') }}">
                        <i class="fa fa-arrow-left"></i>&nbsp;{{ trans('ui.button.back') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="category-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('faq_category.table.no') }}</th>
                                <th>{{ trans('faq_category.table.lang') }}</th>
                                <th>{{ trans('faq_category.table.name') }}</th>
                                <th>{{ trans('faq_category.table.actions') }}</th>
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

    <!-- Add FAQ Category modal -->
    <div class="modal fade" id="modal-category">
        <div class="modal-dialog">
            <form id="frm-category" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modal-title">{{ trans('faq_category.add_title') }}</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="category-id" value="0">
					<div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('faq_category.table.lang') }}</label>
                            <select id="lang" class="form-control mr-sm-2 mb-2 mb-sm-0">
                                @foreach (g_enum('Languages') as $id => $data)
                                    <option value="{{ $id }}">{{ trans($data[0]) }}</option>
                                @endforeach
                            </select>
                            <small id="lang-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('faq_category.table.name') }}</label>
                            <input type="text" id="name" class="form-control mr-sm-2 mb-2 mb-sm-0">
                            <small id="name-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                    <button type="button" class="btn btn-success" id="btn-modal-submit"><i class="fa fa-save"></i>&nbsp;{{ trans('ui.button.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Add FAQ modal -->
@endsection


@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset("js/faq_category-list.js") }}"></script>

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
                            url: BASE_URL + 'ajax/cms/faq_categories/delete',
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

        function addFAQCategory() {
			$('#frm-category').find('select').removeClass('is-invalid');
			$('#frm-category').find('input').removeClass('is-invalid');

			$('#lang').val('jp');
			$('#name').val('');
            $('#modal-title').html('{{ trans('faq_category.add_title') }}');
            $('#category-id').val(0);

            $('#modal-category').modal('show');
        }

        function editFAQCategory(id) {
            $.ajax({
                url: BASE_URL + 'ajax/cms/faq_categories/getInfo',
                type: 'POST',
                data: {
                    id: id,
                },
                success: function(result) {
                    $('#frm-category').find('select').removeClass('is-invalid');
                    $('#frm-category').find('input').removeClass('is-invalid');

					$('#lang').val(result['lang']);
					$('#name').val(result['name']);
                    $('#modal-title').html('{{ trans('faq_category.edit_title') }}');
                    $('#category-id').val(result['id']);

                    $('#modal-category').modal('show');
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        $('#btn-modal-submit').on('click', function() {
            let id = $('#category-id').val();
			let lang = $('#lang').val();
			let name = $('#name').val();

            $.ajax({
                url: BASE_URL + 'ajax/cms/faq_categories/' + ((id == 0) ? 'add' : 'edit'),
                type: 'POST',
                data: {
                    id: id,
					lang: lang,
					name: name,
                },
                success: function(result) {
                    $('#modal-category').modal('hide');
                    listTable.ajax.reload();
                },
                error: function(err) {
                    var errorMsg = err['responseJSON']['errors'];
                    $('#frm-category').find('select').removeClass('is-invalid');
                    $('#frm-category').find('input').removeClass('is-invalid');

                    if (errorMsg['lang'] != null) {
                        $('#lang').addClass('is-invalid');
                        document.getElementById('lang-error').innerHTML = errorMsg['lang'];
                    }
                    if (errorMsg['name'] != null) {
                        $('#name').addClass('is-invalid');
                    }
                }
            });
        });

        function initTable() {
            listTable = $('#category-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/cms/faq_categories/search',
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
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "{{ trans('ui.table.all') }}"]],
                columnDefs: [{
                    targets: [3],
                    orderable: false,
                    searchable: false
                }],
                columns: [
                    {data: 'id'},
					{data: 'lang'},
					{data: 'name'},
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
						Languages[data['lang']][3]
					);

                    $('td', row).eq(3).html('').append(
                        '<a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip" href="javascript:editFAQCategory(' + data["id"] + ');" title="' + '{{ trans('ui.button.edit') }}' + '">'
                        + '<i class="fa fa-edit"></i></a>' +
                        '<a class="btn btn-icon btn-icon-rounded-circle text-danger btn-flat-danger user-tooltip" onclick="javascript:deleteRecord(' +  data["id"] + ');" title="' + '{{ trans('ui.button.delete') }}' + '">'
                        + '<i class="fa fa-remove"></i></a>'
                    );
                },
            });
        }
    </script>
@endsection
