<div class="comment-item mt-3">
    <div class="d-flex align-items-center mb-1">
        <span class="avatar avatar-l me-2 flex-shrink-0">
            @if (isset($hval['user']->profile_image) && file_exists(public_path('storage/profile/' . $hval['user']->profile_image)))
                <img src="{{ asset('storage/profile/' . $hval['user']->profile_image) }}" alt="User Profile Image" class="img-fluid rounded-circle profileImagePreview">
            @else
                <img src="{{ asset('assets/img/profile-default.png') }}" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview">
            @endif
        </span>
        <div>
            <h6 class="mb-1">{{ $hval['user']->user_name ?? '-' }}</h6>
            <p><i class="ti ti-calendar-bolt me-1"></i>Updated {{ \App\Helpers\TimeHelper::getRelativeTime($hval['createdat']) }}</p>
        </div>
    </div>
    <div>
        <div class="border-bottom p-2">
            @php
                $content = $hval['comment'];
                $dom = new DOMDocument();
                @$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

                $imageLinks = [];

                foreach ($dom->getElementsByTagName('img') as $img) {
                    $src = $img->getAttribute('src');
                    $dataFilename = $img->getAttribute('data-filename');
                    $imageName = $dataFilename ?: basename($src);

                    $imageLinks[] = "
                        <div>
                            <a href='{$src}' download='{$imageName}' class='d-flex align-items-center'>
                                <i class='ti ti-download me-2'></i> {$imageName}
                            </a>
                        </div>
                    ";

                    $img->parentNode->removeChild($img);
                }

                $modifiedContent = $dom->saveHTML();
            @endphp

            <div>{!! $modifiedContent !!}</div>
            @foreach ($imageLinks as $link)
                {!! $link !!}
            @endforeach
        </div>
    </div>
</div>