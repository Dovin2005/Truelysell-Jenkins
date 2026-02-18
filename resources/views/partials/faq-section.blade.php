<!-- FAQ Section -->
<section class="section faq-section">
    <div class="container">
        <div class="section-header text-center mb-4">
            <h2 class="mb-1">
                {{ $section['section_title'] ?? '' }}
                <span class="text-linear-primary">{{ $section['section_title_last'] ?? '' }}</span>
            </h2>
            <p class="sub-title">{{ $section['section_label'] ?? 'Find answers to common questions below.' }}</p>
        </div>

        @if(!empty($section['section_content']) && $section['section_content']->count() > 0)
        <div class="accordion accordion-flush" id="accordionFlushExample">
            @foreach($section['section_content'] as $index => $faq)
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#flush-collapse{{ $index }}" aria-expanded="false"
                        aria-controls="flush-collapse{{ $index }}">
                        {{ $faq->question }}
                    </button>
                </h2>
                <div id="flush-collapse{{ $index }}" class="accordion-collapse collapse"
                    data-bs-parent="#accordionFlushExample">
                    <div class="accordion-body">
                        {!! nl2br(e($faq->answer)) !!}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <h6 class="text-center">{{ __('No FAQs available at the moment.') }}</h6>
        @endif
    </div>
</section>
<!-- /FAQ Section -->