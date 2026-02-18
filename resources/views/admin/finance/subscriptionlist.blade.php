@extends('admin.admin')
@section('content')

<div class="page-wrapper">
	<div class="content bg-white">
		<div class="d-md-flex d-block align-items-center justify-content-between pb-3">
			<div class="my-auto mb-2">
			<h3 class="page-title mb-1">{{ __('subscription_list')}}</h3>
				<nav>
					<ol class="breadcrumb mb-0">
						<li class="breadcrumb-item">
							<a href="{{route('admin.dashboard')}}">{{ __('Dashboard')}}</a>
						</li>
						<li class="breadcrumb-item">
							<a href="javascript:void(0);">{{ __('finance')}}</a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">{{ __('subscription_list')}}</li>
					</ol>
				</nav>
			</div>
		</div>
		<div class="card">
			<div class="card-body p-0">
				<div class="col-xxl-12">
					<form>
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
											<th>{{ __('#')}}</th>
											<th>{{ __('Plan Name')}}</th>
											<th>{{ __('Price')}}</th>
											<th>{{ __('Subscription_Type')}}</th>
											<th>{{ __('Description')}}</th>
											<th>{{ __('Provider')}}</th>
											<th>{{ __('Start Date')}}</th>
											<th>{{ __('End Date')}}</th>
											<th>{{ __('Payment Status')}}</th>
											<th>{{ __('Status')}}</th>
											<th>{{ __('Payment Proof')}}</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
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
					<h4>Confirm Deletion</h4>
					<p>You want to delete this template, this cant be undone once you delete.</p>
					<div class="d-flex justify-content-center">
						<a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</a>
						<button type="submit" class="btn btn-danger" id="confirmDelete">Yes, Delete</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="verifyPaymentModal">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form method="POST" id="verifyPaymentForm">
				@csrf
				<input type="hidden" name="trx_id" id="trx_id">
				<div class="modal-body text-center">
					<span class="fs-30 text-success">
						<i class="ti ti-check"></i>
					</span>
					<h4>{{ __('confirm_verification') }}</h4>
					<p>{{ __('Are you sure you want to verify this payment') }}</p>
					<div class="d-flex justify-content-center">
						<button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
						<button type="button" class="btn btn-success" id="confirmVerifyBtn" data-verifying="{{ __('verifying') }}" data-yes_verify="{{ __('Yes, Verify') }}">{{ __('Yes, Verify') }}</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection
