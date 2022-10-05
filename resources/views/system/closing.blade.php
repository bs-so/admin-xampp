@extends('layouts.afterlogin')

@section('title', trans('closing.title'))

@section('styles')
    <link rel="stylesheet" href="{{ cAsset('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css') }}">

    <style>
        .datepicker {
            padding: 4px;
            -webkit-border-radius: 4px;
            -moz-border-radius: 4px;
            border-radius: 4px;
            direction: ltr;
            z-index: 15 !important;
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
            @if ($message = Session::get('error_message'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    {{ trans($message) }}
                </div>
            @endif
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <form method="post" action="{{ route('closing.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card">
                            <div class="card-body pb-2">
                                <div class="form-group row">
                                    <label class="col-form-label col-sm-3 text-sm-right">{{ trans('closing.table.start_at') }}</label>
                                    <div class="col-sm-3">
                                        <input type="text" class="form-control @error('start_at_date') is-invalid @enderror"
                                            id="start_at_date" name="start_at_date" value="{{ old('start_at_date', (isset($setting->start_at) ? substr($setting->start_at, 0, 10) : '')) }}">
                                    </div>
                                    <div class="col-sm-3">
                                        <input type="text" class="form-control @error('start_at_time') is-invalid @enderror"
                                            id="start_at_time" name="start_at_time" value="{{ old('start_at_time', (isset($setting->start_at) ? substr($setting->start_at, 11, 5) : '')) }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-sm-3 text-sm-right">{{ trans('closing.table.finish_at') }}</label>
                                    <div class="col-sm-3">
                                        <input type="text" class="form-control @error('finish_at_date') is-invalid @enderror"
                                            id="finish_at_date" name="finish_at_date" value="{{ old('finish_at_date', (isset($setting->finish_at) ? substr($setting->finish_at, 0, 10) : '')) }}">
                                    </div>
                                    <div class="col-sm-3">
                                        <input type="text" class="form-control @error('finish_at_time') is-invalid @enderror"
                                            id="finish_at_time" name="finish_at_time" value="{{ old('finish_at_time', (isset($setting->finish_at) ? substr($setting->finish_at, 11, 5) : '')) }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-sm-3 text-sm-right">{{ trans('closing.table.status') }}</label>
                                    <div class="col-sm-9">
                                        <div class="custom-control custom-switch custom-switch-success mr-2 mb-1">
                                            <p class="mb-0" id="status-caption">
                                                {{ (!isset($setting->status) || $setting->status == STATUS_ACTIVE) ? trans('closing.status.opened') : trans('closing.status.closed') }}
                                            </p>
                                            <input type="checkbox" class="custom-control-input" id="status" name="status"
                                                   {{ (!isset($setting->status) || $setting->status == STATUS_ACTIVE) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="status">
                                                <span class="switch-icon-left"><i class="fa fa-check"></i></span>
                                                <span class="switch-icon-right"><i class="fa fa-times"></i></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <hr class="border-light m-0">
                            </div>
                            <div class="text-center mt-1 mb-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-save"></i>&nbsp;{{ trans('ui.button.update') }}
                                </button>&nbsp;
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ cAsset('js/closing-index.js') }}"></script>

    <script>
        $('#status').on('change', function() {
            let status = $('#status').prop('checked');
            if (status == true) {
                // Opened
                $('#status-caption').html('{{ trans('closing.status.opened') }}');
            }
            else {
                // Closed
                $('#status-caption').html('{{ trans('closing.status.closed') }}');
            }
        });
    </script>
@endsection
