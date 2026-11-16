<div class="accordion-item">
    <form action="{{ route('admin.msg91_whatsapp_template_user_plan_expiry_remainder.update') }}" method="post"
        enctype="multipart/form-data" id="myForm4">
        @csrf
        <h2 class="accordion-header" id="heading-4">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapse-4" aria-expanded="false">
                <h3 class="card-title">{{ __('User Plan Expiry Remainder') }}</h3>
            </button>
        </h2>
        <div id="collapse-4" class="accordion-collapse collapse" data-bs-parent="#accordion-example">
            <div class="alert alert-important alert-info alert-dismissible mx-3" role="alert">
                <div class="d-flex">
                    <div>
                        {{ __('Note: You need to specify the date and time in the cron job to send notifications. (Settings->Cron Jobs)') }}
                    </div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
            <div class="accordion-body pt-0">
                <div class="row">
                    {{-- Is enabled user --}}
                    <div>
                        <h3>{{ __('For User') }}</h3>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="form-label">
                                {{ __('Send Notification to User') }}</div>
                            <select class="form-select user_remainder" name="user_plan_expiry_remainder">
                                <option value="1"
                                    {{ $user_plan_expiry_remainder->is_enabled == 1 ? 'selected' : '' }}>
                                    {{ __('Yes') }}</option>
                                <option value="0"
                                    {{ $user_plan_expiry_remainder->is_enabled == 0 ? 'selected' : '' }}>
                                    {{ __('No') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- User content sid --}}
                    <div class="mb-3 col-md-4">
                        <div class="form-label">{{ __('User Template Name') }}</div>
                        <input type="text" class="form-control" name="user_plan_expiry_remainder_template_id"
                            value="{{ $user_plan_expiry_remainder->template_id }}"
                            placeholder="{{ __('User Template Name') }}">
                    </div>

                    {{-- User template namespace --}}
                    <div class="mb-3 col-md-4">
                        <div class="form-label">{{ __('Template Namespace') }}</div>
                        <input type="text" class="form-control" name="user_plan_expiry_remainder_template_namespace"
                            value="{{ $user_plan_expiry_remainder->namespace }}"
                            placeholder="{{ __('Template Namespace') }}">
                    </div>

                    {{-- Variables --}}
                    @php
                        $decodedVariabels6 = json_decode($user_plan_expiry_remainder->variables ?? '[]', true);
                    @endphp
                    <div class="col-12 col-md-6 col-xl-6">
                        <h3 class="mb-3"> {{ __('Variables User') }} </h3>
                        <table class="table border" id="variablesTable6">
                            <tr>
                                <th class="p-3 border-end w-1">{{ __('#') }}</th>
                                <th class="p-3 border-end">{{ __('Variable') }}</th>
                                <th class="p-3 w-50">{{ __('Value') }}</th>
                            </tr>
                            @for ($i = 1; $i <= 5; $i++)
                                <tr>
                                    <td class="p-3 border-end text-center align-middle">
                                        <input type="checkbox" class="form-check-input variable-check"
                                            {{ isset($decodedVariabels6[$i - 1]) ? 'checked' : '' }}>
                                    </td>
                                    <td class="p-3 border-end align-middle">
                                        {!! '@{{ ' . $i . ' }}' !!}
                                    </td>
                                    <td class="p-3">
                                        <select class="form-select variable-select variables">
                                            <option value="app_name"
                                                {{ ($decodedVariabels6[$i - 1] ?? '') == 'app_name' ? 'selected' : '' }}>
                                                {{ __('App Name') }}
                                            </option>
                                            <option value="name"
                                                {{ ($decodedVariabels6[$i - 1] ?? '') == 'name' ? 'selected' : '' }}>
                                                {{ __('User Name') }}
                                            </option>
                                            <option value="email"
                                                {{ ($decodedVariabels6[$i - 1] ?? '') == 'email' ? 'selected' : '' }}>
                                                {{ __('User Email') }}
                                            </option>
                                            <option value="plan_name"
                                                {{ ($decodedVariabels6[$i - 1] ?? '') == 'plan_name' ? 'selected' : '' }}>
                                                {{ __('Plan Name') }}
                                            </option>
                                            <option value="expiry_date"
                                                {{ ($decodedVariabels6[$i - 1] ?? '') == 'expiry_date' ? 'selected' : '' }}>
                                                {{ __('Plan Expiry Date') }}
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            @endfor
                        </table>

                        <!-- Hidden input to hold final array -->
                        <input type="hidden" name="variables" id="variablesInput6">
                    </div>

                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
