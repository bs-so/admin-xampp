@extends('layouts.afterlogin')

@section('title', trans('notify.title'))

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ cAsset('app-assets/css/plugins/colorpicker/colorpicker.css') }}">
@endsection

@section('contents')
	@if ($message = Session::get('message'))
		<div class="alert alert-success alert-dismissible fade show">
			<button type="button" class="close" data-dismiss="alert">×</button>
			{{ trans($message) }}
		</div>
	@endif
	@if ($message = Session::get('message_color'))
		<div class="alert alert-success alert-dismissible fade show">
			<button type="button" class="close" data-dismiss="alert">×</button>
			{{ trans($message) }}
		</div>
	@endif
	<!-- users list start -->
    <section class="users-list-wrapper">
        <!-- users filter start -->
        <div class="card">
            <div class="card-content collapse show">
                <div class="card-body">
                    <div class="users-list-filter">
                        @if ($errors->has('message'))
                            <div id="password-error" class="error text-danger pl-3" style="display: block;">
                                <strong>{{ $errors->first('password') }}</strong>
                            </div>
                        @endif
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ Session::has('message_color') ? '' : 'active' }}" id="notify-tab"
                                    data-toggle="tab" href="#notify" aria-controls="notify" role="tab"
                                    aria-selected="true">{{ trans('notify.tab.notify') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Session::has('message_color') ? 'active' : '' }}" id="color-tab"
                                    data-toggle="tab" href="#color" aria-controls="color" role="tab"
                                    aria-selected="false">{{ trans('notify.tab.color') }}</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane {{ Session::has('message_color') ? '' : 'active' }}" id="notify"
                                aria-labelledby="notify-tab" role="tabpanel">
                                <form action="{{ route('cms.notify_modify') }}" method="POST">
                                    {{ csrf_field() }}
                                    <div class="form-group row">
                                        <label class="col-form-label col-sm-3 text-sm-right">
                                            {{ trans('notify.lang') }}
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
                                            {{ trans('notify.content') }}
                                        </label>
                                        <div class="col-sm-8">
                                            <textarea name="content" id="content" class="form-control">{{ $content[$lang] }}</textarea>
                                        </div>
                                    </div>

                                    <button type="submit"
                                        class="btn btn-success mt-3">{{ trans('ui.button.submit') }}</button>
                                </form>
                            </div>
                            <div class="tab-pane  {{ Session::has('message_color') ? 'active' : '' }}" id="color"
                                aria-labelledby="color-tab" role="tabpanel">
                                <form action="{{ route('cms.notify_modifycolor') }}" method="POST">
                                    {{ csrf_field() }}
                                    <h4>{{ trans('notify.color.event') }}</h4>
                                    <input class="form-control mb-1" id="color1" name="color1" type="text"
                                        value="{{ $color1 }}" readonly />
                                    <p id="colorpickerHolder1" name="colorpickerHolder1"></p>
                                    <hr>

                                    <h4>{{ trans('notify.color.casino') }}</h4>
                                    <input class="form-control mb-1" id="color2" name="color2" type="text"
                                        value="{{ $color2 }}" readonly />
                                    <p id="colorpickerHolder2" name="colorpickerHolder2"></p>
                                    <hr>

                                    <h4>{{ trans('notify.color.wallet') }}</h4>
                                    <input class="form-control mb-1" id="color3" name="color3" type="text"
                                        value="{{ $color3 }}" readonly />
                                    <p id="colorpickerHolder3" name="colorpickerHolder3"></p>
                                    <button type="submit"
                                        class="btn btn-success mt-3">{{ trans('ui.button.submit') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- users filter end -->
    </section>
    <!-- users list ends -->

@endsection


@section('scripts')
    <?php echo '<script>let Contents='.json_encode($content).';</script>';?>
    <script>
        var jq = $.noConflict(true);
        jq(document).ready(function() {
            jq('#lang').on('change', function () {
                lang = jq(this).val();
                jq('#content').val(Contents[lang]);
            });
        });
    </script>

    <script src=" {{ cAsset('js/colorpicker/jquery.js') }}"></script>
    <script src="{{ cAsset('js/colorpicker/colorpicker.js') }}"></script>
    <script src="{{ cAsset('js/colorpicker/eye.js') }}"></script>
    <script src="{{ cAsset('js/colorpicker/utils.js') }}"></script>
    <script src="{{ cAsset('js/colorpicker/layout.js?ver=1.0.2') }}"></script>
@endsection
