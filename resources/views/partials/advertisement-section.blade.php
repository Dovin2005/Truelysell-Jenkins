@if($addvertismentStatus === 1 && View::exists('advertisement::advertisement.ad'))
<section class="section testimonial-section bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center wow fadeInUp" data-wow-delay="0.2s">
                <div class="section-header text-center">
                    <h2 class="mb-1">{{ $section['section_title'] ?? '' }}
                    </h2>
                    <p class="sub-title">{{ $section['section_label'] ?? '' }}</p>
                </div>
            </div>
        </div>
        <div class="bg-light d-flex justify-content-center align-items-center text-center">
            <section class="row mx-3 w-100 justify-content-center">
                <div class="col-xl-12 d-flex justify-content-center">
                    <div class="d-flex gap-3 overflow-auto flex-nowrap pb-3" id="advertisementOnePage">
                        <!-- Advertisement content goes here -->
                    </div>
                </div>
            </section>
        </div>
    </div>
</section>
@endif