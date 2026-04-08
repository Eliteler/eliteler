@php
    $popup = \App\WebPopup::where('template_id', 'GoBizOriginal')->where('enabled', 1)->where('status', 1)->first();
@endphp

@if ($popup && $popup->image)

    <style>
        #popup-toggle {
            display: none;
        }

        /* overlay */
        .popup-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .65);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999999;
        }

        #popup-toggle:checked+.popup-overlay {
            display: flex;
        }

        .popup-box {
            position: relative;
            max-width: 520px;
            width: 95%;
            z-index: 2;
        }

        .popup-img {
            width: 100%;
            border-radius: 14px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .35);
        }

        .popup-close {
            position: absolute;
            top: -12px;
            right: -12px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .25);
        }

        /* outside click layer */
        .popup-bg {
            position: absolute;
            inset: 0;
            cursor: pointer;
            z-index: 1;
        }
    </style>

    <input type="checkbox" id="popup-toggle" checked>

    <div class="popup-overlay">

        <!-- outside click -->
        <label for="popup-toggle" class="popup-bg"></label>

        <div class="popup-box">

            <label for="popup-toggle" class="popup-close">×</label>

            @if ($popup->link)
                <a href="{{ $popup->link }}" target="_blank">
                    <img src="{{ asset($popup->image) }}" class="popup-img" alt="Popup">
                </a>
            @else
                <img src="{{ asset($popup->image) }}" class="popup-img" alt="Popup">
            @endif

        </div>

    </div>

@endif
