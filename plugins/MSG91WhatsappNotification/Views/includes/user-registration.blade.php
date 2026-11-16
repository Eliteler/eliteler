<div class="accordion-item">
    <form action="{{ route('admin.msg91_whatsapp_template_user_register.update') }}" method="post"
        enctype="multipart/form-data" id="myForm1">
        @csrf
        <h2 class="accordion-header" id="heading-1">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapse-1" aria-expanded="false">
                <h3 class="card-title">{{ __('New User Registration Notification') }}</h3>
            </button>
        </h2>
        <div id="collapse-1" class="accordion-collapse collapse" data-bs-parent="#accordion-example">
            <div class="accordion-body pt-0">
                <div class="row">
                    {{-- Is enabled admin --}}
                    <div>
                        <h3>{{ __('For Admin') }}</h3>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="form-label">
                                {{ __('Send Notification to Admin') }}</div>
                            <select class="form-select new_user_registration_admin" name="new_user_registration_admin">
                                <option value="1"
                                    {{ $new_user_registration_admin->is_enabled == 1 ? 'selected' : '' }}>
                                    {{ __('Yes') }}</option>
                                <option value="0"
                                    {{ $new_user_registration_admin->is_enabled == 0 ? 'selected' : '' }}>
                                    {{ __('No') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Admin Template Name --}}
                    <div class="mb-3 col-md-4">
                        <div class="form-label">{{ __('Template Name') }}</div>
                        <input type="text" class="form-control" name="new_user_registration_admin_template_id"
                            value="{{ $new_user_registration_admin->template_id }}"
                            placeholder="{{ __('Template Name') }}">
                    </div>

                    {{-- Admin template namespace --}}
                    <div class="mb-3 col-md-4">
                        <div class="form-label">{{ __('Template Namespace') }}</div>
                        <input type="text" class="form-control" name="new_user_registration_admin_template_namespace"
                            value="{{ $new_user_registration_admin->namespace }}"
                            placeholder="{{ __('Template Namespace') }}">
                    </div>

                    {{-- Variables --}}
                    @php
                        $decodedVariabels1 = json_decode($new_user_registration_admin->variables ?? '[]', true);
                    @endphp
                    <div class="col-12 col-md-6 col-xl-6">
                        <h3 class="mb-3"> {{ __('Variables Admin') }} </h3>
                        <table class="table border" id="variablesTable1">
                            <tr>
                                <th class="p-3 border-end w-1">{{ __('#') }}</th>
                                <th class="p-3 border-end">{{ __('Variable') }}</th>
                                <th class="p-3 w-50">{{ __('Value') }}</th>
                            </tr>
                            @for ($i = 1; $i <= 3; $i++)
                                <tr>
                                    <td class="p-3 border-end text-center align-middle">
                                        <input type="checkbox" class="form-check-input variable-check"
                                            {{ isset($decodedVariabels1[$i - 1]) ? 'checked' : '' }}>
                                    </td>
                                    <td class="p-3 border-end align-middle">
                                        {!! '@{{ ' . $i . ' }}' !!}
                                    </td>
                                    <td class="p-3">
                                        <select class="form-select variable-select variables">
                                            <option value="app_name"
                                                {{ ($decodedVariabels1[$i - 1] ?? '') == 'app_name' ? 'selected' : '' }}>
                                                {{ __('App Name') }}
                                            </option>
                                            <option value="name"
                                                {{ ($decodedVariabels1[$i - 1] ?? '') == 'name' ? 'selected' : '' }}>
                                                {{ __('User Name') }}
                                            </option>
                                            <option value="email"
                                                {{ ($decodedVariabels1[$i - 1] ?? '') == 'email' ? 'selected' : '' }}>
                                                {{ __('User Email') }}
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            @endfor
                        </table>

                        <!-- Hidden input to hold final array -->
                        <input type="hidden" name="variables" id="variablesInput1">
                    </div>

                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
