@extends('provider.provider')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
                <div class="skeleton label-skeleton label-loader"></div>
                <h5 class="d-none real-label">{{ __('Plan & Billings') }}</h5>
                <div class="d-flex align-items-center">
                    <div class="skeleton label-skeleton label-loader"></div>
                    <a href="{{route('provider.subscription')}}" class="btn btn-dark d-flex align-items-center d-none real-label">{{ __('Plans') }}</a>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-6 d-flex flex-column">
                <div class="skeleton label-skeleton label-loader"></div>
                <h6 class="subhead-title d-none real-label">{{ __('Current Plan') }}</h6>
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="plan-info row">
                            @if(isset($data['standardplan']))
                            <div class="col-md-9">
                                <div class="plan-term">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <h6 class="mb-1 d-none real-label">{{$data['standardplan']->package_title}}</h6>
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <p class="mb-2 d-none real-label">{{$data['standardplan']->description}}</p>
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <p class="fs-12 text-dark d-none real-label"><i class="feather-calendar fs-12"></i> {{ __('Renew') }}
                                        {{ __('Date:') }} <span class="fs-12 text-gray">{{$data['standardplan']->end_date}}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="plan-price">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <h4 class="d-none real-label">{{$data['currency']}}{{$data['standardplan']->price}}</h4>
                                </div>
                            </div>
                            @else
                            <div class="col-md-9">
                                <div class="skeleton label-skeleton label-loader"></div>
                                <p class="mb-0 text-dark fw-medium d-none real-label">{{ __('You have not Subscribed any Plan yet..') }}</p>
                            </div>
                            @endif
                        </div>
                        <div class="plan-btns">
                            <div class="skeleton label-skeleton label-loader"></div>
                            <a href="{{route('provider.subscription')}}" class="btn btn-dark d-none real-label">{{ __('Upgrade') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            @if(isset($data['topupplan']))
            <div class="col-lg-2 d-flex flex-column">
                <div class="skeleton label-skeleton label-loader"></div>
                <h6 class="subhead-title d-none real-label">{{ __('Topup') }}</h6>
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="plan-info row">

                            <div class="col-md-9">
                                <div class="plan-term">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <h6 class="mb-1 d-none real-label">{{$data['topupplan']->package_title}}</h6>
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <p class="mb-2 d-none real-label">{{$data['topupplan']->description}}</p>
                                </div>
                                <div class="plan-price">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <h4 class="d-none real-label">{{$data['currency']}}{{$data['topupplan']->price}}</h4>
                                </div>
                                <div>
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <p class="fs-12 text-dark d-none real-label"><i class="feather-calendar fs-12 me-1"></i><span
                                            class="fs-12 text-gray">{{$data['topupplan']->next_payment_date ?? ''}}</span> </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-lg-4 d-flex flex-column">
                <div class="skeleton label-skeleton label-loader"></div>
                <h6 class="subhead-title d-none real-label">{{ __('Payment') }}</h6>
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="plan-info row">
                            <div class="col-md-9">
                                <div class="plan-term">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <h6 class="mb-1 d-none real-label">{{ __('Last Payment') }}</h6>
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <p class="fs-12 text-dark d-none real-label"><i class="feather-calendar fs-12 me-1"></i><span
                                            class="fs-12 text-gray">{{$data['standardplan']->payment_date ?? '-'}}</span> </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="skeleton label-skeleton label-loader"></div>
                                <div class="plan-price d-flex justify-content-end">
                                    <span
                                        class="badge badge-soft-success d-inline-flex align-items-center d-none real-label">{{$data['standardplan']->activestatus?? ''}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="plan-btns row">
                            <div class="col-md-7">
                                <div class="plan-term">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <h6 class="mb-1 d-none real-label">{{ __('Next Payment') }}</h6>
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <p class="fs-12 text-dark d-none real-label"><i class="feather-calendar fs-12 me-1"></i><span
                                            class="fs-12 text-gray">{{$data['standardplan']->next_payment_date ?? ''}}</span> </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="page-header">
                    <div class="row flex-fill">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="skeleton label-skeleton label-loader"></div>
                            <h5 class="d-none real-label">{{ __('Billing History') }}</h5>
                        </div>

                    </div>
                </div>
                <div class="card-body p-3 pb-0">
                    <div class="col-xxl-12">
                        <form>
                            <div class="card-body p-0 py-3">
                                <div class="custom-datatable-filter table-responsive">
                                    <table class="table d-none real-label" id="ListTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>{{ __('S.No') }}</th>
                                                <th>{{ __('Payout Date') }}</th>
                                                <th>{{ __('Subscription_Type')}}
                                                <th>{{ __('Plan Name') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                <th>{{ __('Payment Status') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th class="no-sort">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>

                                    <!-- loader Datatable Start-->
                                    <table id="loader-table" class="table table-striped table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <p class="d-none real-label">ID</p>
                                                </th>
                                                <th>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <p class="d-none real-label">Name</p>
                                                </th>
                                                <th>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <p class="d-none real-label">Email</p>
                                                </th>
                                                <th>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <p class="d-none real-label">Role</p>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">1</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">John Doe</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">johndoe@example.com</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Admin</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">2</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Jane Smith</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">janesmith@example.com</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Manager</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">3</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Robert Brown</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">robertbrown@example.com</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">User</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">3</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Robert Brown</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">robertbrown@example.com</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">User</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">3</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Robert Brown</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">robertbrown@example.com</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">User</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">3</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Robert Brown</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">robertbrown@example.com</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">User</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">3</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Robert Brown</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">robertbrown@example.com</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">User</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">4</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Emily Davis</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">emilydavis@example.com</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Customer</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <!-- loader Datatable End -->
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-5">
                        <div id="tablelength"></div>
                    </div>
                    <div class="col-md-7">
                        <div class="table-ingopage">
                            <div id="tableinfo"></div>
                            <div id="tablepagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="view-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">{{ __('Billing Details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>{{ __('Type:') }}</strong> <span id="modalType"></span> </p>
                <p><strong>{{ __('Title:') }}</strong> <span id="modalTitle"></span> </p>
                <p><strong>{{ __('Date:') }}</strong> <span id="modalDate"></span></p>
                <p><strong>{{ __('Payment Status:') }}</strong> <span id="status"></span> </p>
                <p><strong>{{ __('Amount:') }}</strong> <span id="amount"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection