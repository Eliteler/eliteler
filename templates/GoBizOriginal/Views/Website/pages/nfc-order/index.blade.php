@extends('GoBizOriginal::Website.layouts.index', ['nav' => true, 'banner' => false, 'footer' => true, 'cookie' => true, 'setting' => true, 'title' => __('Smart Products')])

{{-- Custom CSS --}}
@section('custom-script')
    <!-- Include Alpine.js -->
    <script src="{{ asset('app/js/alpinejs.js') }}" defer></script>
@endsection

@section('content')
    <section class="pt-8 pb-28">
        <div class="container mx-auto px-4">
            <div class="flex justify-center mb-6">
                <div class="bg-{{ $template_config->template_color }}-100 text-{{ $template_config->template_color }}-600 px-2 py-1 text-xs font-bold inline-block rounded">
                    {{ __('Smart Products') }}</div>
            </div>
            <h1 class="font-heading text-center text-4xl lg:text-5xl font-bold mb-4 max-w-3xl mx-auto">
                {{ __('Smart NFC Products For Your Business') }}
            </h1>
            <p class="text-gray-600 text-center mb-12 max-w-2xl mx-auto">
                {{ __('NFC cards, review stands, tags and more. Each one opens your business card, store or any link with a tap, and you can change it any time.') }}
            </p>

            @include('GoBizOriginal::Website.includes.smart-product-grid')
        </div>
    </section>
@endsection
