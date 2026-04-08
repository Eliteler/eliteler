@extends('layouts.index', ['nav' => true, 'banner' => false, 'footer' => true, 'cookie' => true, 'setting' => true, 'title' => __($page->section_name)])

@section('content')
    {{-- Custom JS --}}
    @section('custom-script')
        {{-- Custom Style --}}
        <link rel="stylesheet" href="{{ asset('css/custom-style.css') }}">

        {{-- AdSense status --}}
        @if ($settings->adsense_code != 'DISABLE')
            @if ($settings->adsense_code != '')
                {{-- AdSense code --}}
                <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $settings->adsense_code }}"
                    crossorigin="anonymous"></script>
            @endif
        @endif
    @endsection

    <section class="pt-12 lg:pb-20 lg:px-24 overflow-hidden">
        <div class="container mx-auto px-4" data-aos="fade-up">
            <div class="prose prose-lg prose-slate">
                @if (!empty($page->section_content))
                    @foreach (preg_split('/(<[^>]*>)/', $page->section_content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $part)
                        @if (strpos($part, '<') === 0)
                            {!! __($part) !!}
                        @else
                            {{ __($part) }}
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </section>
@endsection
