@extends('GoBizOriginal::Website.layouts.index', ['nav' => true, 'banner' => false, 'footer' => true, 'cookie' => true, 'setting' => true])

{{-- Custom JS --}}
@section('custom-script')
    {{-- Blog CSS --}}
    <link rel="stylesheet" href="{{ asset('gobiz_original_assets/css/blog.css') }}">

    {{-- AdSense status --}}
    @if ($settings->adsense_code != 'DISABLE')
        {{-- AdSense code --}}
        <script async
            src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $settings->google_adsense_code }}"
            crossorigin="anonymous"></script>
    @endif
@endsection

@section('content')
    {{-- Start Blog Section --}}
    <section class="blog-section">
        <div class="blog-container">

            <div class="blog-header">
                <h2>{{ __('Blogs') }}</h2>
                <p>{{ __('Discover the All-in-One Solution to Manage Contacts, Share Business Info, and Boost Sales - Start Growing Your Business Today!') }}
                </p>
            </div>

            <div class="blog-grid">

                @if (count($blogs) > 0)
                    @foreach ($blogs as $blog)
                        <article class="blog-card">
                            <a href="{{ route('view.blog', $blog->slug) }}">

                                <div class="blog-image">
                                    <img src="{{ asset($blog->cover_image) }}" alt="{{ __($blog->heading) }}">
                                </div>

                                <div class="blog-content">
                                    <span class="blog-date">
                                        {{ Carbon\Carbon::parse($blog->created_at)->format('d M Y') }}
                                    </span>

                                    <h3>{{ __($blog->heading) }}</h3>

                                    <p>{{ __($blog->short_description) }}</p>
                                </div>

                            </a>
                        </article>
                    @endforeach
                @else
                    <div class="no-blog">
                        <h3>{{ __('No blog posts found!') }}</h3>
                    </div>
                @endif

            </div>

            {{-- Pagination --}}
            <div class="blog-pagination">
                {{ $blogs->links('vendor.pagination.blog') }}
            </div>

        </div>
    </section>
    {{-- End Blog Section --}}
@endsection
