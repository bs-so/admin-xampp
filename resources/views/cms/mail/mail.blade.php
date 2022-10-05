@extends('layouts.afterlogin')

@section('title', trans('mail.detailtitle'))

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
            <div class="card-content collapse show">
                <div class="card-body" id="mail-block">
                    <div class="col-md" style="position: relative; z-index: 10; padding:20px">
                        @include('emails.mail')
                    </div>
                </div>
            </div>
        </div>

        <h2>{{ trans('mail.userlist') }}</h2>
        <ul class="trader_mail_list">
        @foreach($users as $index => $trader)
            <li>{{ $trader->userid }}</li>
        @endforeach
        </ul>

        <div class="mt-5">{{ sprintf(trans('mail.message.created_at'), $created_at)}}</div>
        <!-- users filter end -->

        <a type="button" class="btn btn-success mt-3 text-white" href="{{ route('cms.mail') }}"><i
                            class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.back') }}</a>
    </section>
    <!-- users list ends -->
    
@endsection


@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset('https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js') }}"></script>

@endsection
