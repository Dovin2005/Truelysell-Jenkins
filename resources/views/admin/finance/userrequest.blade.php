@extends('admin.admin')

@section('content')
<div class="page-wrapper page-settings">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('user_payout')}}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{route('admin.dashboard')}}">{{ __('Dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('finance')}}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('user_payout')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 ">
                <div class="card">
                    <div class="card-body p-0 py-3">
                        <div class="custom-datatable-filter  table-responsive">
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
                            <table class="table d-none" id="userrequestlist" data-empty="{{ __('No Data Found') }}">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('S.No')}}</th>    
                                        <th>{{ __('Booking Date')}}</th>                            
                                        <th>{{ __('user_name')}}</th>    
                                        <th>{{ __('Product Name')}}</th>                             
                                        <th>{{ __('Amount')}}</th>
                                        <th>{{ __('Payment Type')}}</th>
                                        <th>{{ __('Status')}}</th>
                                        <th>{{ __('Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody class="userrequestlist">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="veiw_transaction">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Refund Process')}}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="booking_id">               
                <label class="form-label mt-2 "> {{ __('Refund')}} {{ __('Amount')}} : <span class='refundamt'></span></label>             
                <div id="codUploadSection" class="mt-2">                   
                    <label for="codFile" class="form-label mt-4">{{ __('Upload Payment Proof') }}</label>
                    <input type="file" id="codFile" class="form-control" accept="image/*,application/pdf">
                    <div id="filePreview" class="mt-3"></div>
                    <button id="uploadPaymentProof" class="btn btn-primary mt-3" disabled>{{ __('Submit Proof') }}</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection
