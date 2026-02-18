@extends('admin.admin')

@section('content')
	<div class="page-wrapper">
		<div class="content bg-white">
			<div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
				<div class="my-auto mb-2">
					<h3 class="page-title mb-1">{{ __('sitemap_settings') }}</h3>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
							</li>
							<li class="breadcrumb-item">
								<a href="javascript:void(0);">{{ __('Settings') }}</a>
							</li>
							<li class="breadcrumb-item active" aria-current="page">{{ __('sitemap_settings') }}</li>
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
                <div id="has_permission" data-delete="{{ $delete }}" data-visible="{{ $isVisible }}"></div>
            @else
            <div id="has_permission" data-delete="1"></div>
            @endif
			<div class="row">
				@include('admin.partials.general_settings_side_menu')
				<div class="col-xxl-10 col-xl-9">
					<div class="ps-1">
						<div class="d-flex align-items-center justify-content-between flex-wrap border-bottom pt-3 mb-3">
							<div class="mb-3">
								<h5 class="mb-1">{{ __('sitemap_settings') }}</h5>
							</div>
							<div class="mb-3">
                                @if(isset($permission))
                                    @if(hasPermission($permission, 'General Settings', 'create'))
										<div class="skeleton label-skeleton label-loader"></div>
                                        <a href="#" class="btn btn-primary d-none real-label" id="add_sitemap_btn" data-bs-toggle="modal" data-bs-target="#add_sitemap"><i class="ti ti-square-rounded-plus-filled me-2"></i>{{ __('add_sitemap') }}</a>
                                    @endif
                                @endif
							</div>
						</div>
						<div class="card">
							<div class="card-body p-0 py-3">
								<div class="custom-datatable-filter table-responsive">
									<table class="table table-bordered loader-table">
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
											</tr>
										</tbody>
									</table>
									<table class="table d-none real-table" id="sitemapTable">
										<thead class="thead-light">
											<tr>
												<th>{{ __('url') }}</th>
												<th>{{ __('file_name') }}</th>
												@if ($isVisible == 1)
												<th class="no-sort">{{ __('Action') }}</th>
												@endif
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

    	<div class="modal fade" id="add_sitemap">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" data-add_tax_text="{{ __('add_sitemap') }}">{{ __('add_sitemap') }}</h4>
					<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
						<i class="ti ti-x"></i>
					</button>
				</div>
				<form id="sitemapForm">
					<div class="modal-body">
						<div class="row">
							<div class="col-md-12">
								<div class="mb-3">
									<label class="form-label">{{ __('sitemap_url') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="url" id="url">
                                    <span id="url_error" class="text-danger error-text" data-required="{{ __('url_required') }}" data-valid_url="{{ __('url_valid') }}" data-min="{{ __('url_least_characters') }}" data-max="{{ __('url_most_characters') }}"></span>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
						<button type="submit" class="btn btn-primary submitbtn" data-save_text="{{ __('Save') }}">{{ __('Save') }}</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="modal fade" id="delete-modal">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<form id="deleteForm">
					<div class="modal-body text-center">
						<span class="delete-icon">
							<i class="ti ti-trash-x"></i>
						</span>
						<h4>{{ __('Confirm Deletion') }}</h4>
						<p>{{ __('Are you sure you want to delete this item? This action cannot be undone.') }}</p>
						<input type="hidden" name="delete_id" id="delete_id">
						<div class="d-flex justify-content-center">
							<button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
							<button type="submit" class="btn btn-danger delete_tax_option">{{ __('Delete') }}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/sitemap.js') }}"></script>
@endpush