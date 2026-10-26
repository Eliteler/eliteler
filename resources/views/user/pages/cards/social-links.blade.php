@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS (Kept dry) --}}
@section('css')
    <link rel="stylesheet" href="{{ asset('css/all.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/bootstrap-iconpicker.min.css') }}" />
    <style>
    .ts-control>input {
        display: contents !important;
    }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header (Wraps the inner container-fluid) -->
    <div class="page-header d-print-none">
        <div class="container-fluid">
            <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Trail -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
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
                    <h2 class="page-title">
                            {{ __('Social Links') }}
                    </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Add interactive elements, attach social media channels, embed video sections, and assign location coordinates.') }}
                </div>
            </div>
        </div>
    </div>
        </div>

    <div class="page-body">
        <div class="container-fluid">
                {{-- Failed Alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
            @endif

                {{-- Success Alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
            @endif
            
            <div class="row row-deck row-cards">
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper (Decoupled form structures) -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('user.save.social.links', Request::segment(3)) }}" id="myForm"
                                method="post">
                        @csrf
                        <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Aligned dynamically added links --}}
                                        <div id="more-features" class="row g-3 m-0"></div>
                                
                                        <div class="col-12 mt-4">
                                            <button type="button" onclick="addFeature()"
                                                class="btn btn-primary d-inline-flex align-items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-plus me-2" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <line x1="12" y1="5" x2="12" y2="19" />
                                                    <line x1="5" y1="12" x2="19" y2="12" />
                                                </svg>
                                                {{ __('Add New') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                                <!-- Form Footer containing save trigger styled alone -->
                        <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
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

    <!-- Icon Picker Modal upgraded to Borderless Shadowed Pattern -->
    <div class="modal modal-bg fade" id="iconPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable py-7" role="document">
            <div class="modal-content border-0 shadow-sm">
            <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark">{{ __('Choose an Icon') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                    <div class="mb-4">
                    <input type="text" id="iconSearchInput" class="form-control" placeholder="Search icons..." />
                </div>

                @php
                    $icons = include resource_path('views/user/includes/icons.php');
                @endphp

                    <div class="row g-2 justify-content-center" id="iconList">
                    @foreach ($icons as $icon)
                            <div class="col-3 col-sm-2 col-md-1 p-2 text-center" data-icon-name="{{ $icon }}">
                                <div role="button" class="icon-option p-2 border rounded shadow-none hover-primary"
                                    data-icon="{{ $icon }}">
                                    <i class="{{ $icon }} fa-lg"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('custom-js')
    <script type="text/javascript" src="{{ asset('js/fontawesome-iconpicker.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
    var count = 0; 

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
            var id = getRandomInt();
            let card_details = "{{ $card_details->theme_id }}";

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
                sectionOptions += "<option value='iframe'>{{ __('iframe') }}</option>";
                sectionOptions += "</optgroup>";
            }

                var features = `<div class='row g-3' id=` + id + `>
                            <div class='col-lg-3 col-md-3'>
                                    <div class='mb-3 mt-2'>
                                        <label class='form-label required' for='type'>{{ __('Display type') }}</label>
                                    <select class="type` + id + ` form-select" id="type` + id +
                    `" name="type[]" onchange='changeLabel(` + id + `)' required>
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
                                </div>

                                <div class='col-auto'>
                                    <div class='mb-3 mt-2'>
                                        <label class='form-label required'>{{ __('Icon') }}</label>
                                    <button type="button" id='iconpick` + id +
                    `' class="btn btn-primary btn-icon text-white btn-md icon-picker-trigger" data-bs-toggle="modal" data-bs-target="#iconPickerModal" data-id="` +
                    id + `">
                                            <i id="displayIcon` + id + `" class="fab fa-facebook-f fa-lg text-white"></i>
                                        </button>
                                        <input type='hidden' class='icon` + id + ` form-control' value="fab fa-facebook-f fa-md" placeholder='{{ __('Choose Icon') }}' name='icon[]' required readonly>
                                    </div>
                                </div>

                                <div class='col-lg-4 col-md-4'>
                                    <div class='mb-3 mt-2'>
                                    <label class='form-label'>{{ __('Label / Title') }}</label>
                                    <input type='text' class='lbl` + id + ` form-control' name='label[]' placeholder='{{ __('Enter label') }}' value='Facebook'>
                                    </div>
                                </div>

                                <div class='col-lg-4 col-md-4'>
                                    <div class='mb-3 mt-2'>
                                        <label class='form-label required'>{{ __('Content') }}</label>
                                        <input type='text' class='textlbl`+ id +` form-control' name='value[]' placeholder='{{ __('For ex: https://facebook.com') }}' autofocus required>
                                        <small class='notelbl`+ id +` text-danger' style='display:none;'></small>
                                    </div>
                                </div>

                            <div class='col-auto'>
                                <div class='mb-2 pt-1 mt-5'>
                                    <button type="button" class='btn btn-danger btn-icon btn-md' onclick='removeFeature(` +
                    id + `)'>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-0" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>`;
                $("#more-features").append(features);
                dynamicSelect(id);
        }
    }

    addFeature();

    function removeFeature(id) {
	"use strict";
            if (count == 1) {
        new swal({
        title: `{{ __('Oops!') }}`,
        icon: 'warning',
        text: `{{ __('Please add at least one bio link.') }}`,
        timer: 2000,
        buttons: false,
        showConfirmButton: false,
    });
            } else {
                $("#" + id).remove();
        count--;
    }
    }

    function getRandomInt() {
            var min = Math.ceil(0);
            var max = Math.floor(9999999999);
            return Math.floor(Math.random() * (max - min) + min);
    }

    $('#iconSearchInput').on('keyup', function() {
        "use strict";
        const search = $(this).val().toLowerCase().trim();

        $('#iconList > div').css('display', 'none');

        if (search === '') {
            $('#iconList > div').css('display', 'flex');
            return;
        }

        $('#iconList > div').each(function() {
            const fullIconName = $(this).attr('data-icon-name') || "";
            const nameParts = fullIconName.split(' ');
            const iconPart = nameParts.length > 1 ? nameParts[1] : fullIconName;
            const iconNameOnly = iconPart.replace('fa-', '');

            if (fullIconName.toLowerCase().includes(search) ||
                iconNameOnly.toLowerCase().includes(search)) {
                $(this).css('display', 'flex');
            }
        });
    });

    let selectedIconId = null;

        $(document).on('click', '.icon-picker-trigger', function() {
        selectedIconId = $(this).data('id');
    });

        $(document).on('click', '.icon-option', function() {
        "use strict";
        const selectedIcon = $(this).data('icon');
        $('.icon' + selectedIconId).val(selectedIcon);
            $('#displayIcon' + selectedIconId).attr('class', `${selectedIcon} fa-lg text-white`);
        $('#iconPickerModal').modal('hide');
    });

    function changeLabel(id) {
        "use strict";
        var label = `{{ __('Label') }}`;
        var textlabel = `{{ __('Type something') }}`;
        let icon = document.querySelector('.icon'+id);
        let lbl = document.querySelector('.lbl'+id);
        let textlbl = document.querySelector('.textlbl'+id);
        let type = document.querySelector('.type'+id).value;
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
                textlabel =
                    `{{ __('For ex: https://www.google.com/maps/place/data=!4m8!3m7!1s0x3b00ab9baf4b4101:0x9d6d57a812be5cc6!8m2!3d10.3538708!4d77.979598!9m1!1b1!16s%2Fg%2F11k_jzyswz?entry=ttu&g_ep=EgoyMDI1MDYxNS4wIKXMDSoASAFQAw%3D%3D') }}`;
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

        lbl.placeholder = label;
        textlbl.placeholder = textlabel;
    }

    document.getElementById("myForm").onkeypress = function(e) {
        var key = e.charCode || e.keyCode || 0;
        if (key == 13) {
        e.preventDefault();
        }
    }

    function dynamicSelect(id) {
        "use strict";
        var el;
            window.TomSelect && (new TomSelect(el = document.getElementById('type' + id), {
            copyClassesToDropdown: false,
            dropdownClass: 'dropdown-menu ts-dropdown',
                optionClass: 'dropdown-item',
            controlInput: '<input>',
            maxOptions: null,
                render: {
                    item: function(data, escape) {
                        if (data.customProperties) {
                            return '<div><span class="dropdown-item-indicator">' + data.customProperties +
                                '</span>' + escape(data.text) + '</div>';
                    }
                    return '<div>' + escape(data.text) + '</div>';
                },
                    option: function(data, escape) {
                        if (data.customProperties) {
                            return '<div><span class="dropdown-item-indicator">' + data.customProperties +
                                '</span>' + escape(data.text) + '</div>';
                    }
                    return '<div>' + escape(data.text) + '</div>';
                },
            },
        }));
    }
    </script>
@endpush
