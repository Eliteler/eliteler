{{-- Smart Products: print files (image assets) uploaded by a customer. Core email styles. --}}
@php
    $coreLayout = @file_get_contents(resource_path('views/emails/appointment.blade.php')) ?: '';
    $coreCss = preg_match('#<style[^>]*>(.*?)</style>#s', $coreLayout, $match) ? $match[1] : '';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{{ __('Print files') }}</title>
    <style>
        {!! $coreCss !!}
        .assets td { padding: 6px 8px; border-bottom: 1px solid #edf2f7; font-size: 14px; }
    </style>
</head>

<body>
    <div class="container">
        <h2>
            {{ $justCompleted ? __('Print files are ready') : __('A print file was updated') }}
        </h2>
        <p>
            {{ __(':customer (:email) :action ":asset" for order :order.', [
                'customer' => $customer->name,
                'email' => $customer->email,
                'action' => $replaced ? __('replaced') : __('uploaded'),
                'asset' => $assetName,
                'order' => $order->nfc_card_order_id,
            ]) }}
        </p>

        <table class="assets" width="100%" cellpadding="0" cellspacing="0">
            @foreach ($assets as $asset)
                <tr>
                    <td>
                        <strong>{{ $asset['name'] }}</strong>
                        @if ($asset['required'])
                            <span style="color:#718096">({{ __('required') }})</span>
                        @endif
                        @if ($asset['resolution'])
                            <br><span style="color:#718096">{{ $asset['resolution'] }}</span>
                        @endif
                    </td>
                    <td align="right">
                        @if ($asset['url'])
                            <a href="{{ $asset['url'] }}">{{ __('Download') }}</a>
                        @else
                            <span style="color:#a0aec0">{{ __('Not uploaded') }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>

        <p style="margin-top:20px">
            <a href="{{ $orderUrl }}">{{ __('Open the order') }}</a>
        </p>
    </div>
</body>

</html>
