@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"
        integrity="sha512-KGtsnWohFUg0oksKq7p7eDgA1Rw2nBfqhGJn463/rGhtUY825dBqGexj8eP04LwfnsSW6dNAHAlOqKJKquHsnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            {{ __('Overview') }}
                        </div>
                        <h2 class="page-title">
                            {{ __('Policies') }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>

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
                                <h4 class="subheader">{{ __('Update Store Category') }}</h4>
                                <div class="list-group list-group-transparent">
                                    {{-- Nav links --}}
                                    @include('user.pages.edit-store.include.nav-link', [
                                        'link' => 'policies',
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-10 d-flex flex-column">
                            <div class="div">
                                {{-- Update --}}
                                <form action="{{ route('user.update.store.policies') }}" method="POST">
                                    @csrf
                                    <div class="card-header">
                                        <h3 class="card-title">{{ __('Policies') }}</h3>
                                    </div>
                                    <div class="card-body m-0 py-4 px-2">
                                        {{-- Store ID --}}
                                        <input type="hidden" class="form-control" name="store_id" value="{{ $business_card->card_id }}">

                                        {{-- Privacy Policy --}}
                                        <div class="accordion" id="privacy-1-policy">
                                            <div class="accordion-item">
                                                <div class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#privacy-policy" aria-expanded="false">
                                                        <strong>{{ __('Privacy Policy') }}</strong>
                                                    </button>
                                                </div>
                                                <div id="privacy-policy" class="accordion-collapse collapse"
                                                    data-bs-parent="#privacy-1-policy">
                                                    <div class="accordion-body">
                                                        {{-- Textarea --}}
                                                        <textarea class="form-control" id="privacy-policy-textarea"
                                                            name="privacy_policy_textarea" rows="10">{{ $business_card->privacy_policy }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Terms and Conditions --}}
                                        <div class="accordion mt-3" id="terms-and-conditions-1-policy">
                                            <div class="accordion-item">
                                                <div class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#terms-and-conditions" aria-expanded="false">
                                                        <strong>{{ __('Terms and Conditions') }}</strong>
                                                    </button>
                                                </div>
                                                <div id="terms-and-conditions" class="accordion-collapse collapse"
                                                    data-bs-parent="#terms-and-conditions-1-policy">
                                                    <div class="accordion-body">
                                                        {{-- Textarea --}}
                                                        <textarea class="form-control" id="terms-and-conditions-textarea"
                                                            name="terms_and_conditions_textarea" rows="10">{{ $business_card->terms_and_conditions }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Return/Refund Policy --}}
                                        <div class="accordion mt-3" id="refund-policy-1-policy">
                                            <div class="accordion-item">
                                                <div class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#refund-policy" aria-expanded="false">
                                                        <strong>{{ __('Return/Refund Policy') }}</strong>
                                                    </button>
                                                </div>
                                                <div id="refund-policy" class="accordion-collapse collapse"
                                                    data-bs-parent="#refund-policy-1-policy">
                                                    <div class="accordion-body">
                                                        {{-- Textarea --}}
                                                        <textarea class="form-control" id="refund-policy-textarea"
                                                            name="refund_policy_textarea" rows="10">{{ $business_card->refund_policy }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Shipping Policy --}}
                                        <div class="accordion mt-3" id="shipping-policy-1-policy">
                                            <div class="accordion-item">
                                                <div class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#shipping-policy" aria-expanded="false">
                                                        <strong>{{ __('Shipping Policy') }}</strong>
                                                    </button>
                                                </div>
                                                <div id="shipping-policy" class="accordion-collapse collapse"
                                                    data-bs-parent="#shipping-policy-1-policy">
                                                    <div class="accordion-body">
                                                        {{-- Textarea --}}
                                                        <textarea class="form-control" id="shipping-policy-textarea"
                                                            name="shipping_policy_textarea" rows="10">{{ $business_card->shipping_policy }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Cookie Policy --}}
                                        <div class="accordion mt-3" id="cookie-policy-1-policy">
                                            <div class="accordion-item">
                                                <div class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#cookie-policy" aria-expanded="false">
                                                        <strong>{{ __('Cookie Policy') }}</strong>
                                                    </button>
                                                </div>
                                                <div id="cookie-policy" class="accordion-collapse collapse"
                                                    data-bs-parent="#cookie-policy-1-policy">
                                                    <div class="accordion-body">
                                                        {{-- Textarea --}}
                                                        <textarea class="form-control" id="cookie-policy-textarea"
                                                            name="cookie_policy_textarea" rows="10">{{ $business_card->cookie_policy }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Contact Information / Customer Support Policy --}}
                                        <div class="accordion mt-3" id="customer-support-policy-1-policy">
                                            <div class="accordion-item">
                                                <div class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#customer-support-policy" aria-expanded="false">
                                                        <strong>{{ __('Contact Information / Customer Support Policy') }}</strong>
                                                    </button>
                                                </div>
                                                <div id="customer-support-policy" class="accordion-collapse collapse"
                                                    data-bs-parent="#customer-support-policy-1-policy">
                                                    <div class="accordion-body">
                                                        {{-- Textarea --}}
                                                        <textarea class="form-control" id="customer-support-policy-textarea"
                                                            name="customer_support_policy_textarea" rows="10">{{ $business_card->customer_support_policy }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
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
    </div>

    {{-- Custom JS --}}
    @push('custom-js')
        <script>
            // TinyMCE Init
            tinymce.init({
                selector: 'textarea#privacy-policy-textarea, textarea#terms-and-conditions-textarea, textarea#refund-policy-textarea, textarea#shipping-policy-textarea, textarea#cookie-policy-textarea, textarea#customer-support-policy-textarea',
                plugins: 'code paste preview importcss searchreplace autolink autosave save directionality visualblocks visualchars link charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount charmap quickbars emoticons',
                menubar: 'file edit view insert format tools',
                toolbar: 'code | undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | preview save print | insertfile link anchor | ltr rtl',
                content_style: 'body { font-family:Times New Roman,Arial,sans-serif; font-size:16px }',
                height: 226,
                menubar: false,
                statusbar: false,

                // Correct mobile settings (limited fields allowed)
                mobile: {
                    plugins: 'paste',
                    toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | preview save print | insertfile link anchor | ltr rtl'
                }
            });
        </script>
    @endpush
@endsection
