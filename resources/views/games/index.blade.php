@extends('layouts.afterlogin')

@section('title', trans('games.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ cAsset('app-assets/vendors/css/ui/prism.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset('app-assets/vendors/css/file-uploaders/dropzone.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ cAsset('app-assets/css/plugins/file-uploaders/dropzone.css') }}">
    <link href="{{ cAsset('app-assets/css/plugins/photoswipe/photoswipe.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ cAsset('app-assets/vendors/css/forms/select/select2.min.css') }}">
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
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#nav-games" aria-controls="games"
                                role="tab" aria-selected="false">{{ trans('games.nav.games') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#nav-category" aria-controls="category" role="tab"
                                aria-selected="false">{{ trans('games.nav.category') }}</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="nav-games" aria-labelledby="nav-games" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <a href="javascript:addGame();" class="text-white btn btn-primary"><i class="fa fa-plus"></i>&nbsp;{{ trans('ui.button.add') }}</a>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="games-list" class="table">
                                                <thead>
                                                <tr>
                                                    <th>{{ trans('games.games.no') }}</th>
                                                    <th>{{ trans('games.games.name') }}</th>
                                                    <th>{{ trans('games.games.category') }}</th>
                                                    <th>{{ trans('games.games.main_img') }}</th>
                                                    <th>{{ trans('games.games.mobile_img_jp') }}</th>
                                                    <th>{{ trans('games.games.mobile_img_en') }}</th>
                                                    <th>{{ trans('games.games.desc_img1_jp') }}</th>
                                                    <th>{{ trans('games.games.desc_img2_jp') }}</th>
                                                    <th>{{ trans('games.games.desc_img1_en') }}</th>
                                                    <th>{{ trans('games.games.desc_img2_en') }}</th>
                                                    <th>{{ trans('games.games.video_img') }}</th>
                                                    <th>{{ trans('games.games.video') }}</th>
                                                    <th>{{ trans('games.games.actions') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="nav-category" aria-labelledby="nav-category" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <a href="javascript:addCategory();" class="text-white btn btn-primary"><i class="fa fa-plus"></i>&nbsp;{{ trans('ui.button.add') }}</a>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="category-list" class="table">
                                                <thead>
                                                <tr>
                                                    <th>{{ trans('games.category.no') }}</th>
                                                    <th>{{ sprintf(trans('games.category.name'), g_enum('Languages')[App::getLocale()][3]) }}</th>
                                                    <th>{{ trans('games.category.status') }}</th>
                                                    <th>{{ trans('games.category.created_at') }}</th>
                                                    <th>{{ trans('games.category.actions') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Add game modal -->
    <div class="modal fade" id="modal-game">
        <div class="modal-dialog">
            <form id="frm-game" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modal-game-title">{{ trans('games.games.add_title') }}</span>
                        <br>
                        <small id="modal-game-title" class="text-muted">{{ trans('games.games.subtitle') }}</small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="game-id" value="0">
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('games.games.name') }}</label>
                            <input type="text" id="game-name" class="form-control mr-sm-2 mb-2 mb-sm-0">
                            <small id="game-name-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('games.games.category') }}</label>
                            <ul class="list-unstyled mb-0">
                                @foreach ($categories as $id => $name)
                                <li class="d-inline-block mr-2">
                                    <fieldset>
                                        <div class="vs-checkbox-con vs-checkbox-primary">
                                            <input type="checkbox" id="game-category-{{ $id }}" name="game-category" value="{{ $id }}">
                                            <span class="vs-checkbox">
                                                <span class="vs-checkbox--check">
                                                    <i class="vs-icon feather icon-check"></i>
                                                </span>
                                            </span>
                                            <span class="">{{ $name }}</span>
                                        </div>
                                    </fieldset>
                                </li>
                                @endforeach
                            </ul>
                            <small id="game-category-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('games.games.main_img') }}</label>
                            <div class="row">
                                <div class="col-sm-4">
                                    <img id="main_img-img" class="img-preview" src="{{ cAsset('uploads/games/main/_none.png') }}" alt="" class="d-block ui-w-80">
                                </div>
                                <div class="col-sm-8">
                                    <div class="custom-file">
                                        <input type="file" id="main_img" class="custom-file-input data-input">
                                        <label for="main_img" id="main_img-label" class="input-label custom-file-label">{{ trans('ui.button.browse') }}</label>
                                    </div>
                                    <small id="main_img-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('games.games.mobile_img_jp') }}</label>
                            <div class="row">
                                <div class="col-sm-4">
                                    <img id="mobile_img_jp-img" class="img-preview" src="{{ cAsset('uploads/games/main/_none.png') }}" alt="" class="d-block ui-w-80">
                                </div>
                                <div class="col-sm-8">
                                    <div class="custom-file">
                                        <input type="file" id="mobile_img_jp" class="custom-file-input data-input">
                                        <label for="mobile_img_jp" id="mobile_img_jp-label" class="input-label custom-file-label">{{ trans('ui.button.browse') }}</label>
                                    </div>
                                    <small id="mobile_img_jp-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('games.games.mobile_img_en') }}</label>
                            <div class="row">
                                <div class="col-sm-4">
                                    <img id="mobile_img_en-img" class="img-preview" src="{{ cAsset('uploads/games/main/_none.png') }}" alt="" class="d-block ui-w-80">
                                </div>
                                <div class="col-sm-8">
                                    <div class="custom-file">
                                        <input type="file" id="mobile_img_en" class="custom-file-input data-input">
                                        <label for="mobile_img_en" id="mobile_img_en-label" class="input-label custom-file-label">{{ trans('ui.button.browse') }}</label>
                                    </div>
                                    <small id="mobile_img_en-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('games.games.desc_img1_jp') }}</label>
                            <div class="row">
                                <div class="col-sm-4">
                                    <img id="desc_img1_jp-img" class="img-preview" src="{{ cAsset('uploads/games/main/_none.png') }}" alt="" class="d-block ui-w-80">
                                </div>
                                <div class="col-sm-8">
                                    <div class="custom-file">
                                        <input type="file" id="desc_img1_jp" class="custom-file-input data-input">
                                        <label for="desc_img1_jp" id="desc_img1_jp-label" class="input-label custom-file-label">{{ trans('ui.button.browse') }}</label>
                                    </div>
                                    <small id="desc_img1_jp-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('games.games.desc_img2_jp') }}</label>
                            <div class="row">
                                <div class="col-sm-4">
                                    <img id="desc_img2_jp-img" class="img-preview" src="{{ cAsset('uploads/games/main/_none.png') }}" alt="" class="d-block ui-w-80">
                                </div>
                                <div class="col-sm-8">
                                    <div class="custom-file">
                                        <input type="file" id="desc_img2_jp" class="custom-file-input data-input">
                                        <label for="desc_img2_jp" id="desc_img2_jp-label" class="input-label custom-file-label">{{ trans('ui.button.browse') }}</label>
                                    </div>
                                    <small id="desc_img2_jp-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('games.games.desc_img1_en') }}</label>
                            <div class="row">
                                <div class="col-sm-4">
                                    <img id="desc_img1_en-img" class="img-preview" src="{{ cAsset('uploads/games/main/_none.png') }}" alt="" class="d-block ui-w-80">
                                </div>
                                <div class="col-sm-8">
                                    <div class="custom-file">
                                        <input type="file" id="desc_img1_en" class="custom-file-input data-input">
                                        <label for="desc_img1_en" id="desc_img1en-label" class="input-label custom-file-label">{{ trans('ui.button.browse') }}</label>
                                    </div>
                                    <small id="desc_img1_en-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('games.games.desc_img2_en') }}</label>
                            <div class="row">
                                <div class="col-sm-4">
                                    <img id="desc_img2_en-img" class="img-preview" src="{{ cAsset('uploads/games/main/_none.png') }}" alt="" class="d-block ui-w-80">
                                </div>
                                <div class="col-sm-8">
                                    <div class="custom-file">
                                        <input type="file" id="desc_img2_en" class="custom-file-input data-input">
                                        <label for="desc_img2_en" id="desc_img2_en-label" class="input-label custom-file-label">{{ trans('ui.button.browse') }}</label>
                                    </div>
                                    <small id="desc_img2_en-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('games.games.video_img') }}</label>
                            <div class="row">
                                <div class="col-sm-4">
                                    <img id="video_img-img" class="img-preview" src="{{ cAsset('uploads/games/video/_none.png') }}" alt="" class="d-block ui-w-80">
                                </div>
                                <div class="col-sm-8">
                                    <div class="custom-file">
                                        <input type="file" id="video_img" class="custom-file-input data-input">
                                        <label for="video_img" id="video_img-label" class="input-label custom-file-label">{{ trans('ui.button.browse') }}</label>
                                    </div>
                                    <small id="video_img-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('games.games.video') }}</label>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="custom-file">
                                        <input type="file" id="video" class="custom-file-input data-input">
                                        <label for="video" id="video-label" class="input-label custom-file-label">{{ trans('ui.button.browse') }}</label>
                                    </div>
                                    <small id="video-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i
                            class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                    <button type="button" class="btn btn-success" id="btn-game-submit"><i
                            class="fa fa-save"></i>&nbsp;{{ trans('ui.button.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Add game modal -->

    <!-- Add category modal -->
    <div class="modal fade" id="modal-category">
        <div class="modal-dialog">
            <form id="frm-category" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modal-category-title">{{ trans('games.category.add_title') }}</span>
                        <br>
                        <small id="modal-category-title" class="text-muted">{{ trans('games.category.subtitle') }}</small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="category-id" value="0">
                    @foreach (g_enum('Languages') as $lang => $lang_data)
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ sprintf(trans('games.category.name'), $lang_data[3]) }}</label>
                            <input type="text" id="category-name-{{ $lang }}" class="form-control mr-sm-2 mb-2 mb-sm-0">
                            <small id="category-name-{{ $lang }}-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                    @endforeach
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('games.category.status') }}</label>
                            <select id="category-status" class="form-control mr-sm-2 mb-2 mb-sm-0">
                                @foreach (g_enum('StatusData') as $status => $data)
                                    <option value="{{ $status }}">{{ $data[0] }}</option>
                                @endforeach
                            </select>
                            <small id="category-status-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i
                            class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                    <button type="button" class="btn btn-success" id="btn-category-submit"><i
                            class="fa fa-save"></i>&nbsp;{{ trans('ui.button.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Add category modal -->

    <!-- Show video modal -->
    <div class="modal fade" id="modal-video">
        <div class="modal-dialog">
            <form id="frm-video" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col">
                            <video id="video-preview" style="width: 100%;" muted playsinline>
                                <source src="">
                            </video>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i
                            class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Show video modal -->

    <form id="FILE_FORM" method="post" enctype="multipart/form-data" action="">
        @csrf
    </form>
@endsection

@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset('https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js') }}"></script>
    <script src="{{ cAsset('app-assets/vendors/js/forms/select/select2.full.min.js') }}"></script>
    <script src="{{ cAsset('app-assets/vendors/js/extensions/dropzone.min.js') }}"></script>
    <script src="{{ cAsset('app-assets/vendors/js/ui/prism.min.js') }}"></script>
    <script src="{{ cAsset('js/games-list.js') }}"></script>
    <script src="{{ cAsset('vendor/photoswipe/jquery.photoswipe.js') }}"></script>

    <?php echo '<script>let StatusData = '.json_encode(g_enum('StatusData')).';</script>';?>
    <?php echo '<script>let Languages = '.json_encode(g_enum('Languages')).';</script>';?>
    <script>
        function addGame() {
            $('#modal-game-title').html('{{ trans('games.games.add_title') }}');
            $('#game-id').val(0);
            $('#game-name').val('');
            $('[name="game-category"]').prop('checked', false);
            $('#game-category').val('');
            $('#main_img').val('');
            $('#mobile_img_jp').val('');
            $('#mobile_img_en').val('');
            $('#desc_img1_jp').val('');
            $('#desc_img2_jp').val('');
            $('#desc_img1_en').val('');
            $('#desc_img2_en').val('');
            $('#video_img').val('');
            $('#video').val('');
            $('#main_img-label').html('');
            $('#mobile_img_jp-label').html('');
            $('#mobile_img_en-label').html('');
            $('#desc_img1_jp-label').html('');
            $('#desc_img2_jp-label').html('');
            $('#desc_img1_en-label').html('');
            $('#desc_img2_en-label').html('');
            $('#video_img-label').html('');
            $('#video-label').html('');
            $('#main_img-img').attr('src', '{{ cAsset('uploads/games/main/_none.png') }}');
            $('#mobile_img_jp-img').attr('src', '{{ cAsset('uploads/games/mobile/_none.png') }}');
            $('#mobile_img_en-img').attr('src', '{{ cAsset('uploads/games/mobile/_none.png') }}');
            $('#desc_img1_jp-img').attr('src', '{{ cAsset('uploads/games/desc/_none.png') }}');
            $('#desc_img2_jp-img').attr('src', '{{ cAsset('uploads/games/desc/_none.png') }}');
            $('#desc_img1_en-img').attr('src', '{{ cAsset('uploads/games/desc/_none.png') }}');
            $('#desc_img2_en-img').attr('src', '{{ cAsset('uploads/games/desc/_none.png') }}');
            $('#video_img-img').attr('src', '{{ cAsset('uploads/games/video/_none.png') }}');

            $('#frm-game').find('select').removeClass('is-invalid');
            $('#frm-game').find('input').removeClass('is-invalid');
            $('#main_img-error').hide();
            $('#mobile_img_jp-error').hide();
            $('#mobile_img_en-error').hide();
            $('#desc_img1_jp-error').hide();
            $('#desc_img2_jp-error').hide();
            $('#desc_img1_en-error').hide();
            $('#desc_img2_en-error').hide();
            $('#video_img-error').hide();
            $('#video-error').hide();

            $('#modal-game').modal('show');
        }

        function editGame(id) {
            $.ajax({
                url: BASE_URL + 'ajax/games/getInfo',
                type: 'POST',
                data: {
                    id: id,
                },
                success: function(result) {
                    $('#modal-game-title').html('{{ trans('games.games.edit_title') }}');
                    $('#game-id').val(id);
                    $('#game-name').val(result['name']);
                    let category = JSON.parse(result['category']);
                    for (let index in category) {
                        $('#game-category-' + category[index]).prop('checked', true);
                    }
                    $('#main_img-label').html('{{ trans('ui.button.browse') }}');
                    $('#mobile_img_jp-label').html('{{ trans('ui.button.browse') }}');
                    $('#mobile_img_en-label').html('{{ trans('ui.button.browse') }}');
                    $('#desc_img1_jp-label').html('{{ trans('ui.button.browse') }}');
                    $('#desc_img2_jp-label').html('{{ trans('ui.button.browse') }}');
                    $('#desc_img1_en-label').html('{{ trans('ui.button.browse') }}');
                    $('#desc_img2_en-label').html('{{ trans('ui.button.browse') }}');
                    $('#video_img-label').html('{{ trans('ui.button.browse') }}');
                    $('#video-label').html(result['video']);
                    $('#main_img-img').attr('src', result['main_img']);
                    $('#mobile_img_jp-img').attr('src', result['mobile_img_jp']);
                    $('#mobile_img_en-img').attr('src', result['mobile_img_en']);
                    $('#desc_img1_jp-img').attr('src', result['desc_img1_jp']);
                    $('#desc_img2_jp-img').attr('src', result['desc_img2_jp']);
                    $('#desc_img1_en-img').attr('src', result['desc_img1_en']);
                    $('#desc_img2_en-img').attr('src', result['desc_img2_en']);
                    $('#video_img-img').attr('src', result['video_img']);

                    $('#frm-game').find('select').removeClass('is-invalid');
                    $('#frm-game').find('input').removeClass('is-invalid');
                    $('#main_img-error').hide();
                    $('#mobile_img_jp-error').hide();
                    $('#mobile_img_en-error').hide();
                    $('#desc_img1_jp-error').hide();
                    $('#desc_img2_jp-error').hide();
                    $('#desc_img1_en-error').hide();
                    $('#desc_img2_en-error').hide();
                    $('#video_img-error').hide();
                    $('#video-error').hide();

                    $('#modal-game').modal('show');
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        function deleteGame(id) {
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
                callback: function (result) {
                    if (result) {
                        $.ajax({
                            url: BASE_URL + 'ajax/games/delete',
                            type: 'POST',
                            data: {
                                id: id,
                            },
                            success: function() {
                                showToast('{{ trans('games.games.op_success') }}', '{{ trans('ui.alert.info') }}', 'success');
                                gamesList.ajax.reload();
                            },
                            error: function(err) {
                                showToast('{{ trans('games.games.op_failed') }}', '{{ trans('ui.alert.info') }}', 'warning');
                                console.log(err);
                            }
                        });
                    }
                }
            });
        }

        $('#btn-game-submit').on('click', function() {
            let id = $('#game-id').val();
            let name = $('#game-name').val();
            let objs = $('[name="game-category"]:checked');
            let category = [];
            for (let index = 0; index < objs.length; index ++) {
                category.push($(objs[index]).val());
            }

            let form = $('#FILE_FORM')[0];
            let formData = new FormData(form);
            let nowMoment = moment().valueOf();

            formData.append('now', nowMoment);
            formData.append('id', id);
            formData.append('name', name);
            formData.append('category', category.length == 0 ? '': JSON.stringify(category));
            let main_img = document.getElementById('main_img');
            let file = main_img.files[0];
            if (file != undefined) formData.append("main_img", file, file.name);
            let mobile_img_jp = document.getElementById('mobile_img_jp');
            file = mobile_img_jp.files[0];
            if (file != undefined) formData.append("mobile_img_jp", file, file.name);
            let mobile_img_en = document.getElementById('mobile_img_en');
            file = mobile_img_en.files[0];
            if (file != undefined) formData.append("mobile_img_en", file, file.name);
            let desc_img1_jp = document.getElementById('desc_img1_jp');
            file = desc_img1_jp.files[0];
            if (file != undefined) formData.append("desc_img1_jp", file, file.name);
            let desc_img2_jp = document.getElementById('desc_img2_jp');
            file = desc_img2_jp.files[0];
            if (file != undefined) formData.append("desc_img2_jp", file, file.name);

            let desc_img1_en = document.getElementById('desc_img1_en');
            file = desc_img1_en.files[0];
            if (file != undefined) formData.append("desc_img1_en", file, file.name);
            let desc_img2_en = document.getElementById('desc_img2_en');
            file = desc_img2_en.files[0];
            if (file != undefined) formData.append("desc_img2_en", file, file.name);

            let video_img = document.getElementById('video_img');
            file = video_img.files[0];
            if (file != undefined) formData.append("video_img", file, file.name);
            let video = document.getElementById('video');
            file = video.files[0];
            if (file != undefined) formData.append("video", file, file.name);

            $.ajax({
                processData: false,
                contentType: false,
                url: BASE_URL + 'ajax/games/' + (id == 0 ? 'add' : 'edit'),
                type: 'POST',
                data: formData,
                success: function(result) {
                    showToast('{{ trans('games.games.op_success') }}', '{{ trans('ui.alert.info') }}', 'success');
                    $('#modal-game').modal('hide');
                    gamesList.ajax.reload();
                },
                error: function(err) {
                    showToast('{{ trans('games.games.op_failed') }}', '{{ trans('ui.alert.info') }}', 'warning');
                    var errorMsg = err['responseJSON']['errors'];
                    $('#frm-game').find('select').removeClass('is-invalid');
                    $('#frm-game').find('input').removeClass('is-invalid');
                    $('#main_img-error').hide();
                    $('#mobile_img_jp-error').hide();
                    $('#mobile_img_en-error').hide();
                    $('#desc_img1_jp-error').hide();
                    $('#desc_img2_jp-error').hide();
                    $('#desc_img1_en-error').hide();
                    $('#desc_img2_en-error').hide();
                    $('#video_img-error').hide();
                    $('#video-error').hide();

                    if (errorMsg['name'] != null) {
                        $('#game-name').addClass('is-invalid');
                    }
                    if (errorMsg['category'] != null) {
                        $('#game-category').addClass('is-invalid');
                    }
                    if (errorMsg['main_img'] != null) {
                        $('#main_img').addClass('is-invalid');
                        $('#main_img-error').show();
                    }
                    if (errorMsg['mobile_img_jp'] != null) {
                        $('#mobile_img_jp').addClass('is-invalid');
                        $('#mobile_img_jp-error').show();
                    }
                    if (errorMsg['mobile_img_en'] != null) {
                        $('#mobile_img_en').addClass('is-invalid');
                        $('#mobile_img_en-error').show();
                    }
                    if (errorMsg['desc_img1_jp'] != null) {
                        $('#desc_img1_jp').addClass('is-invalid');
                        $('#desc_img1_jp-error').show();
                    }
                    if (errorMsg['desc_img2_jp'] != null) {
                        $('#desc_img2_jp').addClass('is-invalid');
                        $('#desc_img2_jp-error').show();
                    }
                    if (errorMsg['desc_img1_en'] != null) {
                        $('#desc_img1_en').addClass('is-invalid');
                        $('#desc_img1_en-error').show();
                    }
                    if (errorMsg['desc_img2_en'] != null) {
                        $('#desc_img2_en').addClass('is-invalid');
                        $('#desc_img2_en-error').show();
                    }
                    if (errorMsg['video_img'] != null) {
                        $('#video_img').addClass('is-invalid');
                        $('#video_img').show();
                    }
                    if (errorMsg['video'] != null) {
                        $('#video').addClass('is-invalid');
                        $('#video-error').show();
                    }
                }
            });
        });

        function addCategory() {
            $('#modal-category-title').html('{{ trans('games.category.add_title') }}');
            for (let lang in Languages) {
                $('#category-name-' + lang).val('');
            }
            $('#category-status').val('{{ STATUS_ACTIVE }}');
            $('#category-id').val(0);

            $('#modal-category').modal('show');
        }

        function editCategory(id) {
            $.ajax({
                url: BASE_URL + 'ajax/games/category/getInfo',
                type: 'POST',
                data: {
                    id: id,
                },
                success: function(result) {
                    $('#modal-category-title').html('{{ trans('games.category.edit_title') }}');
                    for (let lang in Languages) {
                        let name = result['names'][lang];
                        $('#category-name-' + lang).val(name == undefined ? '' : name);
                    }
                    $('#category-status').val(result['status']);
                    $('#category-id').val(result['id']);

                    $('#modal-category').modal('show');
                },
                error: function(err) {
                    console.log(err);
                }
            })
        }

        function deleteCategory(id) {
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
                callback: function (result) {
                    if (result) {
                        $.ajax({
                            url: BASE_URL + 'ajax/games/category/delete',
                            type: 'POST',
                            data: {
                                id: id,
                            },
                            success: function() {
                                showToast('{{ trans('games.category.op_success') }}', '{{ trans('ui.alert.info') }}', 'success');
                                categoryList.ajax.reload();
                            },
                            error: function(err) {
                                showToast('{{ trans('games.category.op_failed') }}', '{{ trans('ui.alert.info') }}', 'warning');
                                console.log(err);
                            }
                        });
                    }
                }
            });
        }

        $('#btn-category-submit').on('click', function() {
            let data = {};
            let edit_id = $('#category-id').val();

            for (let lang in Languages) {
                let val = $('#category-name-' + lang).val();
                data[lang] = val;
            }
            data['status'] = $('#category-status').val();
            data['id'] = edit_id;

            $.ajax({
                url: BASE_URL + 'ajax/games/category/' + (edit_id == 0 ? 'add' : 'edit'),
                type: 'POST',
                data: data,
                success: function(result) {
                    $('#modal-category').modal('hide');
                    categoryList.ajax.reload();
                },
                error: function(err) {
                    var errorMsg = err['responseJSON']['errors'];
                    $('#frm-category').find('select').removeClass('is-invalid');
                    $('#frm-category').find('input').removeClass('is-invalid');

                    for (let lang in Languages) {
                        if (errorMsg[lang] != null) {
                            $('#category-name-' + lang).addClass('is-invalid');
                        }
                    }
                    if (errorMsg['status'] != null) {
                        $('#category-status').addClass('is-invalid');
                    }
                }
            })
        });

        function showVideo(url) {
            $('#video-preview').attr('src', url);
            $('#modal-video').modal('show');
            document.getElementById('video-preview').play();
        }

        function initTable() {
            gamesList = $('#games-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/games/search',
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
                    targets: [12],
                    orderable: false,
                    searchable: false
                }],
                columns: [
                    {data: 'id'},
                    {data: 'name'},
                    {data: 'category_name'},
                    {data: 'main_img'},
                    {data: 'mobile_img_jp'},
                    {data: 'mobile_img_en'},
                    {data: 'desc_img1_jp'},
                    {data: 'desc_img2_jp'},
                    {data: 'desc_img1_en'},
                    {data: 'desc_img2_en'},
                    {data: 'video_img'},
                    {data: 'video'},
                    {data: null},
                ],
                createdRow: function(row, data, index) {
                    var pageInfo = gamesList.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    let category = '';
                    for (let i = 0; i < data['category_name'].length; i ++) {
                        category += '<span class="text-white badge badge-glow badge-primary mb-1">' + data['category_name'][i] + '</span>';
                        if (i < data['category_name'].length - 1) category += '<br>';
                    }
                    $('td', row).eq(2).html('').append(category);

                    $('td', row).eq(3).html('').append(
                        '<a href="' + data['main_img'] + '" class="swipe" rel="group' + index.toString() +
                        '"><img class="main-img-item slide-item" src="' + data['main_img'] + '"></a>'
                    );
                    $('td', row).eq(4).html('').append(
                        '<a href="' + data['mobile_img_jp'] + '" class="swipe" rel="group' + index.toString() +
                        '"><img class="main-img-item slide-item" src="' + data['mobile_img_jp'] + '"></a>'
                    );
                    $('td', row).eq(5).html('').append(
                        '<a href="' + data['mobile_img_en'] + '" class="swipe" rel="group' + index.toString() +
                        '"><img class="main-img-item slide-item" src="' + data['mobile_img_en'] + '"></a>'
                    );
                    $('td', row).eq(6).html('').append(
                        '<a href="' + data['desc_img1_jp'] + '" class="swipe" rel="group' + index.toString() +
                        '"><img class="main-img-item slide-item" src="' + data['desc_img1_jp'] + '"></a>'
                    );
                    $('td', row).eq(7).html('').append(
                        '<a href="' + data['desc_img2_jp'] + '" class="swipe" rel="group' + index.toString() +
                        '"><img class="main-img-item slide-item" src="' + data['desc_img2_jp'] + '"></a>'
                    );
                    $('td', row).eq(8).html('').append(
                        '<a href="' + data['desc_img1_en'] + '" class="swipe" rel="group' + index.toString() +
                        '"><img class="main-img-item slide-item" src="' + data['desc_img1_en'] + '"></a>'
                    );
                    $('td', row).eq(9).html('').append(
                        '<a href="' + data['desc_img2_en'] + '" class="swipe" rel="group' + index.toString() +
                        '"><img class="main-img-item slide-item" src="' + data['desc_img2_en'] + '"></a>'
                    );
                    $('td', row).eq(10).html('').append(
                        '<a href="' + data['video_img'] + '" class="swipe" rel="group' + index.toString() +
                        '"><img class="main-img-item slide-item" src="' + data['video_img'] + '"></a>'
                    );

                    $('td', row).eq(11).html('').append(
                        '<a href="javascript:showVideo(' + "'" + data['video'] + "'" + ')">' +
                        '<video class="main-img-item slide-item" muted playsinline><source src="' + data['video'] + '"></video>' + '</a>'
                    );

                    $('td', row).eq(12).html('').append(
                        '<a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip" href="javascript:editGame(' +
                        data["id"] + ');" title="' + '{{ trans('ui.button.edit') }}' + '">' +
                        '<i class="fa fa-edit"></i></a>' +
                        '<a class="btn btn-icon btn-icon-rounded-circle text-danger btn-flat-danger user-tooltip" onclick="javascript:deleteGame(' +
                        data["id"] + ');" title="' + '{{ trans('ui.button.delete') }}' + '">' +
                        '<i class="fa fa-remove"></i></a>'
                    );
                },
                fnDrawCallback: function() {
                    $('a.swipe').photoSwipe();
                }
            });

            categoryList = $('#category-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/games/category/search',
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
                    targets: [1],
                    orderable: false,
                    searchable: false
                }],
                columns: [
                    {data: 'id'},
                    {data: 'name'},
                    {data: 'status'},
                    {data: 'created_at'},
                    {data: null},
                ],
                createdRow: function(row, data, index) {
                    var pageInfo = categoryList.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(2).html('').append(
                        '<span class="text-white badge-glow badge badge-' + StatusData[data['status']][1] + '">' + StatusData[data['status']][0] + '</span>'
                    );

                    $('td', row).eq(4).html('').append(
                        '<a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip" href="javascript:editCategory(' +
                        data["id"] + ');" title="' + '{{ trans('ui.button.edit') }}' + '">' +
                        '<i class="fa fa-edit"></i></a>' +
                        '<a class="btn btn-icon btn-icon-rounded-circle text-danger btn-flat-danger user-tooltip" onclick="javascript:deleteCategory(' +
                        data["id"] + ');" title="' + '{{ trans('ui.button.delete') }}' + '">' +
                        '<i class="fa fa-remove"></i></a>'
                    );
                },
            });
        }
    </script>
@endsection
