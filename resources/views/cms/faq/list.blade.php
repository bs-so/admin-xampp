@extends('layouts.afterlogin')

@section('title', trans('faq.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
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
                                <div class="col-md">
                                    <label class="form-label">{{ trans('faq.table.lang') }}</label>
                                    <select id="filter-lang" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('Languages') as $lang => $data)
                                            <option value="{{ $lang }}">{{ trans($data[0]) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('faq.table.category') }}</label>
                                    <select id="filter-category" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach ($categories as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('faq.table.question') }}</label>
                                    <input type="text" id="filter-question" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('faq.table.status') }}</label>
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
        <!-- users filter end -->

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
                    <a type="button" class="text-white btn btn-primary" href="javascript:addFAQ();">
                        <i class="fa fa-plus"></i>&nbsp;{{ trans('ui.button.add') }}
                    </a>
                    <a type="button" class="text-white btn btn-info" href="{{ route('cms.faq_categories') }}">
                        <i class="fa fa-asterisk"></i>&nbsp;{{ trans('faq.button.categories') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="faq-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('faq.table.no') }}</th>
                                <th>{{ trans('faq.table.lang') }}</th>
                                <th>{{ trans('faq.table.question') }}</th>
                                <th>{{ trans('faq.table.answer') }}</th>
                                <th>{{ trans('faq.table.category') }}</th>
                                <th>{{ trans('faq.table.status') }}</th>
                                <th>{{ trans('faq.table.actions') }}</th>
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

    <!-- Add FAQ modal -->
    <div class="modal fade" id="modal-faq">
        <div class="modal-dialog">
            <form id="frm-faq" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modal-title">{{ trans('faq.add_title') }}</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="faq-id" value="0">
					<div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('faq.table.lang') }}</label>
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
                            <label class="form-label">{{ trans('faq.table.category') }}</label>
                            <select id="category" class="form-control mr-sm-2 mb-2 mb-sm-0">
                                @foreach ($categories as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <small id="category-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('faq.table.question') }}</label>
                            <input type="text" id="question" class="form-control mr-sm-2 mb-2 mb-sm-0">
                            <small id="question-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('faq.table.answer') }}</label>
                            <textarea rows="5" id="answer" class="form-control mr-sm-2 mb-2 mb-sm-0"></textarea>
                            <small id="answer-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('faq.table.status') }}</label>
                            <select id="status" class="form-control mr-sm-2 mb-2 mb-sm-0">
                                @foreach (g_enum('StatusData') as $status => $data)
                                    <option value="{{ $status }}">{{ $data[0] }}</option>
                                @endforeach
                            </select>
                            <small id="status-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
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
    <script src="{{ cAsset("js/faq-list.js") }}"></script>

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
                            url: BASE_URL + 'ajax/cms/faq/delete',
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

		$('#lang').on('change', function() {
			let lang = $('#lang').val();
			let category = $('#category').val();
			loadCategories(lang, category);
		});

		function loadCategories(lang, category) {
			$.ajax({
				url: BASE_URL + 'ajax/cms/faq_categories/getAll',
				type: 'POST',
				data: {
					lang: lang,
				},
				success: function(result) {
					let html = '<select id="category" class="form-control mr-sm-2 mb-2 mb-sm-0">';
					for (let id in result)
					{
						html += '<option value="' + id + '" ' + (category == id ? 'selected' : '') + '>' + result[id] + '</option>';
					}
					html += '</select>';
					$('#category').html(html);
				},
				error: function(err) {
					console.log(err);
				},
			});
		}

        function addFAQ() {
			$('#frm-faq').find('select').removeClass('is-invalid');
			$('#frm-faq').find('input').removeClass('is-invalid');
			$('#frm-faq').find('textarea').removeClass('is-invalid');

			$('#lang').val('jp');
			loadCategories('jp', 0);
            $('#question').val('');
            $('#answer').val('');
            $('#status').val('{{ STATUS_ACTIVE }}');
            $('#modal-title').html('{{ trans('faq.add_title') }}');
            $('#faq-id').val(0);

            $('#modal-faq').modal('show');
        }

        function editFAQ(id) {
            $.ajax({
                url: BASE_URL + 'ajax/cms/faq/getInfo',
                type: 'POST',
                data: {
                    id: id,
                },
                success: function(result) {
                    $('#frm-faq').find('select').removeClass('is-invalid');
                    $('#frm-faq').find('input').removeClass('is-invalid');

					$('#lang').val(result['lang']);
					loadCategories(result['lang'], result['category']);
                    $('#question').val(result['question']);
                    $('#answer').val(result['answer']);
                    $('#status').val(result['status']);
                    $('#modal-title').html('{{ trans('faq.edit_title') }}');
                    $('#faq-id').val(result['id']);

                    $('#modal-faq').modal('show');
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        $('#btn-modal-submit').on('click', function() {
            let id = $('#faq-id').val();
            let category = $('#category').val();
			let lang = $('#lang').val();
            let question = $('#question').val();
            let answer = $('#answer').val();
            let status = $('#status').val();

            $.ajax({
                url: BASE_URL + 'ajax/cms/faq/' + ((id == 0) ? 'add' : 'edit'),
                type: 'POST',
                data: {
                    id: id,
                    category: category,
					lang: lang,
                    question: question,
                    answer: answer,
                    status: status,
                },
                success: function(result) {
                    $('#modal-faq').modal('hide');
                    listTable.ajax.reload();
                },
                error: function(err) {
                    var errorMsg = err['responseJSON']['errors'];
                    $('#frm-faq').find('select').removeClass('is-invalid');
                    $('#frm-faq').find('input').removeClass('is-invalid');
					$('#frm-faq').find('textarea').removeClass('is-invalid');

                    if (errorMsg['category'] != null) {
                        $('#category').addClass('is-invalid');
                        document.getElementById('category-error').innerHTML = errorMsg['category'];
                    }
                    if (errorMsg['lang'] != null) {
                        $('#lang').addClass('is-invalid');
                        document.getElementById('lang-error').innerHTML = errorMsg['lang'];
                    }
                    if (errorMsg['question'] != null) {
                        $('#question').addClass('is-invalid');
                        document.getElementById('question-error').innerHTML = errorMsg['question'];
                    }
                    if (errorMsg['answer'] != null) {
                        $('#answer').addClass('is-invalid');
                        document.getElementById('answer-error').innerHTML = errorMsg['answer'];
                    }
                    if (errorMsg['status'] != null) {
                        $('#status').addClass('is-invalid');
                        document.getElementById('status-error').innerHTML = errorMsg['status'];
                    }
                }
            });
        });

        function initTable() {
            listTable = $('#faq-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/cms/faq/search',
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
                    targets: [6],
                    orderable: false,
                    searchable: false
                }],
                columns: [
                    {data: 'id'},
					{data: 'lang'},
                    {data: 'question'},
                    {data: 'answer'},
                    {data: 'category'},
                    {data: 'status'},
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
                        data['answer'].substr(0, 20) + (data['answer'].length > 20 ? '...' : '')
                    );

                    $('td', row).eq(4).html('').append(
                        '<span class="text-white badge-glow badge badge-' + badgeClasses[data['category'] % badgeClasses.length] + '">' + data['category_name'] + '</span>'
                    );
                    $('td', row).eq(5).html('').append(
                        '<span class="text-white badge-glow badge badge-' + StatusData[data['status']][1] + '">' + StatusData[data['status']][0] + '</span>'
                    );

                    $('td', row).eq(6).html('').append(
                        '<a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip" href="javascript:editFAQ(' + data["id"] + ');" title="' + '{{ trans('ui.button.edit') }}' + '">'
                        + '<i class="fa fa-edit"></i></a>' +
                        '<a class="btn btn-icon btn-icon-rounded-circle text-danger btn-flat-danger user-tooltip" onclick="javascript:deleteRecord(' +  data["id"] + ');" title="' + '{{ trans('ui.button.delete') }}' + '">'
                        + '<i class="fa fa-remove"></i></a>'
                    );
                },
            });
        }
    </script>
@endsection
