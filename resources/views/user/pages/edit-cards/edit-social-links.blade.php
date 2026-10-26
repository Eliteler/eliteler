@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS for Enhanced Typography, Readability & Modern Tabler Design --}}
@section('css')
    {{-- Font Awesome CSS --}}
    <link rel="stylesheet" href="{{ asset('css/all.css') }}" />

    {{-- Social Links CSS --}}
    <link rel="stylesheet" href="{{ asset('css/social-links.css') }}" />
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.cards') }}" class="text-secondary text-decoration-none">
                                    {{ __('Digital Business Cards') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Social Links') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold">
                            {{ __('Social Links') }}
                        </h2>
                        <div class="text-secondary mt-1">
                            {{ __('Modify active interactive links, attach verified social media pages, embed localized maps, and manage sections priority.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Alerts fallback --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                    <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                    </div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                    <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                    </div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
                @endif
            
                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        <!-- Sidebar navigation -->
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body p-3">
                                <h4 class="subheader mb-3 text-uppercase fw-bold text-muted">{{ __('Card Settings') }}</h4>
                                <div class="list-group list-group-transparent">
                                    @include('user.pages.edit-cards.includes.nav-link', [
                                        'link' => 'links',
                                    ])
                                </div>
                            </div>
                        </div>

                        <!-- Main Form Area -->
                        <div class="col-12 col-md-9 col-lg-10 d-flex flex-column">
                            <form action="{{ route('user.update.social.links', Request::segment(3)) }}" method="post"
                                id="myForm">
                                @csrf
                                <div class="card-body p-lg-3">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div>
                                            <h3 class="card-title mb-1">{{ __('Manage Links & Socials') }}</h3>
                                            <p class="text-muted small mb-0">
                                                {{ __('Add phone numbers, WhatsApp, websites, social networks or embed maps.') }}
                                            </p>
                                        </div>

                                        <button type="button" onclick="addFeature()"
                                            class="btn btn-primary btn-icon-only d-inline-flex align-items-center shadow-sm fw-semibold">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20"
                                                height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <line x1="12" y1="5" x2="12" y2="19" />
                                                <line x1="5" y1="12" x2="19" y2="12" />
                                            </svg>
                                            <span class="d-none d-sm-inline-block">{{ __('Add New Link') }}</span>
                                        </button>
                                    </div>

                                    {{-- Empty State when no links exist --}}
                                    <div id="no-links-empty"
                                        class="{{ count($features) > 0 ? 'd-none' : '' }} py-5 text-center">
                                        <div class="empty">
                                            <div class="empty-icon mb-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-social">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M10 5a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                    <path d="M3 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                    <path d="M17 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                    <path d="M9 14a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                                    <path d="M12 7l0 4" />
                                                    <path d="M6.7 17.8l2.8 -2" />
                                                    <path d="M17.3 17.8l-2.8 -2" />
                                                </svg>
                                            </div>
                                            <p class="empty-title h4 font-weight-bold mb-2 text-dark">
                                                {{ __('No social links added yet') }}</p>
                                            <p class="empty-subtitle text-secondary mb-3">
                                                {{ __('Click the button below to add your first interactive social or contact link.') }}
                                            </p>
                                            <button type="button" onclick="addFeature()"
                                                class="btn btn-primary fw-semibold">
                                                <i class="fa-solid fa-plus me-1"></i> {{ __('Add Your First Link') }}
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Loop Existing Features --}}
                                    <div id="features-container">
                                        @php
                                            $excludedCards = [
                                                '588969111014',
                                                '588969111015',
                                                '588969111016',
                                                '588969111017',
                                                '588969111018',
                                                '588969111019',
                                                '588969111020',
                                                '588969111021',
                                            ];
                                        @endphp

                                        @for ($i = 0; $i < count($features); $i++)
                                            <div class="social-link-row" id="{{ $i }}">
                                                <div class="row g-3 align-items-end">
                                                    <!-- Display Type -->
                                                    <div class="col-md-3 col-lg-3">
                                                        <label class="form-label required"
                                                            for="type{{ $features[$i]->id }}">{{ __('Display Type') }}</label>
                                                        <select
                                                            class="type{{ $features[$i]->id }} defaultType form-control"
                                                            id="type{{ $features[$i]->id }}" name="type[]"
                                                            onchange="changeLabel({{ $features[$i]->id }})" required>
                                                            <optgroup label="{{ __('Social Links') }}">
                                                                <option value="facebook"
                                                                    {{ $features[$i]->type == 'facebook' ? 'selected' : '' }}>
                                                                    {{ __('Facebook') }}</option>
                                                                <option value="instagram"
                                                                    {{ $features[$i]->type == 'instagram' ? 'selected' : '' }}>
                                                                    {{ __('Instagram') }}</option>
                                                                <option value="x-twitter"
                                                                    {{ $features[$i]->type == 'x-twitter' ? 'selected' : '' }}>
                                                                    {{ __('X (Twitter)') }}</option>
                                                                <option value="linkedin"
                                                                    {{ $features[$i]->type == 'linkedin' ? 'selected' : '' }}>
                                                                    {{ __('LinkedIn') }}</option>
                                                                <option value="pinterest"
                                                                    {{ $features[$i]->type == 'pinterest' ? 'selected' : '' }}>
                                                                    {{ __('Pinterest') }}</option>
                                                                <option value="reddit"
                                                                    {{ $features[$i]->type == 'reddit' ? 'selected' : '' }}>
                                                                    {{ __('Reddit') }}</option>
                                                                <option value="tiktok"
                                                                    {{ $features[$i]->type == 'tiktok' ? 'selected' : '' }}>
                                                                    {{ __('Tiktok') }}</option>
                                                                <option value="threads"
                                                                    {{ $features[$i]->type == 'threads' ? 'selected' : '' }}>
                                                                    {{ __('Threads') }}</option>
                                                                <option value="snapchat"
                                                                    {{ $features[$i]->type == 'snapchat' ? 'selected' : '' }}>
                                                                    {{ __('Snapchat') }}</option>
                                                                <option value="wechat"
                                                                    {{ $features[$i]->type == 'wechat' ? 'selected' : '' }}>
                                                                    {{ __('WeChat') }}</option>
                                                                <option value="telegram"
                                                                    {{ $features[$i]->type == 'telegram' ? 'selected' : '' }}>
                                                                    {{ __('Telegram') }}</option>
                                                                <option value="tumblr"
                                                                    {{ $features[$i]->type == 'tumblr' ? 'selected' : '' }}>
                                                                    {{ __('Tumblr') }}</option>
                                                                <option value="qq"
                                                                    {{ $features[$i]->type == 'qq' ? 'selected' : '' }}>
                                                                    {{ __('QQ') }}</option>
                                                                <option value="discord"
                                                                    {{ $features[$i]->type == 'discord' ? 'selected' : '' }}>
                                                                    {{ __('Discord') }}</option>
                                                                <option value="quora"
                                                                    {{ $features[$i]->type == 'quora' ? 'selected' : '' }}>
                                                                    {{ __('Quora') }}</option>
                                                                <option value="wa"
                                                                    {{ $features[$i]->type == 'wa' ? 'selected' : '' }}>
                                                                    {{ __('WhatsApp') }}</option>
                                                                <option value="email"
                                                                    {{ $features[$i]->type == 'email' ? 'selected' : '' }}>
                                                                    {{ __('Email') }}</option>
                                                                <option value="tel"
                                                                    {{ $features[$i]->type == 'tel' ? 'selected' : '' }}>
                                                                    {{ __('Phone') }}</option>
                                                                <option value="url"
                                                                    {{ $features[$i]->type == 'url' ? 'selected' : '' }}>
                                                                    {{ __('Link') }}</option>
                                                                <option value="g-review"
                                                                    {{ $features[$i]->type == 'g-review' ? 'selected' : '' }}>
                                                                    {{ __('Google Review') }}</option>
                                                                
                                                                @if (!in_array($business_card->theme_id, $excludedCards))
                                                                    <option value="address"
                                                                    {{ $features[$i]->type == 'address' ? 'selected' : '' }}>
                                                                    {{ __('Address') }}</option>
                                                                    <option value="text"
                                                                    {{ $features[$i]->type == 'text' || $features[$i]->type == 'textarea' ? 'selected' : '' }}>
                                                                    {{ __('Custom Text') }}</option>
                                                                @endif
                                                            </optgroup>

                                                            @if (!in_array($business_card->theme_id, $excludedCards))
                                                             <optgroup label="{{ __('Sections') }}">
                                                                    <option value="map"
                                                                    {{ $features[$i]->type == 'map' ? 'selected' : '' }}>
                                                                    {{ __('Google Map') }}</option>
                                                                    <option value="location_url"
                                                                    {{ $features[$i]->type == 'location_url' ? 'selected' : '' }}>
                                                                    {{ __('Location without Map') }}</option>
                                                                    <option value="youtube"
                                                                    {{ $features[$i]->type == 'youtube' ? 'selected' : '' }}>
                                                                    {{ __('Youtube') }}</option>
                                                                    <option value="instagram-embed"
                                                                        {{ $features[$i]->type == 'instagram-embed' ? 'selected' : '' }}>
                                                                        {{ __('Instagram Post / Reel') }}</option>
                                                                    <option value="iframe"
                                                                    {{ $features[$i]->type == 'iframe' ? 'selected' : '' }}>
                                                                    {{ __('iframe') }}</option>
                                                            </optgroup>
                                                            @endif
                                                        </select>
                                                    </div>

                                                    <!-- Icon Selector Button -->
                                                    <div class="col-auto">
                                                        <label class="form-label required">{{ __('Icon') }}</label>
                                                        <div>
                                                            <button type="button" id="iconpick{{ $features[$i]->id }}"
                                                                class="btn btn-primary btn-icon-square text-white shadow-sm icon-picker-trigger"
                                                                data-bs-toggle="modal" data-bs-target="#iconPickerModal"
                                                                data-id="{{ $features[$i]->id }}">
                                                                <i id="displayIcon{{ $features[$i]->id }}"
                                                                    class="{{ $features[$i]->icon }}"></i>
                                                        </button>
                                                            <input type="hidden"
                                                                class="icon{{ $features[$i]->id }} form-control"
                                                                value="{{ $features[$i]->icon }}" name="icon[]" required
                                                                readonly>
                                                    </div>
                                                </div>

                                                    <!-- Label Input -->
                                                    <div class="col-md-3">
                                                        <label
                                                            class="form-label">{{ __('Label / Title') }}</label>
                                                        <input type="text"
                                                            class="lbl{{ $features[$i]->id }} form-control"
                                                            name="label[]" placeholder="{{ __('Label') }}"
                                                            value="{{ $features[$i]->label }}">
                                                    </div>

                                                    <!-- Content Value Input -->
                                                    <div class="col-md">
                                                        <label
                                                            class="form-label required">{{ __('Content / URL') }}</label>
                                                        <input
                                                            type="{{ $features[$i]->type == 'instagram-embed' ? 'url' : 'text' }}"
                                                            class="textlbl{{ $features[$i]->id }} form-control font-monospace"
                                                            name="value[]" placeholder="{{ __('Content') }}"
                                                            value="{{ $features[$i]->content }}" required>
                                                        <small class='notelbl{{ $features[$i]->id }} text-danger' style="{{ $features[$i]->type == 'map' ? '' : 'display:none;' }}">
                                                            {{ __('Please note: You must insert the HTML embed iframe code generated from Google Maps. Standard links will not work.') }}
                                                        </small>
                                                    </div>

                                                    <!-- Solid Red Delete Button Perfectly Aligned -->
                                                    <div class="col-auto">
                                                        <button type="button"
                                                            class="btn btn-danger btn-icon-only btn-icon-square shadow-sm"
                                                            onclick="removeFeature({{ $i }})"
                                                            title="{{ __('Remove Link') }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                                width="20" height="20" viewBox="0 0 24 24"
                                                                stroke-width="2" stroke="currentColor" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <line x1="4" y1="7" x2="20"
                                                                    y2="7" />
                                                                <line x1="10" y1="11" x2="10"
                                                                    y2="17" />
                                                                <line x1="14" y1="11" x2="14"
                                                                    y2="17" />
                                                                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor

                                        {{-- Dynamic Appended Elements Target --}}
                                        <div id="more-features"></div>
                                    </div>
                                </div>

                                <!-- Form Footer (Only Save Changes Button) -->
                                <div class="card-footer border-top d-flex align-items-center justify-content-end py-3">
                                    <button type="submit"
                                        class="btn btn-primary d-inline-flex align-items-center shadow-sm px-4 fw-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="20"
                                            height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M14 4l0 4l-6 0l0 -4" />
                                        </svg>
                                        {{ __('Save') }}
                                            </button>
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

    <!-- Icon Picker Modal -->
    <div class="modal modal-bg fade" id="iconPickerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <!-- Responsive Header -->
                <div class="modal-header border-bottom py-3 px-3 px-sm-4 bg-surface">
                    <div class="icon-modal-header">
                        <div class="d-flex-head d-flex align-items-center justify-content-between">
                            <h5 class="modal-title font-weight-bold mb-0 text-dark">{{ __('Choose an Icon') }}</h5>
                            <!-- Mobile Close button -->
                            <button type="button" class="btn-close d-sm-none" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                </div>

                        <div class="d-flex align-items-center flex-grow-1 flex-sm-grow-0">
                            <div class="icon-modal-search-box">
                                <span class="theme-search-icon"
                                    style="position: absolute; left: 12px; top: 10px; color: #64748b;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <circle cx="10" cy="10" r="7" />
                                        <line x1="21" y1="21" x2="15" y2="15" />
                                    </svg>
                                </span>
                                <input type="text" id="iconSearchInput" class="icon-modal-search-input"
                                    placeholder="{{ __('Search icon...') }}" autocomplete="off" />
                    </div>

                            <!-- Desktop Close button -->
                            <button type="button" class="btn-close d-none d-sm-block ms-1" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Body Grid -->
                <div class="modal-body p-3 p-sm-4"
                    style="background-color: var(--tblr-bg-surface-secondary, #f8fafc); min-height: 280px; max-height: 480px;">
                    @php
                        $icons = include resource_path('views/user/includes/icons.php');
                    @endphp

                    <div class="row g-2" id="iconList">
                        @foreach ($icons as $icon)
                            <div class="col-3 col-sm-2 col-md-2 col-lg-1 icon-item"
                                data-icon-name="{{ $icon }}">
                                <div role="button" class="icon-option-card icon-option"
                                    data-icon="{{ $icon }}" title="{{ $icon }}">
                                    <i class="{{ $icon }} fa-lg"></i>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal modal-blur fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="modal-title">{{ __('Are you sure?') }}</div>
                    <div class="text-muted mt-1">{{ __('Do you want to delete this item?') }}</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary me-auto" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">{{ __('Yes, Delete') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
        <script type="text/javascript" src="{{ asset('js/fontawesome-iconpicker.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>

        <script>
            var count = {{ count($features) }};

        function checkEmptyState() {
            if (count === 0) {
                $('#no-links-empty').removeClass('d-none');
            } else {
                $('#no-links-empty').addClass('d-none');
            }
        }

            function addFeature() {
                "use strict";
                if (count >= {{ $plan_details->no_of_links }}) {
                    new swal({
                        title: `{{ __('Oops!') }}`,
                        icon: 'warning',
                        text: `{{ __('You have reached your current plan limit.') }}`,
                        timer: 2000,
                        buttons: false,
                        showConfirmButton: false,
                    });
                } else {
                    count++;
                checkEmptyState();

                var id = getRandomInt();
                    let card_details = "{{ $business_card->theme_id }}";

                    let excludedCards = [
                    "588969111014", "588969111015", "588969111016", "588969111017",
                    "588969111018", "588969111019", "588969111020", "588969111021"
                    ];

                    let businessOptions = '';
                    let sectionOptions = '';
                    if (!excludedCards.includes(card_details)) {
                        businessOptions = "<option value='address'>{{ __('Address') }}</option>";
                        businessOptions += "<option value='text'>{{ __('Custom Text') }}</option>";

                        sectionOptions = "<optgroup label='{{ __('Sections') }}'>";
                        sectionOptions += "<option value='map'>{{ __('Google Map') }}</option>";
                        sectionOptions += "<option value='location_url'>{{ __('Location without Map') }}</option>";
                        sectionOptions += "<option value='youtube'>{{ __('Youtube') }}</option>";
                    sectionOptions += "<option value='instagram-embed'>{{ __('Instagram Post / Reel') }}</option>";
                        sectionOptions += "<option value='iframe'>{{ __('iframe') }}</option>";
                        sectionOptions += "</optgroup>";
                    }

                var features = `
                    <div class='social-link-row' id='${id}'>
                        <div class='row g-3 align-items-end'>
                            <div class='col-md-3 col-lg-3'>
                                <label class='form-label required' for='type${id}'>{{ __('Display Type') }}</label>
                                <select class="type${id} form-control" id="type${id}" name="type[]" onchange='changeLabel(${id})' required>
                                            <optgroup label="{{ __('Social Links') }}">
                                                <option value='facebook'>{{ __('Facebook') }}</option>
                                                <option value='instagram'>{{ __('Instagram') }}</option>
                                                <option value='x-twitter'>{{ __('X (Twitter)') }}</option>
                                                <option value='linkedin'>{{ __('LinkedIn') }}</option>
                                                <option value='pinterest'>{{ __('Pinterest') }}</option>
                                                <option value='reddit'>{{ __('Reddit') }}</option>
                                                <option value='tiktok'>{{ __('Tiktok') }}</option>
                                                <option value='threads'>{{ __('Threads') }}</option>
                                                <option value='snapchat'>{{ __('Snapchat') }}</option>
                                                <option value='wechat'>{{ __('WeChat') }}</option>
                                                <option value='telegram'>{{ __('Telegram') }}</option>
                                                <option value='tumblr'>{{ __('Tumblr') }}</option>
                                                <option value='qq'>{{ __('QQ') }}</option>
                                                <option value='discord'>{{ __('Discord') }}</option>
                                                <option value='quora'>{{ __('Quora') }}</option>
                                                <option value='wa'>{{ __('WhatsApp') }}</option>
                                                <option value='email'>{{ __('Email') }}</option>
                                                <option value='tel'>{{ __('Phone') }}</option>
                                                <option value='url'>{{ __('Link') }}</option>
                                                <option value='g-review'>{{ __('Google Review') }}</option>
                                                ${businessOptions}
                                            </optgroup>
                                            ${sectionOptions}
                                        </select>
                                    </div>

                            <div class='col-auto'>
                                        <label class='form-label required'>{{ __('Icon') }}</label>
                                <div>
                                    <button type="button" id='iconpick${id}' class="btn btn-primary btn-icon-square text-white shadow-sm icon-picker-trigger" data-bs-toggle="modal" data-bs-target="#iconPickerModal" data-id="${id}">
                                        <i id="displayIcon${id}" class="fab fa-facebook-f"></i>
                                        </button>
                                    <input type='hidden' class='icon${id} form-control' value="fab fa-facebook-f" name='icon[]' required readonly>
                                    </div>
                                </div>

                            <div class='col-md-3'>
                                <label class='form-label'>{{ __('Label / Title') }}</label>
                                <input type='text' class='lbl${id} form-control' name='label[]' placeholder='{{ __('Facebook') }}' value="Facebook">
                                </div>

                            <div class='col-md'>
                                <label class='form-label required'>{{ __('Content / URL') }}</label>
                                <input type='text' class='textlbl${id} form-control font-monospace' name='value[]' placeholder='{{ __('For ex: https://facebook.com') }}' required>
                                <small class='notelbl${id} text-danger' style='display:none;'></small>
                                </div>

                            <div class='col-auto'>
                                <button type="button" class='btn btn-danger btn-icon-only btn-icon-square shadow-sm' onclick='removeFeature(${id})' title='{{ __('Remove Link') }}'>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>`;

                $("#more-features").append(features);
                    dynamicSelect('type' + id);
                }
            }

            var pendingDeleteId = null;

            function removeFeature(id) {
                "use strict";
                pendingDeleteId = id;
                $('#confirmDeleteModal').modal('show');
            }

            $('#confirmDeleteBtn').on('click', function() {
                if (pendingDeleteId !== null) {
                    $("#" + pendingDeleteId).remove();
                    count--;
                    checkEmptyState();
                    pendingDeleteId = null;
                }
                $('#confirmDeleteModal').modal('hide');
            });

            function getRandomInt() {
            var min = Math.ceil(0);
            var max = Math.floor(9999999999);
            return Math.floor(Math.random() * (max - min) + min);
            }

        // Icon Search Filter with Placeholder Glow & Tabler Empty State
        var iconSearchTimer;
        $('#iconSearchInput').on('input', function() {
                "use strict";
                const search = $(this).val().toLowerCase().trim();

            clearTimeout(iconSearchTimer);

            // Trigger Tabler Skeleton Skeletons instantly
            let skeletonHtml = '';
            for (let i = 0; i < 16; i++) {
                skeletonHtml += `
                    <div class="col-3 col-sm-2 col-md-2 col-lg-1 icon-skeleton placeholder-glow">
                        <div class="placeholder rounded-2 w-100" style="height: 48px;"></div>
                    </div>`;
            }

            // Temporarily hide actual icon elements and show skeletons
            $('#iconList > div.icon-item').hide();
            $('#icon-empty-state').remove();
            $('.icon-skeleton').remove();
            $('#iconList').append(skeletonHtml);

            iconSearchTimer = setTimeout(function() {
                $('.icon-skeleton').remove();

                if (search === '') {
                    $('#iconList > div.icon-item').show();
                    return;
                }

                let matchCount = 0;
                $('#iconList > div.icon-item').each(function() {
                    const fullIconName = $(this).attr('data-icon-name') || "";
                    if (fullIconName.toLowerCase().includes(search)) {
                        $(this).show();
                        matchCount++;
                    } else {
                        $(this).hide();
                    }
                });

                // Tabler Clean Empty State
                if (matchCount === 0) {
                    let emptyStateHtml = `
                        <div class="col-12 py-5" id="icon-empty-state">
                            <div class="empty text-center py-3">
                                <div class="empty-icon mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-secondary" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <circle cx="10" cy="10" r="7"/>
                                        <line x1="21" y1="21" x2="15" y2="15"/>
                                        <line x1="10" y1="13" x2="10" y2="13.01"/>
                                        <line x1="10" y1="7" x2="10" y2="10"/>
                                    </svg>
                                </div>
                                <p class="empty-title h4 font-weight-bold mb-1 text-dark">{{ __('No icons found') }}</p>
                                <p class="empty-subtitle text-secondary mb-0 small">
                                    {{ __('Try searching for a different keyword or brand name.') }}
                                </p>
                            </div>
                        </div>`;
                    $('#iconList').append(emptyStateHtml);
                }
            }, 250);
        });

        // Reset search on modal close
        $('#iconPickerModal').on('hidden.bs.modal', function() {
            $('#iconSearchInput').val('');
            $('#icon-empty-state').remove();
            $('.icon-skeleton').remove();
            $('#iconList > div.icon-item').show();
            });

            let selectedIconId = null;
        $(document).on('click', '.icon-picker-trigger', function() {
                selectedIconId = $(this).data('id');
            });

        $(document).on('click', '.icon-option', function() {
                "use strict";
                const selectedIcon = $(this).data('icon');
                $('.icon' + selectedIconId).val(selectedIcon);
            $('#displayIcon' + selectedIconId).attr('class', `${selectedIcon}`);
                $('#iconPickerModal').modal('hide');
            });

            function changeLabel(id) {
                "use strict";
                var label = `{{ __('Label') }}`;
                var textlabel = `{{ __('Type something') }}`;
                let icon = document.querySelector('.icon' + id);
                let lbl = document.querySelector('.lbl' + id);
                let textlbl = document.querySelector('.textlbl' + id);
                let type = document.querySelector('.type' + id).value;
                let notelbl = $(".notelbl" + id);
                notelbl.hide();

                if (type == 'address') {
                    label = `{{ __('Address') }}`;
                    textlabel = `{{ __('For ex: Chennai, Tamilnadu, India') }}`;
                    icon.value = "fas fa-home";
                    lbl.value = `{{ __('Address') }}`;
                $("#displayIcon" + id).attr("class", "fas fa-home text-white");
                    textlbl.type = 'text';
                } else if (type == 'text') {
                    label = `{{ __('About us') }}`;
                    textlabel = `{{ __('For ex: Lorem Ipsum is simply dummy text of') }}`;
                    icon.value = "fas fa-info-circle";
                    lbl.value = `{{ __('Content') }}`;
                $("#displayIcon" + id).attr("class", "fas fa-info-circle text-white");
                    textlbl.type = 'text';
                } else if (type == 'email') {
                    label = `{{ __('Email Address') }}`;
                    textlabel = `{{ __('For ex: support@website.com') }}`;
                    icon.value = "fas fa-at";
                    lbl.value = `{{ __('Email') }}`;
                $("#displayIcon" + id).attr("class", "fas fa-at text-white");
                    textlbl.type = 'email';
                } else if (type == 'tel') {
                    label = `{{ __('Phone Number') }}`;
                    textlabel = `{{ __('For ex: +971... or 00971...') }}`;

                    icon.value = "fas fa-phone";
                    lbl.value = `{{ __('Phone') }}`;
                $("#displayIcon" + id).attr("class", "fas fa-phone text-white");
                textlbl.type = 'text';
                } else if (type == 'wa') {
                    label = `{{ __('WhatsApp') }}`;
                    textlabel = `{{ __('For ex: +971... or 00971...') }}`;

                    icon.value = "fab fa-whatsapp";
                    lbl.value = `{{ __('WhatsApp') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-whatsapp text-white");
                textlbl.type = 'text';
                } else if (type == 'url') {
                    label = `{{ __('Website') }}`;
                    textlabel = `{{ __('For ex: website.com') }}`;
                    icon.value = "fas fa-link";
                    lbl.value = `{{ __('Website') }}`;
                $("#displayIcon" + id).attr("class", "fas fa-link text-white");
                textlbl.type = 'text';
                } else if (type == 'youtube') {
                    label = `{{ __('Video Title') }}`;
                    textlabel = `{{ __('For ex: https://www.youtube.com/watch?v=li5ths352') }}`;
                    icon.value = "fab fa-youtube";
                    lbl.value = `{{ __('Youtube') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-youtube text-white");
                textlbl.type = 'text';
                } else if (type == 'map') {
                    label = `{{ __('California') }}`;
                textlabel = `{!! __(
                    "For ex: <iframe src='https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3924.7798234706065!2d77.98194106479716!3d10.359482142605264!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3b00ab9baf4b4101%3A0x9d6d57a812be5cc6!2sCapsimint%20Technologies%20-%20Web%2C%20Mobile%20App%20and%20Software%20Development%20Company!5e0!3m2!1sen!2sin!4v1638283593135!5m2!1sen!2sin'></iframe>",
                ) !!}`;
                    icon.value = "fas fa-location-arrow";
                    lbl.value = `{{ __('Location') }}`;
                $("#displayIcon" + id).attr("class", "fas fa-location-arrow text-white");
                    textlbl.type = 'text';
                    notelbl.text("{{ __('Please note: You must add the HTML embed iframe code generated from Google Maps. Standard links will not work.') }}");
                    notelbl.show();

                } else if (type == 'facebook') {
                    label = `{{ __('Facebook') }}`;
                    textlabel = `{{ __('Ex: username or https://facebook.com/username') }}`;

                    icon.value = "fab fa-facebook-f";
                    lbl.value = `{{ __('Facebook') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-facebook-f text-white");
                textlbl.type = 'text';
                } else if (type == 'instagram') {
                    label = `{{ __('Instagram') }}`;
                    textlabel = `{{ __('Ex: username or https://instagram.com/username') }}`;

                    icon.value = "fab fa-instagram";
                    lbl.value = `{{ __('Instagram') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-instagram text-white");
                textlbl.type = 'text';
            } else if (type == 'instagram-embed') {
                label = `{{ __('Instagram Post / Reel') }}`;
                textlabel = `{{ __('For ex: https://www.instagram.com/reel/C_abc123/ or /p/C_abc123/') }}`;
                icon.value = "fab fa-instagram";
                lbl.value = `{{ __('Instagram Post') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-instagram text-white");
                textlbl.type = 'url';
                } else if (type == 'x-twitter') {
                    label = `{{ __('X (Twitter)') }}`;
                    textlabel = `{{ __('Ex: username or https://x.com/username') }}`;

                    icon.value = "fab fa-x-twitter";
                    lbl.value = `{{ __('X (Twitter)') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-x-twitter text-white");
                textlbl.type = 'text';
                } else if (type == 'linkedin') {
                    label = `{{ __('LinkedIn') }}`;
                    textlabel = `{{ __('For ex: https://linkedin.com') }}`;
                    icon.value = "fab fa-linkedin-in";
                    lbl.value = `{{ __('LinkedIn') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-linkedin-in text-white");
                textlbl.type = 'text';
                } else if (type == 'pinterest') {
                    label = `{{ __('Pinterest') }}`;
                    textlabel = `{{ __('For ex: https://pinterest.com') }}`;
                    icon.value = "fab fa-pinterest";
                    lbl.value = `{{ __('Pinterest') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-pinterest text-white");
                textlbl.type = 'text';
                } else if (type == 'reddit') {
                    label = `{{ __('Reddit') }}`;
                    textlabel = `{{ __('For ex: https://reddit.com') }}`;
                    icon.value = "fab fa-reddit";
                    lbl.value = `{{ __('Reddit') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-reddit text-white");
                textlbl.type = 'text';
                } else if (type == 'tiktok') {
                    label = `{{ __('Tiktok') }}`;
                    textlabel = `{{ __('For ex: https://tiktok.com') }}`;
                    icon.value = "fab fa-tiktok";
                    lbl.value = `{{ __('Tiktok') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-tiktok text-white");
                textlbl.type = 'text';
                } else if (type == 'threads') {
                    label = `{{ __('Threads') }}`;
                    textlabel = `{{ __('For ex: https://threads.net') }}`;
                    icon.value = "fab fa-threads";
                    lbl.value = `{{ __('Threads') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-threads text-white");
                textlbl.type = 'text';
                } else if (type == 'snapchat') {
                    label = `{{ __('Snapchat') }}`;
                    textlabel = `{{ __('For ex: https://snapchat.com') }}`;
                    icon.value = "fab fa-snapchat";
                    lbl.value = `{{ __('Snapchat') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-snapchat text-white");
                textlbl.type = 'text';
                } else if (type == 'wechat') {
                    label = `{{ __('WeChat') }}`;
                    textlabel = `{{ __('For ex: https://wechat.com') }}`;
                    icon.value = "fab fa-weixin";
                    lbl.value = `{{ __('WeChat') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-weixin text-white");
                textlbl.type = 'text';
                } else if (type == 'telegram') {
                    label = `{{ __('Telegram') }}`;
                    textlabel = `{{ __('For ex: https://telegram.org') }}`;
                    icon.value = "fab fa-telegram";
                    lbl.value = `{{ __('Telegram') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-telegram text-white");
                textlbl.type = 'text';
                } else if (type == 'tumblr') {
                    label = `{{ __('Tumblr') }}`;
                    textlabel = `{{ __('For ex: https://tumblr.com') }}`;
                    icon.value = "fab fa-tumblr";
                    lbl.value = `{{ __('Tumblr') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-tumblr text-white");
                textlbl.type = 'text';
                } else if (type == 'qq') {
                    label = `{{ __('QQ') }}`;
                    textlabel = `{{ __('For ex: https://qq.com') }}`;
                    icon.value = "fab fa-qq";
                    lbl.value = `{{ __('QQ') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-qq text-white");
                textlbl.type = 'text';
                } else if (type == 'discord') {
                    label = `{{ __('Discord') }}`;
                    textlabel = `{{ __('For ex: https://discord.com') }}`;
                    icon.value = "fab fa-discord";
                    lbl.value = `{{ __('Discord') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-discord text-white");
                textlbl.type = 'text';
                } else if (type == 'quora') {
                    label = `{{ __('Quora') }}`;
                    textlabel = `{{ __('For ex: https://quora.com') }}`;
                    icon.value = "fab fa-quora";
                    lbl.value = `{{ __('Quora') }}`;
                $("#displayIcon" + id).attr("class", "fab fa-quora text-white");
                textlbl.type = 'text';
                } else if (type == 'iframe') {
                    label = `{{ __('src Link') }}`;
                    textlabel = `{{ __('For ex: https://domain.com?feed=152856') }}`;
                    icon.value = "fas fa-rss";
                    lbl.value = `{{ __('iframe') }}`;
                $("#displayIcon" + id).attr("class", "fas fa-rss text-white");
                textlbl.type = 'text';
                } else if (type == 'g-review') {
                    label = `{{ __('Google Review') }}`;
                textlabel = `{{ __('For ex: https://www.google.com/maps/place/...') }}`;
                    icon.value = "fas fa-star";
                    lbl.value = `{{ __('Google Review') }}`;
                $("#displayIcon" + id).attr("class", "fas fa-star text-white");
                textlbl.type = 'text';
            } else if (type == 'location_url') {
                label = `{{ __('Location') }}`;
                textlabel = `{{ __('For ex: https://maps.app.goo.gl/...') }}`;
                icon.value = "fas fa-location-dot";
                lbl.value = `{{ __('Location') }}`;
                $("#displayIcon" + id).attr("class", "fas fa-location-dot text-white");
                textlbl.type = 'text';
                }

            if (lbl) lbl.placeholder = label;
            if (textlbl) textlbl.placeholder = textlabel;
            }

            document.getElementById("myForm").onkeypress = function(e) {
                var key = e.charCode || e.keyCode || 0;
                if (key == 13) {
                    e.preventDefault();
                }
            }

            $(document).ready(function() {
                $('.defaultType').each(function() {
                    var id = $(this).attr('id');
                    dynamicSelect(id);
                });
            });

            function dynamicSelect(id) {
                "use strict";
                var el;
                window.TomSelect && (new TomSelect(el = document.getElementById(id), {
                    copyClassesToDropdown: false,
                    dropdownClass: 'dropdown-menu ts-dropdown',
                    optionClass: 'dropdown-item',
                controlInput: null,
                hideSearch: true,
                    maxOptions: null,
                    render: {
                        item: function(data, escape) {
                            return '<div>' + escape(data.text) + '</div>';
                        },
                        option: function(data, escape) {
                            return '<div>' + escape(data.text) + '</div>';
                        },
                    },
                }));
            }
        </script>
@endpush
