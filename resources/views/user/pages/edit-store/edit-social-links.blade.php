@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@section('css')
    <link rel="stylesheet" href="{{ asset('css/all.css') }}" />
    <style>
        .ts-control>input {
            display: contents !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        {{-- Page title --}}
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">{{ __('Overview') }}</div>
                        <h2 class="page-title">{{ __('Store Social Links') }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed --}}
                @if(Session::has("failed"))
                <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                    <div class="d-flex"><div>{{Session::get('failed')}}</div></div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
                @endif

                {{-- Success --}}
                @if(Session::has("success"))
                <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                    <div class="d-flex"><div>{{Session::get('success')}}</div></div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
                @endif

                <div class="card">
                    <div class="row g-0">
                        {{-- Sidebar nav --}}
                        <div class="col-12 col-md-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader">{{ __('Update Store') }}</h4>
                                <div class="list-group list-group-transparent">
                                    @include('user.pages.edit-store.include.nav-link', ['link' => 'social-links'])
                                </div>
                            </div>
                        </div>

                        {{-- Main content --}}
                        <div class="col-12 col-md-10 d-flex flex-column">
                            <form action="{{ route('user.update.store.social.links', Request::segment(4)) }}" method="post" id="myForm">
                                @csrf
                                @method('POST')
                                <div class="card-body">
                                    <h3 class="card-title mb-1">{{ __('Social Links') }}</h3>
                                    <p class="text-muted mb-4">{{ __('Add your store\'s social media links. They will appear in the store footer.') }}</p>

                                    <div>
                                        {{-- Existing links --}}
                                        @for ($i = 0; $i < count($features); $i++)
                                            <div class="row" id="{{ $features[$i]->id }}">
                                                <div class='col-lg-2 col-md-2'>
                                                    <div class='mb-3 mt-2'>
                                                        <label class='form-label required' for='type'>{{ __('Platform') }}</label>
                                                        <select class="type{{ $features[$i]->id }} defaultType form-select"
                                                            id="type{{ $features[$i]->id }}" name="type[]"
                                                            onchange='changeLabel({{ $features[$i]->id }})' required>
                                                            @include('user.pages.edit-store.include.social-options', ['selected' => $features[$i]->type])
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class='col-lg-1 col-md-1'>
                                                    <div class='mb-3 mt-2'>
                                                        <label class='form-label required'>{{ __('Icon') }}</label>
                                                        <button type="button" id='iconpick{{ $features[$i]->id }}' class="btn btn-primary btn-icon text-white btn-md icon-picker-trigger" data-bs-toggle="modal" data-bs-target="#iconPickerModal" data-id="{{ $features[$i]->id }}">
                                                            <i id="displayIcon{{ $features[$i]->id }}" class="{{ $features[$i]->icon }}"></i>
                                                        </button>
                                                        <input type='hidden' class='icon{{ $features[$i]->id }} form-control' value="{{ $features[$i]->icon }}"
                                                            placeholder='{{ __('Choose Icon') }}' name='icon[]' required readonly>
                                                    </div>
                                                </div>
                                                <div class='col-lg-3 col-md-3'>
                                                    <div class='mb-3 mt-2'>
                                                        <label class='form-label'>{{ __('Label') }}</label>
                                                        <input type='text'
                                                            class='lbl{{ $features[$i]->id }} form-control' name='label[]'
                                                            placeholder='{{ __('Label') }}'
                                                            value="{{ $features[$i]->label }}">
                                                    </div>
                                                </div>
                                                <div class='col-lg-4 col-md-4'>
                                                    <div class='mb-3 mt-2'>
                                                        <label class='form-label required'>{{ __('URL / Username') }}</label>
                                                        <input type="text"
                                                            class='textlbl{{ $features[$i]->id }} form-control'
                                                            name='value[]' placeholder='{{ __('URL or username') }}'
                                                            value="{{ $features[$i]->content }}" required>
                                                    </div>
                                                </div>
                                                <div class='col-lg-2 col-md-2'>
                                                    <div class='mb-3 pt-1 mt-5'>
                                                        <button type="button" class='btn btn-danger btn-sm'
                                                            onclick='removeFeature({{ $features[$i]->id }})'>
                                                            <i class='fa fa-times text-white'></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor

                                        {{-- New links container --}}
                                        <div id="more-features"></div>

                                        {{-- Add button --}}
                                        <div class="col-lg-12 mb-5">
                                            <button type="button" onclick="addFeature()" class="btn btn-primary">
                                                <i class="ti ti-plus me-1"></i>{{ __('Add Social Link') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-end">
                                    <div class="d-flex">
                                        <a href="{{ route('user.stores') }}" class="btn btn-outline-primary ms-2">{{ __('Cancel') }}</a>
                                        <button type="submit" class="btn btn-primary ms-auto">{{ __('Save') }}</button>
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

    <!-- Icon Picker Modal -->
    <div class="modal modal-blur fade" id="iconPickerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable py-7" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Choose an Icon') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" id="iconSearchInput" class="form-control" placeholder="Search icons..." />
                    </div>
                    @php
                        $icons = include resource_path('views/user/includes/icons.php');
                    @endphp
                    <div class="row gap-2" id="iconList">
                        @foreach ($icons as $icon)
                            <div class="col-2 col-md-1 p-3 icon-padding" data-icon-name="{{ $icon }}">
                                <div role="button" class="icon-option" data-icon="{{ $icon }}">
                                    <i class="{{ $icon }} fa-xl"></i>
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
                    <div class="text-muted mt-1">{{ __('Do you want to delete this social link?') }}</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary me-auto" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">{{ __('Yes, Delete') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Custom JS --}}
    @push('custom-js')
        <link rel="stylesheet" href="{{ asset('css/all.css') }}" />
        <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
        <script>
            var count = {{ count($features) }};

            function addFeature() {
                "use strict";
                if (count >= {{ $plan_details->no_of_links ?? 10 }}) {
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

                    var features = `<div class='row' id=` + id + `>
                        <div class='col-lg-2 col-md-2'>
                            <div class='mb-3 mt-2'>
                                <label class='form-label required' for='type'>{{ __('Platform') }}</label>
                                <select class="type` + id + ` form-select" id="type` + id + `" name="type[]" onchange='changeLabel(` + id + `)' required>
                                    <option value='facebook'>{{ __('Facebook') }}</option>
                                    <option value='instagram'>{{ __('Instagram') }}</option>
                                    <option value='x-twitter'>{{ __('X (Twitter)') }}</option>
                                    <option value='linkedin'>{{ __('LinkedIn') }}</option>
                                    <option value='tiktok'>{{ __('TikTok') }}</option>
                                    <option value='youtube'>{{ __('YouTube') }}</option>
                                    <option value='snapchat'>{{ __('Snapchat') }}</option>
                                    <option value='pinterest'>{{ __('Pinterest') }}</option>
                                    <option value='telegram'>{{ __('Telegram') }}</option>
                                    <option value='threads'>{{ __('Threads') }}</option>
                                    <option value='reddit'>{{ __('Reddit') }}</option>
                                    <option value='wa'>{{ __('WhatsApp') }}</option>
                                    <option value='discord'>{{ __('Discord') }}</option>
                                    <option value='url'>{{ __('Website Link') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class='col-lg-1 col-md-1'>
                            <div class='mb-3 mt-2'>
                                <label class='form-label required'>{{ __('Icon') }}</label>
                                <button type="button" id='iconpick` + id + `' class="btn btn-primary btn-icon text-white btn-md icon-picker-trigger" data-bs-toggle="modal" data-bs-target="#iconPickerModal" data-id="` + id + `">
                                    <i id="displayIcon` + id + `" class="fab fa-facebook-f fa-lg text-white"></i>
                                </button>
                                <input type='hidden' class='icon` + id + ` form-control' value="fab fa-facebook-f fa-md" placeholder='{{ __('Choose Icon') }}' name='icon[]' required readonly>
                            </div>
                        </div>
                        <div class='col-lg-3 col-md-3'>
                            <div class='mb-3 mt-2'>
                                <label class='form-label'>{{ __('Label') }}</label>
                                <input type='text' class='lbl` + id + ` form-control' name='label[]' placeholder='{{ __('Facebook') }}' value="Facebook">
                            </div>
                        </div>
                        <div class='col-lg-4 col-md-4'>
                            <div class='mb-3 mt-2'>
                                <label class='form-label required'>{{ __('URL / Username') }}</label>
                                <input type='text' class='textlbl` + id + ` form-control' name='value[]' placeholder='{{ __('https://facebook.com/yourpage') }}' required>
                            </div>
                        </div>
                        <div class='col-lg-2 col-md-2'>
                            <div class='my-3 py-4'>
                                <button type="button" class='btn btn-danger btn-icon' onclick='removeFeature(` + id + `)'>
                                    <i class='fa fa-times text-white'></i>
                                </button>
                            </div>
                        </div>
                    </div>`;
                    $("#more-features").append(features).html();
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
                    pendingDeleteId = null;
                }
                $('#confirmDeleteModal').modal('hide');
            });

            function getRandomInt() {
                return Math.floor(Math.random() * (Math.floor(9999999999) - Math.ceil(0)) + Math.ceil(0));
            }

            // Icon search
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
                    if (fullIconName.toLowerCase().includes(search) || iconNameOnly.toLowerCase().includes(search)) {
                        $(this).css('display', 'flex');
                    }
                });
            });

            let selectedIconId = null;

            $(document).on('click', '.icon-picker-trigger', function () {
                selectedIconId = $(this).data('id');
            });

            $(document).on('click', '.icon-option', function () {
                "use strict";
                const selectedIcon = $(this).data('icon');
                $('.icon' + selectedIconId).val(selectedIcon);
                $('#displayIcon' + selectedIconId).attr('class', `${selectedIcon} fa-lg`);
                $('#iconPickerModal').modal('hide');
            });

            function changeLabel(id) {
                "use strict";
                let icon = document.querySelector('.icon' + id);
                let lbl = document.querySelector('.lbl' + id);
                let textlbl = document.querySelector('.textlbl' + id);
                let type = document.querySelector('.type' + id).value;

                const iconMap = {
                    'facebook':  { icon: 'fab fa-facebook-f',  label: 'Facebook',    placeholder: 'https://facebook.com/yourpage' },
                    'instagram': { icon: 'fab fa-instagram',    label: 'Instagram',   placeholder: 'https://instagram.com/username' },
                    'x-twitter': { icon: 'fab fa-x-twitter',   label: 'X (Twitter)', placeholder: 'https://x.com/username' },
                    'linkedin':  { icon: 'fab fa-linkedin-in',  label: 'LinkedIn',    placeholder: 'https://linkedin.com/in/username' },
                    'tiktok':    { icon: 'fab fa-tiktok',       label: 'TikTok',      placeholder: 'https://tiktok.com/@username' },
                    'youtube':   { icon: 'fab fa-youtube',      label: 'YouTube',     placeholder: 'https://youtube.com/channel' },
                    'snapchat':  { icon: 'fab fa-snapchat',     label: 'Snapchat',    placeholder: 'https://snapchat.com/add/username' },
                    'pinterest': { icon: 'fab fa-pinterest',    label: 'Pinterest',   placeholder: 'https://pinterest.com/username' },
                    'telegram':  { icon: 'fab fa-telegram',     label: 'Telegram',    placeholder: 'https://t.me/username' },
                    'threads':   { icon: 'fab fa-threads',      label: 'Threads',     placeholder: 'https://threads.net/@username' },
                    'reddit':    { icon: 'fab fa-reddit',       label: 'Reddit',      placeholder: 'https://reddit.com/user/username' },
                    'wa':        { icon: 'fab fa-whatsapp',     label: 'WhatsApp',    placeholder: '+971500000000' },
                    'discord':   { icon: 'fab fa-discord',      label: 'Discord',     placeholder: 'https://discord.gg/invite' },
                    'url':       { icon: 'fas fa-link',         label: 'Website',     placeholder: 'https://yourwebsite.com' },
                };

                if (iconMap[type]) {
                    icon.value = iconMap[type].icon;
                    lbl.value = iconMap[type].label;
                    lbl.placeholder = iconMap[type].label;
                    textlbl.placeholder = iconMap[type].placeholder;
                    $('#displayIcon' + id).attr('class', iconMap[type].icon);
                }
            }

            document.getElementById("myForm").onkeypress = function(e) {
                var key = e.charCode || e.keyCode || 0;
                if (key == 13) { e.preventDefault(); }
            }

            // Initialize TomSelect on existing selects
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
                    controlInput: '<input>',
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
@endsection
