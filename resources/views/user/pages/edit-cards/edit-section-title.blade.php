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
                            <form action="{{ route('user.update.section.title', ['id' => $business_card->card_id]) }}"
                                method="post" id="myForm" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <h3 class="card-title mb-4">{{ __('Section Titles') }}</h3>
                                    <div class="row">
                                        <table id="sectionTable" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    {{-- <th style="width:50px;">{{ __('Drag') }}</th> --}}
                                                    {{-- <th>{{ __('Title') }}</th> --}}
                                                </tr>
                                            </thead>
                                            <tbody id="sortable">
                                                @foreach ($sectionTitles as $item)
                                                    <tr></tr>
                                                    <tr data-id="{{ $item->id }}">
                                                        {{-- <td class="handle text-center" style="cursor:grab;">☰</td> --}}
                                                        <td class="border-0">
                                                            <label
                                                                class='form-label required'>{{ __($item->label) }}</label>
                                                            <input type="text" name="titles[{{ $item->id }}]"
                                                                value="{{ __($item->title) }}"
                                                                class="form-control editable-input"
                                                                placeholder="{{ __('Enter title') }}" minlength="1"
                                                                maxlength="20" required>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <div class="d-flex">
                                        <a href="{{ route('user.cards') }}"
                                            class="btn btn-outline-primary ms-2">{{ __('Cancel') }}</a>

                                        {{-- Next link --}}
                                        @php
                                            $route = route('user.edit.intro-screen', Request::segment(3));

                                            if (
                                                $plan_details->google_wallet == 1 &&
                                                is_dir(base_path('plugins/GoogleWallet')) &&
                                                $business_card->type != 'personal'
                                            ) {
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
            // Init DataTable
            let table = new DataTable('#sectionTable', {
                paging: false,
                searching: false,
                info: false,
                order: [
                    [0, 'asc']
                ]
            });

            // Enable drag/drop on tbody
            let el = document.getElementById('sortable');
            Sortable.create(el, {
                animation: 150,
                handle: '.handle'
            });

            // // Save order
            // document.getElementById('saveOrder').addEventListener('click', () => {
            //     let order = Array.from(document.querySelectorAll('#sortable tr'))
            //         .map(row => row.getAttribute('data-id'));

            //     fetch("{{ route('user.save.section.title', ['id' => $business_card->card_id]) }}", {
            //         method: 'POST',
            //         headers: {
            //             'Content-Type': 'application/json',
            //             'X-CSRF-TOKEN': "{{ csrf_token() }}"
            //         },
            //         body: JSON.stringify({
            //             order
            //         })
            //     }).then(r => r.json()).then(() => {
            //         alert('Order saved');
            //     }).catch(() => alert('Save failed'));
            // });

            // // Update title on blur
            // document.querySelectorAll('.editable').forEach(span => {
            //     span.addEventListener('blur', function() {
            //         let row = this.closest('tr');
            //         let id = row.getAttribute('data-id');
            //         let newTitle = this.textContent.trim();

            //         fetch("", {
            //             method: 'POST',
            //             headers: {
            //                 'Content-Type': 'application/json',
            //                 'X-CSRF-TOKEN': "{{ csrf_token() }}"
            //             },
            //             body: JSON.stringify({
            //                 id,
            //                 title: newTitle
            //             })
            //         }).then(r => r.json()).then(data => {
            //             if (data.status !== 'ok') {
            //                 alert('Failed to update title');
            //             }
            //         }).catch(() => alert('Error updating title'));
            //     });
            // });
        </script>
    @endpush
@endsection
