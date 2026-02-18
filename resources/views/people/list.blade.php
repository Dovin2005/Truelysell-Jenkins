@extends('admin.admin')
@section('content')

	<div class="page-wrapper">
		<div class="content bg-white">
			<div class="d-md-flex d-block align-items-center justify-content-between pb-3">
				<div class="my-auto mb-2">
				<h3 class="page-title mb-1">@if($title=='Providers') {{ __('Providers')}} @else {{ __('user')}} @endif</h3>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{route('admin.dashboard')}}">{{ __('Dashboard')}}</a>
							</li>
							<li class="breadcrumb-item">
								<a href="javascript:void(0);">{{ __('people')}}</a>
							</li>
							<li class="breadcrumb-item active" aria-current="page">@if($title=='Providers') {{ __('Providers')}} @else {{ __('user')}} @endif</li>
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
			<div class="card">
				<div class="card-body p-0">
					<div class="col-xxl-12">
						<form>
							<div class="card-body p-0 py-3">
								<div class="custom-datatable-filter">
									<div class="table-responsive">
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
													<td>
														<div class="skeleton data-skeleton data-loader"></div>
													</td>
												</tr>
											</tbody>
										</table>
										<table class="table d-none" id="ListTable">
											<thead class="thead-light">
												<tr>
													<th>{{ __('name')}}</th>
													<th>{{ __('email')}}</th>
													<th>{{ __('phone_number')}}</th>
													@if($title=='Providers')
														<th>{{ __('Category')}}</th>
													@endif
													@if ($edit == 1)
														<th>{{ __('Status')}}</th>
													@endif
													@if ($isVisible == 1)
														<th class="no-sort">{{ __('Action') }}</th>
													@endif
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="delete-modal">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<form>
					<div class="modal-body text-center">
						<span class="delete-icon">
							<i class="ti ti-trash-x"></i>
						</span>
						<h4>{{ __('Confirm Deletion')}}</h4>
						<p>{{ __('confirm_delete')}}</p>
						<div class="d-flex justify-content-center">
							<a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel')}}</a>
							<button type="submit" class="btn btn-danger" id="confirmDelete" data-id="">{{ __('Yes, Delete')}}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
