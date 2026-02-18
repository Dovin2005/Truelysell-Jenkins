<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{__('Contact Us')}}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item">{{__('Home')}}</li>
                        <li class="breadcrumb-item active" aria-current="page">{{__('Contact Us')}}</li>
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
            <div class="contacts">
                <div class="contacts-overlay-img d-none d-lg-block">
                    <img src="{{ asset('front/img/bg/bg-07.png') }}" alt="img" class="img-fluid">
                </div>
                <div class="contacts-overlay-sm d-none d-lg-block">
                    <img src="{{ asset('front/img/bg/bg-08.png') }}" alt="img" class="img-fluid">
                </div>

                @foreach ($content_sections as $section)

                {!! $section['contact_us'] !!}

                @endforeach

                <div class="row">
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="contact-img flex-fill">
                            <img src="{{ asset('front/img/services/service-76.jpg') }}" class="img-fluid" alt="img" loading="lazy">
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center justify-content-center">
                        <div class="contact-queries flex-fill">
                            <h2>{{ __('Get In Touch') }}</h2>
                            <form id="contactForm">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <div class="form-group">
                                                <label for="contact_name"
                                                    class="form-label">{{ __('Your Name') }}</label>
                                                <input class="form-control" type="text" name="name" id="contact_name"
                                                    placeholder="{{ __('Enter your name') }}">
                                                <span class="error-text text-danger" id="contact_name_error"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <div class="form-group">
                                                <label for="contact_email"
                                                    class="form-label">{{ __('Your Email Address') }}</label>
                                                <input class="form-control" type="text" name="email" id="contact_email"
                                                    placeholder="{{ __('Enter your email address') }}">
                                                <span class="error-text text-danger" id="contact_email_error"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <div class="form-group">
                                                <label for="contact_phone_number"
                                                    class="form-label">{{ __('Your Phone Number') }}</label>
                                                <input class="form-control" type="text" name="phone_number"
                                                    id="contact_phone_number"
                                                    placeholder="{{ __('Enter your phone number') }}">
                                                <span class="error-text text-danger"
                                                    id="contact_phone_number_error"></span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="form-group">
                                                <label for="message" class="form-label">{{ __('Your Message') }}</label>
                                                <textarea class="form-control" name="message" id="message"
                                                    placeholder="{{ __('Type your message here') }}"
                                                    rows="4"></textarea>
                                                <span class="error-text text-danger" id="message_error"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 submit-btn">
                                        <button type="submit" class="btn btn-dark d-flex align-items-center"
                                            id="contactSaveBtn">
                                            {{ __('Send Message') }}<i class="feather-arrow-right-circle ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>