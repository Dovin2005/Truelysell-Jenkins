<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{__('Terms & Conditions')}}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item">{{__('Home')}}</li>
                        <li class="breadcrumb-item active" aria-current="page">{{__('Terms & Conditions')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="breadcrumb-bg">
            <img src="{{ asset('front/img/bg/breadcrumb-bg-01.png') }}" class="breadcrumb-bg-1" alt="Img">
            <img src="{{ asset('front/img/bg/breadcrumb-bg-02.png') }}" class="breadcrumb-bg-2" alt="Img">
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="content">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="terms-content privacy-cont">
                        @foreach ($content_sections as $section)
                        {!! $section['terms_conditions'] !!}
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>