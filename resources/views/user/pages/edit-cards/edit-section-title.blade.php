@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    <!-- DataTables + Bootstrap + SortableJS -->
    <script src="{{ asset('plugins/drag-and-drop/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/drag-and-drop/Sortable.min.js') }}"></script>
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('success') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="card">
                    <div class="row g-0">
                        <div class="col-12 col-md-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader">{{ __('Update Section Title') }}</h4>
                                <div class="list-group list-group-transparent">
                                    {{-- Nav links --}}
                                    @include('user.pages.edit-cards.includes.nav-link', [
                                        'link' => 'section-titles',
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-10 d-flex flex-column">
                            <form action="{{ route('user.update.section.title', ['id' => $business_card->card_id]) }}" method="post" id="myForm" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <h3 class="card-title mb-4">{{ __('Section Titles') }}</h3>

                                    {{-- Column header labels --}}
                                    <div class="row mb-2">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold text-muted small">
                                                🌐 {{ __('Default Title') }}
                                            </label>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold text-muted small" style="float:right;">
                                                🇸🇦 {{ __('العنوان بالعربية') }}
                                            </label>
                                        </div>
                                    </div>

                                    @foreach ($sectionTitles as $item)
                                        <div class="row mb-3 align-items-center border-bottom pb-3">
                                            {{-- Section label --}}
                                            <div class="col-12 mb-1">
                                                <span class="badge bg-blue-lt">{{ __($item->label) }}</span>
                                            </div>

                                            {{-- Default / Primary title --}}
                                            <div class="col-12 col-md-6">
                                                <label class="form-label required small text-muted">{{ __('Default Title') }}</label>
                                                <input
                                                    type="text"
                                                    name="titles[{{ $item->id }}]"
                                                    value="{{ old('titles.' . $item->id, $item->title) }}"
                                                    class="form-control editable-input"
                                                    placeholder="{{ __('Enter title') }}"
                                                    minlength="1" maxlength="100" required
                                                >
                                            </div>

                                            {{-- Arabic title --}}
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small text-muted" style="float:right; width:100%; text-align:right;">
                                                    {{ __('العنوان بالعربية') }}
                                                    <span class="text-muted">({{ __('اختياري') }})</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    name="titles_ar[{{ $item->id }}]"
                                                    value="{{ old('titles_ar.' . $item->id, $item->title_ar) }}"
                                                    class="form-control editable-input text-end"
                                                    placeholder="أدخل العنوان بالعربية"
                                                    dir="rtl"
                                                    maxlength="100"
                                                >
                                            </div>
                                        </div>
                                    @endforeach

                                </div>
                                <div class="card-footer text-end">
                                    <div class="d-flex">
                                        <a href="{{ route('user.cards') }}"
                                            class="btn btn-outline-primary ms-2">{{ __('Cancel') }}</a>

                                        {{-- Next link --}}
                                        @php
                                            $route = route('user.edit.intro-screen', Request::segment(3));

                                            if ($plan_details->google_wallet == 1 && is_dir(base_path('plugins/GoogleWallet')) && $business_card->type != 'personal') {
                                                $route = route('user.edit.google-wallet', Request::segment(3));
                                            }
                                        @endphp

                                        <a href="{{ $route }}" class="btn btn-outline-primary ms-2">
                                            {{ __('Skip') }}
                                        </a>
                                        
                                        <button id="saveOrder" class="btn btn-primary ms-auto">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>

    {{-- Custom JS --}}
    @push('custom-js')
        <script>
            // Init DataTable - disabled since we switched to a simpler list layout
            // let table = new DataTable('#sectionTable', { ... });

            // Enable drag/drop on tbody (disabled for now)
            // let el = document.getElementById('sortable');
            // Sortable.create(el, { animation: 150, handle: '.handle' });
        </script>
    @endpush
@endsection
