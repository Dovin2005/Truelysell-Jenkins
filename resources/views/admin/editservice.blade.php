@extends('admin.admin')

@section('content')

<div class="page-wrapper">
	<div class="content">
		<div class="d-md-flex d-block align-items-center justify-content-between mb-3">
			<div class="my-auto mb-2">
				<h3 class="page-title mb-1">{{ __('edit_service') }}</h3>
				<nav>
					<ol class="breadcrumb mb-0">
						<li class="breadcrumb-item">
							<a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
						</li>
						<li class="breadcrumb-item">
							<a href="javascript:void(0);">{{ __('Settings') }}</a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">{{ __('edit_service') }}</li>
					</ol>
				</nav>
			</div>
		</div>
		<div class="row">
			<div class="col-xxl-12 col-xl-12">
				<div class="flex-fill border-start ps-3">
					<div class="tab-content">
						<div class="tab-pane fade show active" id="general_tab" role="tabpanel" aria-labelledby="general-tab">
							<form id="adminUpdateService" enctype="multipart/form-data" method="POST" action="{{ route('updateservice') }}">
								{{ csrf_field() }}
								<input type="hidden" name="source_id" id="source_id" value="{{$products->id}}">
								<div class="d-md-flex d-block">
									<div class="flex-fill">

										<div class="card">
											<div class="card-header">
												<h5>{{ __('Service Information') }}</h5>
											</div>
											<div class="card-body pb-1">
												<div class="d-block d-xl-flex">
													<div class="mb-3 flex-fill me-xl-3 me-0">
														<label class="form-label">{{ __('name') }}</label>
														<input type="text" name="source_name" id="source_name" value="{{$products->source_name}}" class="form-control" required placeholder="{{ __('Enter Name') }}">
													</div>
													<div class="mb-3 flex-fill">
														<label class="form-label">{{ __('service_code') }}</label>
														<input type="text" name="source_code" id="source_code" value="{{$products->source_code}}" class="form-control" placeholder="{{ __('enter_service_code') }}">
													</div>
												</div>
												<div class="d-block d-xl-flex">
													<div class="mb-3 flex-fill me-xl-3 me-0">
														<label class="form-label">{{ __('Category') }}</label>
														<select id="category" name="category" class="form-control categoryProviderSelect">
															<option value="">{{ __('Select Category') }}</option>
															@foreach ($categories as $category)
															<option value="{{ $category->id }}"
																{{ $products->source_category == $category->id ? 'selected' : '' }}>
																{{ $category->name }}
															</option>
															@endforeach
														</select>
													</div>
													<div class="mb-3 flex-fill">
														<label class="form-label">{{ __('Sub Category') }}</label>
														<select id="Subcategory_fied" name="Subcategory_fied" class="form-control subcategories">
															<option value="{{$subCategories->id}}">{{$subCategories->name}}</option>
														</select>
													</div>
												</div>

												<div class="col-md-12">
													<div class="mb-3">
														<label class="form-label">{{ __('Includes') }} <span class="text-danger"></span></label>
														<input
															type="text"
															name="include"
															id="include"
															class="form-control"
															data-role="tagsinput"
															value="{{ $products->include }}"
															placeholder="{{ __('Enter Includes') }}">
														<span class="invalid-feedback" id="include_error"></span>
													</div>
												</div>

												<div class="d-block d-xl-flex">
													<div class="mb-3 flex-fill">
														<label class="form-label">{{ __('Description') }}</label>
														<textarea class="form-control" name="source_desc" rows="6" id="source_desc">{{$products->source_description}}</textarea>
													</div>

												</div>



											</div>
										</div>

										<div class="card">
											<div class="card-header bg-light">
												<h5>{{ __('Price') }}</h5>
											</div>
											<div class="card-body pb-1">
												<div class="d-block d-xl-flex">
													<div class="mb-3 flex-fill me-xl-3 me-0">
														<label class="form-label">{{ __('Price Type') }}</label>
														<select name="price_type" class="form-control">
															<option>{{ __('Select') }}</option>
															<option value="Fixed" @selected($products->price_type == 'Fixed')>{{ __('Fixed') }}</option>
															<option value="Hourly" @selected($products->price_type == 'Hourly')>{{ __('Hourly') }}</option>
															<option value="Minitue" @selected($products->price_type == 'Minitue')>{{ __('Minitue') }}</option>
															<option value="Squre-metter" @selected($products->price_type == 'Squre-metter')>{{ __('price_type_square_meter') }}</option>
															<option value="Squre-Feet" @selected($products->price_type == 'Squre-Feet')>{{ __('price_type_square_feet') }}</option>
														</select>
													</div>
													<div class="mb-3 flex-fill me-xl-3">
														<label class="form-label">{{ __('Price') }}</label>
														<input type="text" name="fixed_price" id="fixed_price" value="{{$products->source_price}}" class="form-control" placeholder="">

													</div>
													<div class="mt-4 flex-fill">
														<button onclick="loadslots('sl2')" type="button" class="btn btn-primary">{{ __('Add slots') }}</button>
													</div>

												</div>
												<div class="d-block d-xl-flex" id="sl2" style="display:none !important">
													<div class="mb-3 flex-fill me-xl-3">
														<label class="form-label">Slot Time</label>

													</div>
													<div class="mt-4 flex-fill">
														<button onclick="" type="button" class="btn btn-primary">Update</button>
													</div>
												</div>
												<div class="d-block d-xl-flex border border-success p-2 mb-2" id="mon_slot" style="display:none !important">
													<div class="mb-3 flex-fill me-xl-3">
														<div class="form-check form-switch">
															<input class="form-check-input" onclick="loadslotstime('mon_slottime')" type="checkbox" role="switch" id="flexSwitchCheckDefault">
															<label class="form-check-label" for="flexSwitchCheckDefault">Monday</label>
														</div>
													</div>
													<div class="mt-4 flex-fill" id="mon_slottime" style="display:none !important">
														<div class="d-block d-xl-flex">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="mon_from[]" id="source_code2" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="mon_to[]" id="source_cod3" class="form-control" placeholder="To">
															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('mon_2')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>
															</div>
														</div>
														<div class="d-block d-xl-flex" id="mon_2" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="mon_from[]" id="source_code0" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="mon_to[]" id="source_code4" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('mon_3')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>

														<div class="d-block d-xl-flex" id="mon_3" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="mon_from[]" id="source_code5" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="mon_to[]" id="source_code5" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('mon_4')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>

														<div class="d-block d-xl-flex" id="mon_4" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="mon_from[]" id="source_code6" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="mon_to[]" id="source_code7" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('mon_5')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>

														<div class="d-block d-xl-flex" id="mon_5" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="mon_from[]" id="source_code8" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="mon_to[]" id="source_code8" class="form-control" placeholder="To">

															</div>
															<div class=" flex-fill">
																<a href="#" onclick="loadcountrysec('mon_5')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>
															</div>

														</div>
													</div>

												</div>

												<div class="d-block d-xl-flex d-xl-flex border border-success p-2 mb-2" id="tue_slot" style="display:none !important">
													<div class="mb-3 flex-fill me-xl-3">
														<div class="form-check form-switch">
															<input class="form-check-input" onclick="loadslotstime('tue_slottime')" type="checkbox" role="switch" id="flexSwitchCheckDefault">
															<label class="form-check-label" for="flexSwitchCheckDefault">Tuesday</label>
														</div>
													</div>
													<div class="mt-4 flex-fill" id="tue_slottime" style="display:none !important">
														<div class="d-block d-xl-flex">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="tue_from[]" id="source_code9" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="tue_to[]" id="source_code9" class="form-control" placeholder="To">
															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('tues_2')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>
															</div>
														</div>

														<div class="d-block d-xl-flex" id="tues_2" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="tue_from[]" id="source_code10" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="tue_to[]" id="source_code11" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('tues_3')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="tues_3" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="tue_from[]" id="source_code12" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="tue_to[]" id="source_code13" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('tues_4')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="tues_4" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="tue_from[]" id="source_code14" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="tue_to[]" id="source_code15" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('tues_5')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="tues_5" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="tue_from[]" id="source_code16" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="tue_to[]" id="source_code17" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('tues_5')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
													</div>
												</div>


												<div class="d-block d-xl-flex d-xl-flex border border-success p-2 mb-2" id="wed_slot" style="display:none !important">
													<div class="mb-3 flex-fill me-xl-3">
														<div class="form-check form-switch">
															<input class="form-check-input" onclick="loadslotstime('wed_slottime')" type="checkbox" role="switch" id="flexSwitchCheckDefault">
															<label class="form-check-label" for="flexSwitchCheckDefault">Wednesday</label>
														</div>
													</div>
													<div class="mt-4 flex-fill" id="wed_slottime" style="display:none !important">
														<div class="d-block d-xl-flex">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="wed_from[]" id="source_code18" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="wed_to[]" id="source_code19" class="form-control" placeholder="To">
															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('wed_2')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>
															</div>
														</div>

														<div class="d-block d-xl-flex" id="wed_2" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="wed_from[]" id="source_code20" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="wed_to[]" id="source_code21" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('wed_3')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="wed_3" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="wed_from[]" id="source_code22" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="wed_to[]" id="source_code23" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('wed_4')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="wed_4" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="wed_from[]" id="source_code24" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="wed_to[]" id="source_code24" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('wed_5')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="wed_5" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="wed_from[]" id="source_code25" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="wed_to[]" id="source_code26" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('wed_5')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
													</div>

												</div>




												<div class="d-block d-xl-flex" id="thu_slot" style="display:none !important">
													<div class="mb-3 flex-fill me-xl-3">
														<div class="form-check form-switch">
															<input class="form-check-input" onclick="loadslotstime('thu_slottime')" type="checkbox" role="switch" id="flexSwitchCheckDefault">
															<label class="form-check-label" for="flexSwitchCheckDefault">Thursday</label>
														</div>
													</div>

													<div class="mt-4 flex-fill" id="thu_slottime" style="display:none !important">
														<div class="d-block d-xl-flex">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="thu_from[]" id="source_code18" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="thu_to[]" id="source_code19" class="form-control" placeholder="To">
															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('thu_2')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>
															</div>
														</div>

														<div class="d-block d-xl-flex" id="thu_2" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="thu_from[]" id="source_code20" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="thu_to[]" id="source_code21" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('thu_3')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="thu_3" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="thu_from[]" id="source_code22" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="thu_to[]" id="source_code23" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('thu_4')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="thu_4" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="thu_from[]" id="source_code24" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="thu_to[]" id="source_code24" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('thu_5')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="thu_5" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="thu_from[]" id="source_code25" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="thu_to[]" id="source_code26" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('thu_5')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
													</div>




												</div>

												<div class="d-block d-xl-flex" id="fri_slot" style="display:none !important">
													<div class="mb-3 flex-fill me-xl-3">
														<div class="form-check form-switch">
															<input class="form-check-input" onclick="loadslotstime('fri_slottime')" type="checkbox" role="switch" id="flexSwitchCheckDefault">
															<label class="form-check-label" for="flexSwitchCheckDefault">Friday</label>
														</div>
													</div>


													<div class="mt-4 flex-fill" id="fri_slottime" style="display:none !important">
														<div class="d-block d-xl-flex">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="fri_from[]" id="source_code18" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="fri_to[]" id="source_code19" class="form-control" placeholder="To">
															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('fri_2')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>
															</div>
														</div>

														<div class="d-block d-xl-flex" id="fri_2" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="fri_from[]" id="source_code20" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="fri_to[]" id="source_code21" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('fri_3')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="fri_3" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="fri_from[]" id="source_code22" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="fri_to[]" id="source_code23" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('fri_4')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="fri_4" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="fri_from[]" id="source_code24" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="fri_to[]" id="source_code24" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('fri_5')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="fri_5" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="fri_from[]" id="source_code25" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="fri_to[]" id="source_code26" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('fri_5')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
													</div>

												</div>
												<div class="d-block d-xl-flex" id="sat_slot" style="display:none !important">
													<div class="mb-3 flex-fill me-xl-3">
														<div class="form-check form-switch">
															<input class="form-check-input" onclick="loadslotstime('sat_slottime')" type="checkbox" role="switch" id="flexSwitchCheckDefault">
															<label class="form-check-label" for="flexSwitchCheckDefault">Saturday</label>
														</div>
													</div>


													<div class="mt-4 flex-fill" id="sat_slottime" style="display:none !important">
														<div class="d-block d-xl-flex">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="sat_from[]" id="source_code18" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="sat_to[]" id="source_code19" class="form-control" placeholder="To">
															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('sat_2')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>
															</div>
														</div>

														<div class="d-block d-xl-flex" id="sat_2" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="sat_from[]" id="source_code20" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="sat_to[]" id="source_code21" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('sat_3')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="sat_3" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="sat_from[]" id="source_code22" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="sat_to[]" id="source_code23" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('sat_4')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="sat_4" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="sat_from[]" id="source_code24" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="sat_to[]" id="source_code24" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('sat_5')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="sat_5" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="sat_from[]" id="source_code25" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="sat_to[]" id="source_code26" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('sat_5')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
													</div>

												</div>
												<div class="d-block d-xl-flex" id="sun_slot" style="display:none !important">
													<div class="mb-3 flex-fill me-xl-3">
														<div class="form-check form-switch">
															<input class="form-check-input" onclick="loadslotstime('sun_slottime')" type="checkbox" role="switch" id="flexSwitchCheckDefault">
															<label class="form-check-label" for="flexSwitchCheckDefault">Sunday</label>
														</div>
													</div>


													<div class="mt-4 flex-fill" id="sun_slottime" style="display:none !important">
														<div class="d-block d-xl-flex">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="sun_from[]" id="source_code18" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="sun_to[]" id="source_code19" class="form-control" placeholder="To">
															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('sun_2')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>
															</div>
														</div>

														<div class="d-block d-xl-flex" id="sun_2" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="sun_from[]" id="source_code20" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="sun_to[]" id="source_code21" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('sun_3')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="sun_3" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="sun_from[]" id="source_code22" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="sun_to[]" id="source_code23" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('sun_4')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="sun_4" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="sun_from[]" id="source_code24" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="sun_to[]" id="source_code24" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('sun_5')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
														<div class="d-block d-xl-flex" id="sun_5" style="display:none !important">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<input type="time" name="sun_from[]" id="source_code25" class="form-control" placeholder="From">
															</div>
															<div class="mb-3 flex-fill me-xl-3">
																<input type="time" name="sun_to[]" id="source_code26" class="form-control" placeholder="To">

															</div>
															<div class="flex-fill">
																<a href="#" onclick="loadcountrysec('sun_5')" class="btn btn-outline-light bg-white btn-icon me-1" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-square-rounded-plus"></i></a>

															</div>

														</div>
													</div>

												</div>
											</div>
										</div>
										<div class="card">
											<div class="card-header bg-light">
												<h5>{{ __('Location') }}</h5>
											</div>
											<div class="card-body pb-1">

												<div class="d-block d-xl-flex">
													<div class="mb-3 flex-fill me-xl-3 me-0">
														<label class="form-label">{{ __('Country') }}</label>
														<select class="select select2 form-control" id="country" name="country"
															data-placeholder="{{__('select_country')}}"
															data-country="{{ $location->country ?? '' }}">
															<option value="">Select Country</option>
														</select>
													</div>

													<div class="mb-3 flex-fill me-xl-3">
														<label class="form-label">{{ __('State') }}</label>
														<select class="select select2 form-control" id="state" name="state"
															data-placeholder="{{__('select_state')}}"
															data-state="{{ $location->state ?? '' }}">
														</select>
													</div>

													<div class="mb-3 flex-fill me-xl-3">
														<label class="form-label">{{ __('City') }}</label>
														<select class="select select2 form-control" id="city" name="city"
															data-placeholder="{{__('select_city')}}"
															data-city="{{ $location->city ?? '' }}">
														</select>
													</div>
													<!-- <div class="mt-4 flex-fill">
														<button onclick="loadcountrysec('co2')" type="button" class="btn btn-primary">Add</button>
													</div> -->
												</div>

												<div class="d-block d-xl-flex" id="co2" style="display:none !important">
													<div class="mb-3 flex-fill me-xl-3 me-0">
														<label class="form-label">{{ __('Country') }}</label>
														<select name="source_country1[]" class="form-control">
															<option>India</option>
															<option>Australia</option>
															<option>UK</option>
														</select>
													</div>
													<div class="mb-3 flex-fill me-xl-3">
														<label class="form-label">{{ __('City') }}</label>
														<select class="select2" name="source_city1[]" multiple="multiple">
															<option value="Chennai">Chennai</option>
															<option value="Banglore">Banglore</option>
															<option value="Mumbai">Mumbai</option>
															<option value="Delhi">Delhi</option>
															<option value="Melborne">Melborne</option>
															<option value="London">London</option>

														</select>
													</div>
													<div class="mt-4 flex-fill">
														<button onclick="loadcountrysec('co3')" type="button" class="btn btn-primary">{{ __('Add') }}</button>
													</div>
												</div>
												<div class="d-block d-xl-flex" id="co3" style="display:none !important">
													<div class="mb-3 flex-fill me-xl-3 me-0">
														<label class="form-label">{{ __('Country') }}</label>
														<select name="source_country2[]" class="form-control">
															<option>India</option>
															<option>Australia</option>
															<option>UK</option>
														</select>
													</div>
													<div class="mb-3 flex-fill me-xl-3">
														<label class="form-label">{{ __('City') }}</label>
														<select class="select2" name="source_city2[]" multiple="multiple">
															<option value="Chennai">Chennai</option>
															<option value="Banglore">Banglore</option>
															<option value="Mumbai">Mumbai</option>
															<option value="Delhi">Delhi</option>
															<option value="Melborne">Melborne</option>
															<option value="London">London</option>

														</select>
													</div>
													<div class="mt-4 flex-fill">
														<button type="button" class="btn btn-primary">{{ __('Add') }}</button>
													</div>
												</div>
											</div>
										</div>

										<div class="card">
											<div class="card-header">
												<h5>{{ __('Services Offered') }}</h5>
											</div>
											<div class="card-body pb-1">
												<div id="append_fields">
													@foreach ($addservices as $service)
													<div class="appended-fields">
														<div class="d-block d-xl-flex">
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<label class="form-label">{{ __('image') }}</label>
																<input type="file" accept="image/*" name="service_image[]" class="form-control service-image-input">
																<div class="mt-2">
																	@if ($service->image)
																	<img src="{{ asset('storage/' . $service->image) }}" alt="Service Image" class="img-preview" width="100">
																	@else
																	<img src="#" alt="Preview" class="img-preview" width="100" style="display: none;">
																	@endif
																</div>
															</div>
															<div class="mb-3 flex-fill me-xl-3 me-0">
																<label class="form-label">{{ __('Service Name') }}</label>
																<input type="text" name="service_name[]" class="form-control" required value="{{ $service->name }}" placeholder="{{ __('Enter Service Name') }}">
															</div>
															<div class="mb-3 flex-fill">
																<label class="form-label">{{ __('Price') }}</label>
																<input type="text" name="service_price[]" class="form-control" value="{{ $service->price }}" maxlength="8" placeholder="{{ __('Enter Price') }}">
															</div>
														</div>
														<div class="d-block d-xl-flex">
															<div class="mb-3 flex-fill">
																<label class="form-label">{{ __('Description') }}</label>
																<textarea class="form-control" name="service_desc[]" placeholder="{{ __('Enter Description') }}">{{ $service->duration }}</textarea>
															</div>
														</div>
														<div class="mb-3">
															<button type="button" class="btn btn-danger remove-service">{{ __('Remove') }}</button>
														</div>
													</div>
													@endforeach
												</div>
												<div class="mb-3 flex-fill text-end">
													<button type="button" class="btn btn-primary" id="addservice">{{ __('add_more') }}</button>
												</div>
											</div>
										</div>


										<div class="card">
											<div class="card-header bg-light">
												<h5>{{ __('seo_information') }}</h5>
											</div>
											<div class="card-body pb-1">

												<div class="d-block d-xl-flex">
													<div class="mb-3 flex-fill me-xl-3 me-0">
														<label class="form-label">{{ __('seo_title') }}</label>
														<input type="text" name="seo_title" id="seo_title" class="form-control" value="{{$products->seo_title}}" placeholder="{{ __('enter_seo_title') }}">

													</div>
													<div class="mb-3 flex-fill">
														<label class="form-label">{{ __('tags') }}</label>
														<input type="text" name="tags" id="tags" class="form-control" data-role="tagsinput" value="{{$products->tags}}" placeholder="{{ __('enter_tag') }}">

													</div>
												</div>

												<div class="d-block d-xl-flex">
													<div class="mb-3 flex-fill">
														<label class="form-label">{{ __('Description') }}</label>
														<textarea class="form-control" name="content" id="content">{{$products->seo_description}}</textarea>
													</div>

												</div>
											</div>
										</div>
									</div>

									<div class="settings-right-sidebar ms-md-3">
										<div class="d-block d-xl-flex">
											<div class="mb-3 flex-fill">
												<button type="submit" class="btn btn-primary">{{ __('update_service') }}</button>
											</div>
										</div>
										<div class="card">
											<div class="card-body">
												<div class="card-header bg-light">
													<h5>{{ __('Images') }}</h5>
												</div>
												<div class="card-body">
													<div class="border-bottom mb-3 pb-3">
														<div class="d-flex justify-content-between mb-3">
														</div>
														<code>{{ __('recommended_logo_size') }}</code>
														<div class="profile-uploader profile-uploader-two mb-0">
															<span class="d-block text-center lh-1 fs-24 mb-1"><i class="ti ti-upload"></i></span>
															<div class="drag-upload-btn bg-transparent me-0 border-0">
																<p class="fs-12 mb-2">
																	<span class="text-primary">{{ __('click_to_upload') }}</span> {{ __('drag_and_drop') }}
																</p>
																<h6>{{ __('image_format') }}</h6>
																<h6>{{ __('max_size', ['width' => 155, 'height' => 40]) }}</h6>
															</div>
															<input type="file" class="form-control image-sign" id="logo" name="logo[]" accept="image/*" required multiple>
															<div class="frames"></div>
														</div>
													</div>
												</div>
												<div id="image_preview_container">
													@foreach($productImages as $id => $image)
													<div class="d-flex flex-column align-items-center justify-content-between p-2 border rounded mb-3">
														<div class="image-preview position-relative" style="display: flex">
															<img src="{{ asset($image) }}" class="img-thumbnail border" style="width: 155px; height: 155px;">
															<input type="hidden" name="image_ids[]" value="{{ $id }}">
														</div>
														<button type="button" class="btn btn-danger btn-sm w-50 mt-2" onclick="removeImage(this, '{{ $image }}', '{{ $id }}')">
															{{ __('Delete') }}
														</button>
													</div>
													@endforeach
												</div>
												<div id="edit_image_preview"></div>
											</div>
										</div>
									</div>
								</div>
							</form>
							<div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="tab-pane fade" id="logo_favicon_tab" role="tabpanel" aria-labelledby="logo-favicon-tab">
			<h3>Logo & Favicon Content</h3>
			<p>Content for the Logo & Favicon tab.</p>
		</div>
		<div class="tab-pane fade" id="cookie_consent_tab" role="tabpanel" aria-labelledby="cookie-consent-tab">
			<h3>Cookie Consent Content</h3>
			<p>Content for the Cookie Consent tab.</p>
		</div>
		<div class="tab-pane fade" id="breadcrump_img_tab" role="tabpanel" aria-labelledby="breadcrump-tab">
			<h3>Breadcrumb Image Content</h3>
			<p>Content for the Breadcrumb Image tab.</p>
		</div>
		<div class="tab-pane fade" id="copyright_text_tab" role="tabpanel" aria-labelledby="copyright-text">
			<h3>Copyright Text Content</h3>
			<p>Content for the Copyright Text tab.</p>
		</div>
		<div class="tab-pane fade" id="maintenance_mode_tab" role="tabpanel" aria-labelledby="maintenance-tab">
			<h3>Maintenance Mode Content</h3>
			<p>Content for the Maintenance Mode tab.</p>
		</div>
	</div>
</div>
</div>

</div>








</div>
</div>
<!-- /Page Wrapper -->
@endsection