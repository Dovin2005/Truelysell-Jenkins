@extends('front')

@section('content')

<!-- Breadcrumb -->
<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{ __('Wallet') }}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="ti ti-home-2"></i></a></li>
                        <li class="breadcrumb-item">{{ __('Customer') }}</li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Wallet') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="breadcrumb-bg">
            <img src="/assets/img/bg/breadcrumb-bg-01.png" class="breadcrumb-bg-1" alt="Img">
            <img src="/assets/img/bg/breadcrumb-bg-02.png" class="breadcrumb-bg-2" alt="Img">
        </div>
    </div>
</div>
<!-- /Breadcrumb -->

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content">
        <div class="container">
            <div class="row justify-content-center">
                @include('user.partials.sidebar')
                <div class="col-xl-9 col-lg-8">
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
                        <div class="skeleton label-skeleton label-loader"></div>
                        <h4 class="d-none real-label">{{ __('Wallet') }}</h4>
                        <div>
                            <div class="skeleton label-skeleton label-loader"></div>
                            <a href="javascript:void(0);" class="btn btn-dark btn-sm d-flex align-items-center add_wallet d-none real-label"><i class="ti ti-square-rounded-plus me-1"></i>{{ __('Add Wallet') }}</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-xl-3">
                            <div class="card p-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <span class="wallet-icon bg-gray rounded-circle d-flex align-items-center justify-content-center"><i class="ti ti-wallet"></i></span>
                                    </div>
                                    <div>
                                        <div class="skeleton label-skeleton label-loader w-75"></div>
                                        <span class="fs-13 text-gray text-truncate d-none real-label">{{ __('Wallet Balance') }}</span>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <h6 class="fs-18 wallet_balance d-none real-label">
                                            <span class="currency"></span><span class="balance"></span>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-xl-3">
                            <div class="card p-3 ">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <span class="wallet-icon bg-gray rounded-circle d-flex align-items-center justify-content-center"><i class="ti ti-wallet"></i></span>
                                    </div>
                                    <div>
                                        <div class="skeleton label-skeleton label-loader w-75"></div>
                                        <span class="fs-13 text-gray d-none real-label">{{ __('Total Debit') }}</span>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <h6 class="fs-18 total_debit d-none real-label">
                                            <span class="currency"></span><span class="totalAmountdebit"></span>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-xl-3">
                            <div class="card p-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <span class="wallet-icon bg-gray rounded-circle d-flex align-items-center justify-content-center"><i class="ti ti-wallet"></i></span>
                                    </div>
                                    <div>
                                        <div class="skeleton label-skeleton label-loader w-75"></div>
                                        <span class="fs-13 text-gray mb-0 d-none real-label">{{ __('Total Amount') }}</span>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <h6 class="fs-18 total_amount d-none real-label">
                                            <span class="currency"></span><span class="totalAmount"></span>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-xl-3">
                            <div class="card p-3 ">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <span class="wallet-icon bg-gray rounded-circle d-flex align-items-center justify-content-center"><i class="ti ti-wallet"></i></span>
                                    </div>
                                    <div>
                                        <div class="skeleton label-skeleton label-loader w-75"></div>
                                        <span class="fs-13 text-gray mb-0 d-none real-label">{{ __('Savings') }}</span>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <h6 class="fs-18 wallet_balance d-none real-label">
                                            <span class="currency"></span><span class="balance"></span>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="skeleton label-skeleton label-loader"></div>
                        <h6 class="d-none real-label">{{ __('Wallet Transactions') }}</h6>
                    </div>
                    <div class="row">
                        <div class="col-12  ">

                            <div class="custom-datatable-filter table-responsive border p-3">
                                <table id="walletHistoryTable" class="table d-none real-label" data-empty="{{ __('no_data_available') }}">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Payment Type') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Transaction Date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be appended here -->
                                    </tbody>
                                </table>

                                 <!-- loader Datatable Start-->
                                 <table id="loader-table" class="table table-striped table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <p class="d-none real-label"></p>
                                            </th>
                                            <th>
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <p class="d-none real-label"></p>
                                            </th>
                                            <th>
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <p class="d-none real-label"></p>
                                            </th>
                                            <th>
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <p class="d-none real-label"></p>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <!-- loader Datatable End -->

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Page Wrapper -->


@endsection