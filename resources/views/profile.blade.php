@extends('layouts.afterlogin')

@section('title', trans('profile.title'))

@section('styles')
	<link rel="stylesheet" href="{{ cAsset('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css') }}">
@endsection

@section('scripts')
	<script src="{{ cAsset('/js/profile.js') }}"></script>
	<script src="{{ cAsset('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js') }}"></script>
@endsection

@section('contents')
	<div class="row">
		<div class="col-lg-12">
			<form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
				@csrf
				@if ($errors->any())
					<div class="card-body">
						<div class="alert alert-danger">
							<ul class="m-0">
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					</div>
				@endif
                @if ($message = Session::get('flash_message'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        {{ trans($message) }}
                    </div>
                @endif

                <div class="card">
					<div class="card-body pb-2">
						<div class="form-group row">
							<label class="col-form-label col-sm-2 text-sm-right"><span class="text-danger">*</span> {{ trans('profile.table.name') }}</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-form-label col-sm-2 text-sm-right"><span class="text-danger">*</span> {{ trans('profile.table.email') }}</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-form-label col-sm-2 text-sm-right"><span class="text-danger">*</span> {{ trans('profile.table.password') }}</label>
							<div class="col-sm-10">
								<input type="password" class="form-control" name="password" value="{{ old('password') }}">
                                <span class="text-danger">{{ trans('profile.message.dont_input') }}</span>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-form-label col-sm-2 text-sm-right"><span class="text-danger">*</span>{{ trans('profile.table.pass_conf') }}</label>
							<div class="col-sm-10">
								<input type="password" class="form-control" name="password_confirmation">
							</div>
						</div>
                        <div class="form-group row">
                            <label class="col-form-label col-sm-2 text-sm-right">{{ trans('profile.table.avatar') }}</label>
                            <div class="col-sm-3">
                                <img id="avatar-img" class="img-preview" src="{{ cUrl('uploads/avatars') . '/' . ((isset($user->avatar) && $user->avatar != '') ? $user->avatar : '_none.png') }}" alt="" class="d-block ui-w-80">
                                <div class="custom-file">
                                    <input type="file" id="avatar" name="avatar" class="custom-file-input data-input">
                                    <label for="avatar" id="avatar-label" class="input-label custom-file-label">{{ isset($user->avatar) ? $user->avatar : '' }}</label>
                                </div>
                                <small id="avatar-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                            </div>
                        </div>
					</div>
					<hr class="border-light m-0">
				</div>

				<div class="text-center mt-3">
					<button type="submit" class="btn btn-primary"><span class="fa fa-save"></span>&nbsp;{{ trans('ui.button.update') }}</button>&nbsp;
				</div>
			</form>
		</div>
	</div>
@endsection
