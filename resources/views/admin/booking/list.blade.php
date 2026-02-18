@extends('admin.admin')
@section('content')

<div class="page-wrapper">
	<div class="content bg-white">
		<div class="d-md-flex d-block align-items-center justify-content-between pb-3">
			<div class="my-auto mb-2">
			<h3 class="page-title mb-1">{{ __('Booking List')}}</h3>
				<nav>
					<ol class="breadcrumb mb-0">
						<li class="breadcrumb-item">
							<a href="{{route('admin.dashboard')}}">{{ __('Dashboard')}}</a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">{{ __('Booking List')}}</li>
					</ol>
				</nav>
			</div>

		</div>
		<div class="card">
			<ul class="nav nav-tabs bookingtab p-3 pb-0" id="bookingTabs" role="tablist">
				<li class="nav-item" role="presentation">
					<div class="skeleton label-skeleton label-loader"></div>
					<button class="nav-link active d-none real-label"
						id="all-booking-tab"
						data-bs-toggle="tab"
						data-bs-target="#bookings_tab"
						type="button"
						role="tab"
						data-tab_type="all-booking"
						aria-selected="true">
						{{ __('All Bookings')}}
					</button>
				</li>
				<li class="nav-item" role="presentation">
					<div class="skeleton label-skeleton label-loader"></div>
					<button
						class="nav-link d-none real-label"
						id="pending-tab"
						data-bs-toggle="tab"
						data-bs-target="#bookings_tab"
						type="button"
						role="tab"
						data-tab_type="pending"
						aria-selected="false">
						{{ __('Pending')}}
					</button>
				</li>
				<li class="nav-item" role="presentation">
					<div class="skeleton label-skeleton label-loader"></div>
					<button
						class="nav-link d-none real-label"
						id="inprogress-tab"
						data-bs-toggle="tab"
						data-bs-target="#bookings_tab"
						type="button"
						role="tab"
						data-tab_type="inprogress"
						aria-selected="false">
						{{ __('Inprogress')}}
					</button>
				</li>
				<li class="nav-item" role="presentation">
					<div class="skeleton label-skeleton label-loader"></div>
					<button
						class="nav-link d-none real-label"
						id="completed-tab"
						data-bs-toggle="tab"
						data-bs-target="#bookings_tab"
						type="button"
						role="tab"
						data-tab_type="completed"
						aria-selected="false">
						{{ __('Completed')}}
					</button>
				</li>
                <li class="nav-item" role="presentation">
					<div class="skeleton label-skeleton label-loader"></div>
					<button
						class="nav-link d-none real-label"
						id="order-completed-tab"
						data-bs-toggle="tab"
						data-bs-target="#bookings_tab"
						type="button"
						role="tab"
						data-tab_type="order-completed"
						aria-selected="false">
						{{ __('Order Completed')}}
					</button>
				</li>
                <li class="nav-item" role="presentation">
					<div class="skeleton label-skeleton label-loader"></div>
					<button
						class="nav-link d-none real-label"
						id="refund-completed-tab"
						data-bs-toggle="tab"
						data-bs-target="#bookings_tab"
						type="button"
						role="tab"
						data-tab_type="refund-completed"
						aria-selected="false">
						{{ __('Refund completed')}}
					</button>
				</li>
                <li class="nav-item" role="presentation">
					<button
						class="nav-link d-none real-label"
						id="cancelled-tab"
						data-bs-toggle="tab"
						data-bs-target="#bookings_tab"
						type="button"
						role="tab"
						data-tab_type="cancelled"
						aria-selected="false">
						{{ __('Provider Cancelled')}}
					</button>
				</li>
                <li class="nav-item" role="presentation">
					<button
						class="nav-link d-none real-label"
						id="customer-cancelled-tab"
						data-bs-toggle="tab"
						data-bs-target="#bookings_tab"
						type="button"
						role="tab"
						data-tab_type="customer-cancelled"
						aria-selected="false">
						 {{ __('Customer Cancelled')}}
					</button>
				</li>
			</ul>
			<div class="card-body p-3 pb-0 booktable">
				<div class="col-xxl-12">
					<form>
						<div class="card-body p-0 py-3" >
							<div class="tab-content" id="bookingTabsContent">
								<div class="tab-pane fade show active" id="bookings_tab" role="tabpanel">
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
													<th>{{ __('Date')}}</th>
													<th>{{ __('Provider')}}</th>
													<th>{{ __('user')}}</th>
													<th>{{ __('Service')}}</th>
													<th>{{ __('Amount')}}</th>
													<th>{{ __('Status')}}</th>
													<th class="no-sort">{{ __('Action')}}</th>
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="view-modal">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="eventModalLabel">{{ __('Booking Details')}} <span id="order_id" class="badge bg-primary ms-2"></span></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p><strong>{{ __('Title')}}:</strong> <span id="modalTitle"></span> </p>
				<p><strong>{{ __('Date')}}:</strong> <span id="modalDate"></span></p>
				<p><strong>{{ __('Status')}}:</strong> <span id="status"></span> </p>
				<p><strong>{{ __('user')}}:</strong> <span id="user"></span></p>
				<p><strong>{{ __('Location')}}:</strong> <span id="location"></span></p>
				<p><strong>{{ __('Amount')}}:</strong> <span id="amount"></span></p>
				<p><strong>{{ __('Provider')}}:</strong> <span id="provider"></span></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close')}}</button>
			</div>
		</div>
	</div>
</div>
@endsection
