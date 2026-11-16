<div class="accordion-item">
    <form action="{{ route('admin.msg91_whatsapp_template_plan_purchase.update') }}" method="post"
        enctype="multipart/form-data" id="myForm2">
        @csrf
        <h2 class="accordion-header" id="heading-2">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapse-2" aria-expanded="false">
                <h3 class="card-title">{{ __('Plan Purchase Notification') }}</h3>
            </button>
        </h2>
        <div id="collapse-2" class="accordion-collapse collapse" data-bs-parent="#accordion-example">
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
                            <select class="form-select plan_purchase_admin" name="plan_purchase_admin">
                                <option value="1" {{ $plan_purchase_admin->is_enabled == 1 ? 'selected' : '' }}>
                                    {{ __('Yes') }}</option>
                                <option value="0" {{ $plan_purchase_admin->is_enabled == 0 ? 'selected' : '' }}>
                                    {{ __('No') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Admin content sid --}}
                    <div class="mb-3 col-md-4">
                        <div class="form-label">{{ __('Admin Template Name') }}</div>
                        <input type="text" class="form-control" name="plan_purchase_admin_template_id"
                            value="{{ $plan_purchase_admin->template_id }}"
                            placeholder="{{ __('Admin Template Name') }}">
                    </div>

                    {{-- Admin template namespace --}}
                    <div class="mb-3 col-md-4">
                        <div class="form-label">{{ __('Template Namespace') }}</div>
                        <input type="text" class="form-control" name="plan_purchase_admin_template_namespace"
                            value="{{ $plan_purchase_admin->namespace }}"
                            placeholder="{{ __('Template Namespace') }}">
                    </div>

                    {{-- Is enabled user --}}
                    <div>
                        <h3>{{ __('For User') }}</h3>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="form-label">
                                {{ __('Send Notification to User') }}</div>
                            <select class="form-select plan_purchase_user" name="plan_purchase_user">
                                <option value="1" {{ $plan_purchase_user->is_enabled == 1 ? 'selected' : '' }}>
                                    {{ __('Yes') }}</option>
                                <option value="0" {{ $plan_purchase_user->is_enabled == 0 ? 'selected' : '' }}>
                                    {{ __('No') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Admin content sid --}}
                    <div class="mb-3 col-md-4">
                        <div class="form-label">{{ __('User Template Name') }}</div>
                        <input type="text" class="form-control" name="plan_purchase_user_template_id"
                            value="{{ $plan_purchase_user->template_id }}"
                            placeholder="{{ __('User Template Name') }}">
                    </div>

                    {{-- User template namespace --}}
                    <div class="mb-3 col-md-4">
                        <div class="form-label">{{ __('Template Namespace') }}</div>
                        <input type="text" class="form-control" name="plan_purchase_user_template_namespace"
                            value="{{ $plan_purchase_user->namespace }}" placeholder="{{ __('Template Namespace') }}">
                    </div>

                    {{-- Variables --}}
                    @php
                        $decodedVariabels2 = json_decode($plan_purchase_admin->variables ?? '[]', true);
                        $decodedVariabels3 = json_decode($plan_purchase_user->variables ?? '[]', true);
                    @endphp

                    {{-- Variables --}}
                    <div class="row">
                        {{-- Variables Admin --}}
                        <div class="col-12 col-md-6">
                            <h3 class="mb-3"> {{ __('Variables Admin') }} </h3>
                            <table class="table border" id="variablesTable2">
                                <tr>
                                    <th class="p-3 border-end w-1">{{ __('#') }}</th>
                                    <th class="p-3 border-end">{{ __('Variable') }}</th>
                                    <th class="p-3 w-50">{{ __('Value') }}</th>
                                </tr>
                                @for ($i = 1; $i <= 8; $i++)
                                    <tr>
                                        <td class="p-3 border-end text-center align-middle">
                                            <input type="checkbox" class="form-check-input variable-check"
                                                {{ isset($decodedVariabels2[$i - 1]) ? 'checked' : '' }}>
                                        </td>
                                        <td class="p-3 border-end align-middle">
                                            {!! '@{{ ' . $i . ' }}' !!}
                                        </td>
                                        <td class="p-3">
                                            <select class="form-select variable-select variables">
                                                <option value="app_name"
                                                    {{ ($decodedVariabels2[$i - 1] ?? '') == 'app_name' ? 'selected' : '' }}>
                                                    {{ __('App Name') }}
                                                </option>
                                                <option value="name"
                                                    {{ ($decodedVariabels2[$i - 1] ?? '') == 'name' ? 'selected' : '' }}>
                                                    {{ __('User Name') }}
                                                </option>
                                                <option value="email"
                                                    {{ ($decodedVariabels2[$i - 1] ?? '') == 'email' ? 'selected' : '' }}>
                                                    {{ __('User Email') }}
                                                </option>
                                                <option value="plan_name"
                                                    {{ ($decodedVariabels2[$i - 1] ?? '') == 'plan_name' ? 'selected' : '' }}>
                                                    {{ __('Plan Name') }}
                                                </option>
                                                <option value="currency"
                                                    {{ ($decodedVariabels2[$i - 1] ?? '') == 'currency' ? 'selected' : '' }}>
                                                    {{ __('Currency Code') }}
                                                </option>
                                                <option value="plan_price"
                                                    {{ ($decodedVariabels2[$i - 1] ?? '') == 'plan_price' ? 'selected' : '' }}>
                                                    {{ __('Plan Price') }}
                                                </option>
                                                <option value="plan_validity"
                                                    {{ ($decodedVariabels2[$i - 1] ?? '') == 'plan_validity' ? 'selected' : '' }}>
                                                    {{ __('Plan Validity') }}
                                                </option>
                                                <option value="plan_expiry_date"
                                                    {{ ($decodedVariabels2[$i - 1] ?? '') == 'plan_expiry_date' ? 'selected' : '' }}>
                                                    {{ __('Plan Expiry Date') }}
                                                </option>
                                            </select>
                                        </td>
                                    </tr>
                                @endfor
                            </table>

                            <!-- Hidden input to hold final array -->
                            <input type="hidden" name="variablesAdmin" id="variablesInput2">
                        </div>

                        {{-- Variables User --}}
                        <div class="col-12 col-md-6">
                            <h3 class="mb-3"> {{ __('Variables User') }} </h3>
                            <table class="table border" id="variablesTable3">
                                <tr>
                                    <th class="p-3 border-end w-1">{{ __('#') }}</th>
                                    <th class="p-3 border-end">{{ __('Variable') }}</th>
                                    <th class="p-3 w-50">{{ __('Value') }}</th>
                                </tr>
                                @for ($i = 1; $i <= 8; $i++)
                                    <tr>
                                        <td class="p-3 border-end text-center align-middle">
                                            <input type="checkbox" class="form-check-input variable-check"
                                                {{ isset($decodedVariabels3[$i - 1]) ? 'checked' : '' }}>
                                        </td>
                                        <td class="p-3 border-end align-middle">
                                            {!! '@{{ ' . $i . ' }}' !!}
                                        </td>
                                        <td class="p-3">
                                            <select class="form-select variable-select variables">
                                                <option value="app_name"
                                                    {{ ($decodedVariabels3[$i - 1] ?? '') == 'app_name' ? 'selected' : '' }}>
                                                    {{ __('App Name') }}
                                                </option>
                                                <option value="name"
                                                    {{ ($decodedVariabels3[$i - 1] ?? '') == 'name' ? 'selected' : '' }}>
                                                    {{ __('User Name') }}
                                                </option>
                                                <option value="email"
                                                    {{ ($decodedVariabels3[$i - 1] ?? '') == 'email' ? 'selected' : '' }}>
                                                    {{ __('User Email') }}
                                                </option>
                                                <option value="plan_name"
                                                    {{ ($decodedVariabels3[$i - 1] ?? '') == 'plan_name' ? 'selected' : '' }}>
                                                    {{ __('Plan Name') }}
                                                </option>
                                                <option value="currency"
                                                    {{ ($decodedVariabels3[$i - 1] ?? '') == 'currency' ? 'selected' : '' }}>
                                                    {{ __('Currency Code') }}
                                                </option>
                                                <option value="plan_price"
                                                    {{ ($decodedVariabels3[$i - 1] ?? '') == 'plan_price' ? 'selected' : '' }}>
                                                    {{ __('Plan Price') }}
                                                </option>
                                                <option value="plan_validity"
                                                    {{ ($decodedVariabels3[$i - 1] ?? '') == 'plan_validity' ? 'selected' : '' }}>
                                                    {{ __('Plan Validity') }}
                                                </option>
                                                <option value="plan_expiry_date"
                                                    {{ ($decodedVariabels3[$i - 1] ?? '') == 'plan_expiry_date' ? 'selected' : '' }}>
                                                    {{ __('Plan Expiry Date') }}
                                                </option>
                                            </select>
                                        </td>
                                    </tr>
                                @endfor
                            </table>

                            <!-- Hidden input to hold final array -->
                            <input type="hidden" name="variablesUser" id="variablesInput3">
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
