<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ __('Contact Form Submission') }}</title>
</head>

<body style="margin:0;padding:0;background-color:#f4f6f8;font-family:Arial,Helvetica,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8;padding:30px 0;">
        <tr>
            <td align="center">

                <!-- Email Container -->
                <table width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="padding:20px 30px;background:#0f172a;color:#ffffff;">
                            <h2 style="margin:0;font-size:20px;font-weight:600;">
                                {{ __('Contact Form Submission') }}
                            </h2>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:30px;color:#334155;font-size:14px;line-height:1.6;">

                            <p style="margin:0 0 12px;">
                               {{ __('Hi, You have received a new message from the contact form.') }}
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="margin-top:20px;border-collapse:collapse;">
                                <tr>
                                    <td style="padding:8px 0;font-weight:bold;color:#0f172a;">{{ __('First Name') }}:</td>
                                    <td style="padding:8px 0;">{{ $data['first_name'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;font-weight:bold;color:#0f172a;">{{ __('Last Name') }}:</td>
                                    <td style="padding:8px 0;">{{ $data['last_name'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;font-weight:bold;color:#0f172a;">{{ __('Email') }}:</td>
                                    <td style="padding:8px 0;">
                                        <a href="mailto:{{ $data['email'] }}"
                                            style="color:#2563eb;text-decoration:none;">
                                            {{ $data['email'] }}
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top:24px;">
                                <p style="margin:0 0 6px;font-weight:bold;color:#0f172a;">
                                    {{ __('Message') }}:
                                </p>
                                <div
                                    style="padding:15px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;">
                                    {{ $data['message'] }}
                                </div>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="padding:20px 30px;background:#f8fafc;color:#64748b;font-size:12px;text-align:center;">
                            © {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
