@extends('front')

@section('content')

<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{__('Providers')}}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}"><i class="ti ti-home-2"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{__('Providers')}}</li>
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

<div class="page-wrapper">
    <div class="content content-two">
        <div class="container">
            <div class="row align-items-start">


                <div class="col-xl-3 col-lg-4 theiaStickySidebar">
                    <div class="card">
                        <div class="card-body">
                            <form id="searchFilterForm">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                                    <h5><i class="ti ti-filter-check me-2"></i>{{__('Filters')}}</h5>
                                    <a href="javascript:void(0);" id="resetFilter">{{__('Reset Filter')}}</a>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <label class="form-label">{{__('Search By Keyword')}}</label>
                                    <input type="text" class="form-control" id="keywords" placeholder="What are you looking for?">
                                </div>

                                <div class="accordion border-bottom mb-3">
                                    <div class="accordion-item mb-3">
                                        <div class="accordion-header" id="accordion-headingThree">
                                            <div class="accordion-button p-0 mb-3" data-bs-toggle="collapse" data-bs-target="#accordion-collapseThree" aria-expanded="true" aria-controls="accordion-collapseThree" role="button">
                                                {{__('Categories')}}
                                            </div>
                                        </div>
                                        <div id="accordion-collapseThree" class="accordion-collapse collapse show" aria-labelledby="accordion-headingThree">
                                            <div class="content-list mb-3" id="fill-more">
                                                <div class="form-check mb-2" >
                                                    <label class="form-check-label">
                                                        <input class="form-check-input" id="all_categories" type="checkbox" checked>
                                                        {{__('All Categories')}}
                                                    </label>
                                                </div>
                                                @foreach ($categories as $category)
                                                <div class="form-check mb-2">
                                                    <label class="form-check-label">
                                                        <input class="form-check-input category-checkbox filter_category" name="category_id" type="checkbox" value="{{ $category->id }}">
                                                        {{ $category->name }}
                                                    </label>
                                                </div>
                                                @endforeach

                                            </div>
                                            @if (count($categories) > 4)
                                                <a href="javascript:void(0);" id="more" class="more-view text-primary fs-14">{{__('View more')}} <i class="ti ti-chevron-down ms-1"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion border-bottom mb-3">
                                    <div class="accordion-header" id="accordion-headingFour">
                                        <div class="accordion-button p-0 mb-3" data-bs-toggle="collapse" data-bs-target="#accordion-collapseFour" aria-expanded="true" aria-controls="accordion-collapseFour" role="button">
                                            {{__('Price Range')}} 
                                        </div>
                                    </div>
                                    <div id="accordion-collapseFour" class="accordion-collapse collapse show" aria-labelledby="accordion-headingFour">
                                        <div class="row gx-2">
                                            <div class="col-6">
                                                <div class="mb-3">
                                                    <input type="text" class="form-control" placeholder="$ Min">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-3">
                                                    <input type="text" class="form-control" placeholder="$ Max">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion border-bottom mb-3">
                                    <div class="accordion-header" id="accordion-headingFive">
                                        <div class="accordion-button p-0 mb-3" data-bs-toggle="collapse" data-bs-target="#accordion-collapseFive" aria-expanded="true" aria-controls="accordion-collapseFive" role="button">
                                            {{__('Location')}}
                                        </div>
                                    </div>
                                    <div id="accordion-collapseFive" class="accordion-collapse collapse show" aria-labelledby="accordion-headingFive">
                                        <div class="mb-3">
                                            <select class="select" name="location" id="location">
                                                <option value="" selected>{{__('Select Location')}}</option>
                                                @if ($cities)
                                                    @foreach ($cities as $city)
                                                        <option value="{{ $city->city }}">{{  $city->showcities() }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion">
                                    <div class="accordion-item mb-3">
                                        <div class="accordion-header" id="accordion-headingTwo">
                                            <div class="accordion-button fs-18 p-0 mb-3" data-bs-toggle="collapse" data-bs-target="#accordion-collapseTwo" aria-expanded="true" aria-controls="accordion-collapseTwo" role="button">
                                                {{__('Ratings')}} 
                                            </div>
                                        </div>
                                        <div id="accordion-collapseTwo" class="accordion-collapse collapse show" aria-labelledby="accordion-headingTwo">
                                            <div class="mb-3">
                                                <div class="form-check mb-2">
                                                    <label class="form-check-label d-block">
                                                        <input class="form-check-input rating_filter" value="5" type="checkbox">
                                                        <span class="rating">
                                                            <i class="fas fa-star filled"></i>
                                                            <i class="fas fa-star filled"></i>
                                                            <i class="fas fa-star filled"></i>
                                                            <i class="fas fa-star filled"></i>
                                                            <i class="fas fa-star filled"></i><span class="float-end"></span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <label class="form-check-label d-block">
                                                        <input class="form-check-input rating_filter" value="4" type="checkbox">
                                                        <span class="rating">
                                                            <i class="fas fa-star filled"></i>
                                                            <i class="fas fa-star filled"></i>
                                                            <i class="fas fa-star filled"></i>
                                                            <i class="fas fa-star filled"></i>
                                                            <i class="fa-regular fa-star filled"></i><span class="float-end"></span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <label class="form-check-label d-block">
                                                        <input class="form-check-input rating_filter" value="3" type="checkbox">
                                                        <span class="rating">
                                                            <i class="fas fa-star filled"></i>
                                                            <i class="fas fa-star filled"></i>
                                                            <i class="fas fa-star filled"></i>
                                                            <i class="fa-regular fa-star filled"></i>
                                                            <i class="fa-regular fa-star filled"></i><span class="float-end"></span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <label class="form-check-label d-block">
                                                        <input class="form-check-input rating_filter" value="2" type="checkbox">
                                                        <span class="rating">
                                                            <i class="fas fa-star filled"></i>
                                                            <i class="fas fa-star filled"></i>
                                                            <i class="fa-regular fa-star filled"></i>
                                                            <i class="fa-regular fa-star filled"></i>
                                                            <i class="fa-regular fa-star filled"></i><span class="float-end"></span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <label class="form-check-label d-block">
                                                        <input class="form-check-input rating_filter" value="1" type="checkbox">
                                                        <span class="rating">
                                                            <i class="fas fa-star filled"></i>
                                                            <i class="fa-regular fa-star filled"></i>
                                                            <i class="fa-regular fa-star filled"></i>
                                                            <i class="fa-regular fa-star filled"></i>
                                                            <i class="fa-regular fa-star filled"></i><span class="float-end"></span>
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-dark w-100" id="searchProviderBtn" >Search</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-8">
                    <div class="row" id="providers-container" data-featured_text="{{__('featured')}}" data-recommended_text="{{__('recommended')}}">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="success_modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body text-center">
				<div class="mb-4">
					<span class="success-icon mx-auto mb-4">
						<i class="ti ti-check"></i>
					</span>
					<p>Your has been sent successfully</p>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
