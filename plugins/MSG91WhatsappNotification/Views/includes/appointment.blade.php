<div class="accordion-item">
    <form action="{{ route('admin.msg91_whatsapp_template_appointment.update') }}" method="post" id="myForm6">
        @csrf
        <h2 class="accordion-header" id="heading-6">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-6"
                aria-expanded="false">
                <h3 class="card-title">{{ __('Appointment Notification') }}</h3>
            </button>
        </h2>
        <div id="collapse-6" class="accordion-collapse collapse" data-bs-parent="#accordion-example">
            <div class="accordion-body pt-0">
                @php
                    $appointmentNotificationSections = [
                        'new_appointment' => [
                            'title' => 'New Appointment Notification',
                            'user' => $msg91_whatsapp_notification_templates[7] ?? null,
                            'customer' => $msg91_whatsapp_notification_templates[8] ?? null,
                        ],
                        'appointment_confirmed' => [
                            'title' => 'Appointment Confirmed Notification',
                            'user' => $msg91_whatsapp_notification_templates[9] ?? null,
                            'customer' => $msg91_whatsapp_notification_templates[10] ?? null,
                        ],
                        'appointment_cancelled' => [
                            'title' => 'Appointment Cancelled Notification',
                            'user' => $msg91_whatsapp_notification_templates[11] ?? null,
                            'customer' => $msg91_whatsapp_notification_templates[12] ?? null,
                        ],
                        'appointment_rescheduled' => [
                            'title' => 'Appointment Rescheduled Notification',
                            'user' => $msg91_whatsapp_notification_templates[13] ?? null,
                            'customer' => $msg91_whatsapp_notification_templates[14] ?? null,
                        ],
                        'appointment_completed' => [
                            'title' => 'Appointment Completed Notification',
                            'user' => $msg91_whatsapp_notification_templates[15] ?? null,
                            'customer' => $msg91_whatsapp_notification_templates[16] ?? null,
                        ],
                    ];
                @endphp

                @foreach ($appointmentNotificationSections as $key => $section)
                    <div class="row border-bottom mb-4">
                        <h3 class="mb-3">{{ __($section['title']) }}</h3>

                        @foreach (['user' => 'User', 'customer' => 'Customer'] as $type => $label)
                            @php
                                $template = $section[$type];
                                if (!$template) {
                                    continue;
                                }
                                $decodedVariables = json_decode($template->variables ?? '[]', true);
                                $tableId = $key . '_' . $type;
                            @endphp

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-label">{{ __("Send Notification to $label") }}</div>
                                    <select class="form-select tom-select"
                                        name="{{ $key }}_{{ $type }}_is_enabled">
                                        <option value="1" {{ $template->is_enabled ? 'selected' : '' }}>
                                            {{ __('Yes') }}</option>
                                        <option value="0" {{ !$template->is_enabled ? 'selected' : '' }}>
                                            {{ __('No') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3 col-md-4">
                                <div class="form-label">{{ __("Template Name ($label)") }}</div>
                                <input type="text" class="form-control"
                                    name="{{ $key }}_{{ $type }}_template_id"
                                    placeholder="{{ __('Template Name') }}" value="{{ $template->template_id }}">
                            </div>

                            <div class="mb-3 col-md-4">
                                <div class="form-label">{{ __("Template Namespace ($label)") }}</div>
                                <input type="text" class="form-control"
                                    name="{{ $key }}_{{ $type }}_template_namespace"
                                    placeholder="{{ __('Template Namespace') }}" value="{{ $template->namespace }}">
                            </div>

                            <div class="col-12 mb-3">
                                <h5>{{ __("Variables $label") }}</h5>

                                <table class="table border" id="variablesTable_{{ $tableId }}">
                                    <tr>
                                        <th class="p-3 border-end w-1">{{ __('#') }}</th>
                                        <th class="p-3 border-end">{{ __('Variable') }}</th>
                                        <th class="p-3 w-50">{{ __('Value') }}</th>
                                    </tr>
                                    @for ($i = 1; $i <= 8; $i++)
                                        <tr>
                                            <td class="p-3 border-end text-center align-middle">
                                                <input type="checkbox" class="variable-check"
                                                    {{ isset($decodedVariables[$i - 1]) ? 'checked' : '' }}>
                                            </td>
                                            <td class="p-3 border-end align-middle">
                                                {!! '@{{ ' . $i . ' }}' !!}
                                            </td>
                                            <td class="p-3">
                                                <select class="form-select tom-select variable-select">
                                                    <option value="app_name"
                                                        {{ ($decodedVariables[$i - 1] ?? '') == 'app_name' ? 'selected' : '' }}>
                                                        {{ __('App Name') }}</option>
                                                    <option value="name"
                                                        {{ ($decodedVariables[$i - 1] ?? '') == 'name' ? 'selected' : '' }}>
                                                        {{ __('Customer Name') }}</option>
                                                    <option value="email"
                                                        {{ ($decodedVariables[$i - 1] ?? '') == 'email' ? 'selected' : '' }}>
                                                        {{ __('Customer Email') }}</option>
                                                    <option value="phone"
                                                        {{ ($decodedVariables[$i - 1] ?? '') == 'phone' ? 'selected' : '' }}>
                                                        {{ __('Phone') }}</option>
                                                    <option value="appointment_date"
                                                        {{ ($decodedVariables[$i - 1] ?? '') == 'appointment_date' ? 'selected' : '' }}>
                                                        {{ __('Appointment Date') }}</option>
                                                    <option value="appointment_time"
                                                        {{ ($decodedVariables[$i - 1] ?? '') == 'appointment_time' ? 'selected' : '' }}>
                                                        {{ __('Appointment Time') }}</option>
                                                    <option value="appointment_rescheduled_date"
                                                        {{ ($decodedVariables[$i - 1] ?? '') == 'appointment_rescheduled_date' ? 'selected' : '' }}>
                                                        {{ __('Rescheduled Date') }}</option>
                                                    <option value="appointment_rescheduled_time"
                                                        {{ ($decodedVariables[$i - 1] ?? '') == 'appointment_rescheduled_time' ? 'selected' : '' }}>
                                                        {{ __('Rescheduled Time') }}</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endfor
                                </table>

                                <input type="hidden" name="{{ $key }}_{{ $type }}_variables"
                                    id="variablesInput_{{ $tableId }}">
                            </div>
                        @endforeach
                    </div>
                @endforeach

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>
