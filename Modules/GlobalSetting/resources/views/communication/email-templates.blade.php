@extends('admin.admin')
@section('content')

	<div class="page-wrapper">
		<div class="content bg-white">
			<div class="d-md-flex d-block align-items-center justify-content-between pb-3">
				<div class="my-auto mb-2">
				<h3 class="page-title mb-1">{{ __('Templates')}}</h3>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a>
							</li>
							<li class="breadcrumb-item">
								<a href="javascript:void(0);">{{ __('Communication_Settings')}}</a>
							</li>
							<li class="breadcrumb-item active" aria-current="page">{{ __('Templates')}}</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
					<div class="pe-1 mb-2">
                        @if(isset($permission))
                            @if(hasPermission($permission, 'Communication Settings', 'create'))
								<div class="skeleton label-skeleton label-loader"></div>
                                <a href="#" class="btn btn-primary d-none real-label" data-bs-toggle="modal" data-bs-target="#add_template"><i class="ti ti-plus"></i>{{ __('add_template')}}</a>
                            @endif
                        @endif
					</div>
				</div>
			</div>
            @php $isVisible = 0; @endphp
            @if(isset($permission))
                @if(hasPermission($permission, 'Communication Settings', 'delete'))
                    @php $delete = 1; $isVisible = 1; @endphp
                @else
                    @php $delete = 0; @endphp
                @endif
                @if(hasPermission($permission, 'Communication Settings', 'edit'))
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
									<table class="table d-none" id="TemplateTable">
										<thead class="thead-light">
											<tr>
												<th>{{ __('Communication Type')}}</th>
												<th>{{ __('Notification Type')}}</th>
												<th>{{ __('Title')}}</th>
												<th>{{ __('Status')}}</th>
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
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="add_template">
		<div class="modal-dialog modal-dialog-centered  modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" data-add="{{ __('add_template')}}" data-edit="{{ __('Edit_Template')}}">{{ __('add_template')}}</h4>
					<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
						<i class="ti ti-x"></i>
					</button>
				</div>
				<form id="templateform">
					<div class="modal-body">
						<div class="row">
							<div class="col-md-12">
							<input type="hidden" name="id" id="template_id" class="form-control template_id">
							<div class="mb-3">
									<label class="form-label">{{ __('Template Type')}}</label><span class="text-danger"> *</span>
									<select name="type" id="templatetype" class="form-control select2 validate-input">
										<option value="">-- Please Select --</option>
										<option value=1>Email</option>
										<option value=2>SMS</option>
										<option value=3>Notifications</option>
									</select>
									<span class="text-danger error-text" id="type_error"></span>
								</div>
								<div class="mb-3">
									<label class="form-label">{{ __('Notification Type')}}</label><span class="text-danger"> *</span>
									<select name="notification_type" id="notification_type" class="form-control select2 validate-input">
										<option value="">-- Please Select --</option>
											@foreach($getnotificationtypes as $types)
												<option value="{{ $types->id }}">{{ $types->type }}</option>
											@endforeach
									</select>
									<span class="text-danger error-text" id="notification_type_error"></span>
								</div>
								<div class="mb-3">
									<label class="form-label">{{ __('Title')}}</label><span class="text-danger"> *</span>
									<input type="text" name="title" id="title" class="form-control title validate-input" placeholder="{{ __('enter_title') }}">
									<span class="text-danger error-text" id="title_error"></span>
								</div>
								<div class="mb-3 subjectfield">
									<label class="form-label">{{ __('Subject')}}</label><span class="text-danger"> *</span>
									<input type="text" name="subject" id="subject" class="form-control subject validate-input" placeholder="{{ __('enter_subject') }}">
									<span class="text-danger error-text" id="subject_error"></span>
								</div>
								<div class="mb-3">
									<div><label class="form-label">{{ __('Place Holder')}}</label></div>
									@foreach($getplaceholder as $value)
										<button type="button" class="btn btn-secondary btn-sm mb-2 placeholder_value" data-value="{{ $value->placeholder_name }}">{{ $value->placeholder_name }}</button>
									@endforeach
								</div>
								<div class="mb-3">
									<div class="input-blocks summer-description-box notes-summernote maildiv">
										<label class="form-label">{{ __('Descriptions')}}</label><span class="text-danger"> *</span>
										<input type="hidden" class="content" id="content" name="content" value="">

										<div id="summernote">
										</div>
										<span class="text-danger error-text" id="content_error"></span>

									</div>
									<div class="otherdiv">
										<label class="form-label">{{ __('Descriptions')}}</label><span class="text-danger"> *</span>
										<textarea id="othercontent" name="othercontent" rows="4" cols="50" placeholder="Enter your content here..."  style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; resize: vertical; font-size: 16px;"></textarea>
										<span class="text-danger error-text" id="content_error"></span>

									</div>
									</div>
								<div
									class="modal-satus-toggle d-flex align-items-center justify-content-between">
									<div class="status-title">
										<h5>{{ __('Status')}}</h5>
										<p>{{ __('Change the Status by toggle')}} </p>
									</div>
									<div class="status-toggle modal-status">
										<input type="checkbox" id="status" name="status" class="check status" checked="">
										<label for="status" class="checktoggle"> </label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel')}}</a>
						<button type="submit"  class="btn btn-primary add_template_btn" data-save="{{ __('Save') }}">{{ __('Save')}}</button>
					</div>
				</form>
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
						<p>{{ __('delete_template')}}</p>
						<div class="d-flex justify-content-center">
							<a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel')}}</a>
							<button type="submit" class="btn btn-danger" id="confirmDelete">{{ __('Yes, Delete') }}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
