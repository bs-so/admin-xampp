@extends('layouts.afterlogin')

@section('title', sprintf(trans('staff.edit_title'), $staff->login_id))

@section('styles')
	<link href="{{ cAsset('https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css') }}" rel="stylesheet">
	<link rel="stylesheet" href="{{ cAsset('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css') }}">
	<link rel="stylesheet" href="{{ cAsset('/vendor/libs/select2/select2.css') }}">

	<style>
		.dataTables_filter {
			display:none;
		}
	</style>
@endsection

@section('scripts')
	<script src="{{ cAsset('/js/staff-edit.js') }}"></script>
	<script src="{{ cAsset('https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ cAsset('/vendor/libs/select2/select2.js') }}"></script>
	<script src="{{ cAsset('/vendor/libs/moment/moment.js') }}"></script>
	<script src="{{ cAsset('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js') }}"></script>
@endsection

@section('contents')
	<div class="row">
		<div class="col-lg-12">
			<form method="post" action="{{ route('staff.post.edit') }}" enctype="multipart/form-data">
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
				<div class="card">
					<div class="card-body pb-2">
						<input type="hidden" name="id" value="{{ $staff->id }}">
						<div class="form-group row">
							<label class="col-form-label col-sm-2 text-sm-right"><span class="text-danger">*</span> {{ trans('staff.table.login_id') }}</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="login_id" value="{{ old('login_id', $staff->login_id) }}">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-form-label col-sm-2 text-sm-right"><span class="text-danger">*</span> {{ trans('staff.table.name') }}</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="name" value="{{ old('name', $staff->name) }}">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-form-label col-sm-2 text-sm-right"> {{ trans('staff.table.email') }}</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="email" value="{{ old('email', $staff->email) }}">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-form-label col-sm-2 text-sm-right"><span class="text-danger">*</span> {{ trans('staff.table.password') }}</label>
							<div class="col-sm-10">
								<input type="password" class="form-control" name="password" value="{{ old('password') }}">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-form-label col-sm-2 text-sm-right"><span class="text-danger">*</span>{{ trans('staff.table.pass_conf') }}</label>
							<div class="col-sm-10">
								<input type="password" class="form-control" name="password_confirmation">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-form-label col-sm-2 text-sm-right"><span class="text-danger">*</span> {{ trans('staff.table.role') }}</label>
							<div class="col-sm-10">
                                <?php $me = Auth::user(); ?>
								<select name="role" class="form-control">
									@foreach (g_enum('UserRoleData') as $role => $value)
                                        @if ($role >= $me->role)
										<option value="{{ $role }}" {{ ($staff->role == $role) ? 'selected' : '' }}>{{ $value[0] }}</option>
                                        @endif
									@endforeach
								</select>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-form-label col-sm-2 text-sm-right"><span class="text-danger">*</span> {{ trans('staff.table.status') }}</label>
							<div class="col-sm-10">
								<select name="status" class="form-control">
									@foreach (g_enum('StatusData') as $status => $value)
										<option value="{{ $status }}" {{ ($staff->status == $status) ? 'selected' : '' }}>{{ $value[0] }}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
					<hr class="border-light m-0">
				</div>

				<div class="text-center mt-3">
					<button type="submit" class="btn btn-primary"><span class="fa fa-save"></span>&nbsp;{{ trans('ui.button.update') }}</button>&nbsp;
					<a class="btn btn-light" href="{{ route('staff') }}"><span class="fa fa-arrow-left"></span>&nbsp;{{ trans('ui.button.back') }}</a>
				</div>
			</form>
		</div>
	</div>
@endsection
