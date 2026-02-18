@extends('admin.admin')

@section('content')
	<div class="page-wrapper">
		<div class="content bg-white">
			<div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
				<div class="my-auto mb-2">
					<h3 class="page-title mb-1">{{ __('tax_options') }}</h3>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
							</li>
							<li class="breadcrumb-item">
								<a href="javascript:void(0);">{{ __('Settings') }}</a>
							</li>
							<li class="breadcrumb-item active" aria-current="page">{{ __('tax_options') }}</li>
						</ol>
					</nav>
				</div>
			</div>
			@php $isVisible = 0; @endphp
            @if(isset($permission))
                @if(hasPermission($permission, 'General Settings', 'delete'))
                    @php $delete = 1; $isVisible = 1; @endphp
                @else
                    @php $delete = 0; @endphp
                @endif
                @if(hasPermission($permission, 'General Settings', 'edit'))
                    @php $edit = 1; $isVisible = 1; @endphp
                @else
                    @php $edit = 0; @endphp
                @endif
                <div id="has_permission" data-delete="{{ $delete }}" data-edit="{{ $edit }}" data-visible="{{ $isVisible }}"></div>
            @else
            <div id="has_permission" data-delete="1" data-edit="1"></div>
            @endif
			<div class="row">
				@include('admin.partials.general_settings_side_menu')
				<div class="col-xxl-10 col-xl-9">
					<div class="ps-1">
						<div class="d-flex align-items-center justify-content-between flex-wrap border-bottom pt-3 mb-3">
							<div class="mb-3">
								<h5 class="mb-1">{{ __('tax_options') }}</h5>
							</div>
							<div class="mb-3">
                                @if(isset($permission))
                                    @if(hasPermission($permission, 'General Settings', 'create'))
										<div class="skeleton label-skeleton label-loader"></div>
                                        <a href="#" class="btn btn-primary d-none real-label" id="add_tax_btn" data-bs-toggle="modal" data-bs-target="#add_tax_rate"><i class="ti ti-square-rounded-plus-filled me-2"></i>{{ __('add_tax_rate') }}</a>
                                    @endif
                                @endif
							</div>
						</div>
						<div class="card">
							<div class="card-body p-0 py-3">
								<div class="custom-datatable-filter table-responsive">
									<table id="loader-table" class="table table-bordered">
										<thead class="thead-light">
											<tr>
												<th>
													<div class="skeleton label-skeleton label-loader"></div>
												</th>
												<th>
													<div class="skeleton label-skeleton label-loader"></div>
												</th>
												<th>
													<div class="skeleton label-skeleton label-loader"></div>
												</th>
												<th>
													<div class="skeleton label-skeleton label-loader"></div>
												</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
											</tr>
											<tr>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
											</tr>
											<tr>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
											</tr>
											<tr>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
											</tr>
											<tr>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
												<td>
													<div class="skeleton data-skeleton data-loader"></div>
												</td>
											</tr>
										</tbody>
									</table>
									<table class="table d-none" id="tax_option_table">
										<thead class="thead-light">
											<tr>
												<th>{{ __('tax_type') }}</th>
												<th>{{ __('tax_rate') }}</th>
                                                @if ($edit == 1)
                                                <th>{{ __('Status') }}</th>
												@endif
												@if ($isVisible == 1)
												<th class="no-sort">{{ __('Action') }}</th>
												@endif
											</tr>
										</thead>
										<tbody class="tax_option_list"></tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="add_tax_rate">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title tax_modal_title" data-add_tax_text="{{ __('add_tax_rate') }}" data-edit_tax_text="{{ __('edit_tax_rate') }}" >{{ __('add_tax_rate') }}</h4>
					<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
						<i class="ti ti-x"></i>
					</button>
				</div>
				<form id="addTaxRateForm">
					<div class="modal-body">
						<div class="row">
							<div class="col-md-12">
								<input type="hidden" name="group_id" id="group_id" value="3">
								<input type="hidden" name="method" id="method">
								<input type="hidden" name="tax_type_id" id="tax_type_id">
								<input type="hidden" name="tax_rate_id" id="tax_rate_id">
								<div class="mb-3">
									<label class="form-label">{{ __('tax_type') }}<span class="text-danger"> *</span></label>
									<input type="text" class="form-control" id="tax_type" name="tax_type" placeholder="{{ __('enter_tax_type') }}">
									<span class="text-danger error-text" id="tax_type_error"></span>
								</div>
								<div class="mb-3">
									<label class="form-label">{{ __('tax_rate') }} (%)<span class="text-danger"> *</span></label>
									<input type="text" class="form-control" id="tax_rate" name="tax_rate" placeholder="{{ __('enter_tax_rate') }}">
									<span class="text-danger error-text" id="tax_rate_error"></span>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
						<button type="submit" class="btn btn-primary tax_options_btn" data-save_text="{{ __('Save') }}">{{ __('Save') }}</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="modal fade" id="tax_delete_modal">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<form id="deleteTaxForm">
					<div class="modal-body text-center">
						<span class="delete-icon">
							<i class="ti ti-trash-x"></i>
						</span>
						<h4>{{ __('Confirm Deletion') }}</h4>
						<p>{{ __('You want to delete all the marked items, this cant be undone once you delete.') }}</p>
						<input type="hidden" name="del_tax_type" id="del_tax_type">
						<input type="hidden" name="del_tax_rate" id="del_tax_rate">
						<input type="hidden" name="del_tax_status" id="del_tax_status">
						<div class="d-flex justify-content-center">
							<a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
							<button type="submit" class="btn btn-danger delete_tax_option">{{ __('Delete') }}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection