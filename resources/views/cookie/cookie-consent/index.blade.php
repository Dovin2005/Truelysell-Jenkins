<div class="cookie-consent hidden">
    <div>
        <div class="cookie-consent__message d-flex gap-3">
            <div class="mb-2">
                {!! $cookies['cookies_content_text_' . $selectedLanguageCode] 
                ?? 'We use cookies to personalize content and analyze traffic. By using our site' !!}
            </div>
            <a class="text-primary text-decoration-underline" href="{{ $cookies['lin_for_cookies_page_' . $selectedLanguageCode] ?? '#' }}">
                {{ __('Learn more') }}
            </a>
        </div>
        <div class="d-flex justify-content-center">
            <button class="cookie-consent__agree">
                {{ $cookies['agree_button_text_' . $selectedLanguageCode] ?? __('Accept Cookies')  }}
            </button>
            @if (isset($cookies['show_decline_button_' . $selectedLanguageCode]) && $cookies['show_decline_button_' . $selectedLanguageCode] == 1)
            <button class="cookie-consent__decline">
                {{ $cookies['decline_button_text_' . $selectedLanguageCode] ?? __('Decline')  }}
            </button>
            @endif
        </div>
    </div>
</div>
