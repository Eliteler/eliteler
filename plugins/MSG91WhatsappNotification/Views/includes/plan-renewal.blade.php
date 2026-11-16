<div class="accordion-item">
    <form action="{{ route('admin.msg91_whatsapp_template_plan_renewal.update') }}" method="post"
        enctype="multipart/form-data" id="myForm3">
        @csrf
        <h2 class="accordion-header" id="heading-3">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapse-3" aria-expanded="false">
                <h3 class="card-title">{{ __('Plan Renewal Notification') }}</h3>
            </button>
        </h2>
        <div id="collapse-3" class="accordion-collapse collapse" data-bs-parent="#accordion-example">
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
                            <select class="form-select plan_renewal_admin" name="plan_renewal_admin">
                                <option value="1" {{ $plan_renewal_admin->is_enabled == 1 ? 'selected' : '' }}>
                                    {{ __('Yes') }}</option>
                                <option value="0" {{ $plan_renewal_admin->is_enabled == 0 ? 'selected' : '' }}>
                                    {{ __('No') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Admin content sid --}}
                    <div class="mb-3 col-md-4">
                        <div class="form-label">{{ __('Admin Template Name') }}</div>
                        <input type="text" class="form-control" name="plan_renewal_admin_template_id"
                            value="{{ $plan_renewal_admin->template_id }}"
                            placeholder="{{ __('Admin Template Name') }}">
                    </div>

                    {{-- Admin template namespace --}}
                    <div class="mb-3 col-md-4">
                        <div class="form-label">{{ __('Template Namespace') }}</div>
                        <input type="text" class="form-control" name="plan_renewal_admin_template_namespace"
                            value="{{ $plan_renewal_admin->namespace }}" placeholder="{{ __('Template Namespace') }}">
                    </div>

                    {{-- Is enabled user --}}
                    <div>
                        <h3>{{ __('For User') }}</h3>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="form-label">
                                {{ __('Send Notification to User') }}</div>
                            <select class="form-select plan_renewal_user" name="plan_renewal_user">
                                <option value="1" {{ $plan_renewal_user->is_enabled == 1 ? 'selected' : '' }}>
                                    {{ __('Yes') }}</option>
                                <option value="0" {{ $plan_renewal_user->is_enabled == 0 ? 'selected' : '' }}>
                                    {{ __('No') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Admin content sid --}}
                    <div class="mb-3 col-md-4">
                        <div class="form-label">{{ __('User Template Name') }}</div>
                        <input type="text" class="form-control" name="plan_renewal_user_template_id"
                            value="{{ $plan_renewal_user->template_id }}"
                            placeholder="{{ __('User Template Name') }}">
                    </div>

                    {{-- User template namespace --}}
                    <div class="mb-3 col-md-4">
                        <div class="form-label">{{ __('Template Namespace') }}</div>
                        <input type="text" class="form-control" name="plan_renewal_user_template_namespace"
                            value="{{ $plan_renewal_user->namespace }}" placeholder="{{ __('Template Namespace') }}">
                    </div>

                    {{-- Variables --}}
                    @php
                        $decodedVariabels4 = json_decode($plan_renewal_admin->variables ?? '[]', true);
                        $decodedVariabels5 = json_decode($plan_renewal_user->variables ?? '[]', true);
                    @endphp

                    {{-- Variables --}}
                    <div class="row">
                        {{-- Variables Admin --}}
                        <div class="col-12 col-md-6">
                            <h3 class="mb-3"> {{ __('Variables Admin') }} </h3>
                            <table class="table border" id="variablesTable4">
                                <tr>
                                    <th class="p-3 border-end w-1">{{ __('#') }}</th>
                                    <th class="p-3 border-end">{{ __('Variable') }}</th>
                                    <th class="p-3 w-50">{{ __('Value') }}</th>
                                </tr>
                                @for ($i = 1; $i <= 8; $i++)
                                    <tr>
                                        <td class="p-3 border-end text-center align-middle">
                                            <input type="checkbox" class="form-check-input variable-check"
                                                {{ isset($decodedVariabels4[$i - 1]) ? 'checked' : '' }}>
                                        </td>
                                        <td class="p-3 border-end align-middle">
                                            {!! '@{{ ' . $i . ' }}' !!}
                                        </td>
                                        <td class="p-3">
                                            <select class="form-select variable-select variables">
                                                <option value="app_name"
                                                    {{ ($decodedVariabels4[$i - 1] ?? '') == 'app_name' ? 'selected' : '' }}>
                                                    {{ __('App Name') }}
                                                </option>
                                                <option value="name"
                                                    {{ ($decodedVariabels4[$i - 1] ?? '') == 'name' ? 'selected' : '' }}>
                                                    {{ __('User Name') }}
                                                </option>
                                                <option value="email"
                                                    {{ ($decodedVariabels4[$i - 1] ?? '') == 'email' ? 'selected' : '' }}>
                                                    {{ __('User Email') }}
                                                </option>
                                                <option value="plan_name"
                                                    {{ ($decodedVariabels4[$i - 1] ?? '') == 'plan_name' ? 'selected' : '' }}>
                                                    {{ __('Plan Name') }}
                                                </option>
                                                <option value="currency"
                                                    {{ ($decodedVariabels4[$i - 1] ?? '') == 'currency' ? 'selected' : '' }}>
                                                    {{ __('Currency Code') }}
                                                </option>
                                                <option value="plan_price"
                                                    {{ ($decodedVariabels4[$i - 1] ?? '') == 'plan_price' ? 'selected' : '' }}>
                                                    {{ __('Plan Price') }}
                                                </option>
                                                <option value="plan_validity"
                                                    {{ ($decodedVariabels4[$i - 1] ?? '') == 'plan_validity' ? 'selected' : '' }}>
                                                    {{ __('Plan Validity') }}
                                                </option>
                                                <option value="plan_expiry_date"
                                                    {{ ($decodedVariabels4[$i - 1] ?? '') == 'plan_expiry_date' ? 'selected' : '' }}>
                                                    {{ __('Plan Expiry Date') }}
                                                </option>
                                            </select>
                                        </td>
                                    </tr>
                                @endfor
                            </table>

                            <!-- Hidden input to hold final array -->
                            <input type="hidden" name="variablesAdmin" id="variablesInput4">
                        </div>

                        {{-- Variables User --}}
                        <div class="col-12 col-md-6">
                            <h3 class="mb-3"> {{ __('Variables User') }} </h3>
                            <table class="table border" id="variablesTable5">
                                <tr>
                                    <th class="p-3 border-end w-1">{{ __('#') }}</th>
                                    <th class="p-3 border-end">{{ __('Variable') }}</th>
                                    <th class="p-3 w-50">{{ __('Value') }}</th>
                                </tr>
                                @for ($i = 1; $i <= 8; $i++)
                                    <tr>
                                        <td class="p-3 border-end text-center align-middle">
                                            <input type="checkbox" class="form-check-input variable-check"
                                                {{ isset($decodedVariabels5[$i - 1]) ? 'checked' : '' }}>
                                        </td>
                                        <td class="p-3 border-end align-middle">
                                            {!! '@{{ ' . $i . ' }}' !!}
                                        </td>
                                        <td class="p-3">
                                            <select class="form-select variable-select variables">
                                                <option value="app_name"
                                                    {{ ($decodedVariabels5[$i - 1] ?? '') == 'app_name' ? 'selected' : '' }}>
                                                    {{ __('App Name') }}
                                                </option>
                                                <option value="name"
                                                    {{ ($decodedVariabels5[$i - 1] ?? '') == 'name' ? 'selected' : '' }}>
                                                    {{ __('User Name') }}
                                                </option>
                                                <option value="email"
                                                    {{ ($decodedVariabels5[$i - 1] ?? '') == 'email' ? 'selected' : '' }}>
                                                    {{ __('User Email') }}
                                                </option>
                                                <option value="plan_name"
                                                    {{ ($decodedVariabels5[$i - 1] ?? '') == 'plan_name' ? 'selected' : '' }}>
                                                    {{ __('Plan Name') }}
                                                </option>
                                                <option value="currency"
                                                    {{ ($decodedVariabels5[$i - 1] ?? '') == 'currency' ? 'selected' : '' }}>
                                                    {{ __('Currency Code') }}
                                                </option>
                                                <option value="plan_price"
                                                    {{ ($decodedVariabels5[$i - 1] ?? '') == 'plan_price' ? 'selected' : '' }}>
                                                    {{ __('Plan Price') }}
                                                </option>
                                                <option value="plan_validity"
                                                    {{ ($decodedVariabels5[$i - 1] ?? '') == 'plan_validity' ? 'selected' : '' }}>
                                                    {{ __('Plan Validity') }}
                                                </option>
                                                <option value="plan_expiry_date"
                                                    {{ ($decodedVariabels5[$i - 1] ?? '') == 'plan_expiry_date' ? 'selected' : '' }}>
                                                    {{ __('Plan Expiry Date') }}
                                                </option>
                                            </select>
                                        </td>
                                    </tr>
                                @endfor
                            </table>

                            <!-- Hidden input to hold final array -->
                            <input type="hidden" name="variablesUser" id="variablesInput5">
                        </div>
                    </div>

                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
