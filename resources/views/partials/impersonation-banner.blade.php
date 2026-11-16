{{-- Shown while an admin is signed in as this account (App\Support\Impersonation) --}}
@php($impersonation = \App\Support\Impersonation::banner())
@if ($impersonation)
    <div role="status" class="d-print-none"
        style="position: fixed; inset-inline: 12px; bottom: 12px; z-index: 1090; display: flex; justify-content: center; pointer-events: none;">
        <form method="POST" action="{{ $impersonation['leaveUrl'] }}"
            style="pointer-events: auto; display: flex; align-items: center; gap: 12px; max-width: 100%; padding: 6px 6px 6px 14px; border: 1px solid #fcd34d; border-radius: 999px; background: #fffbeb; color: #451a03; font-size: 14px; box-shadow: 0 10px 25px rgba(0, 0, 0, .15);">
            @csrf
            <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ __('Signed in as') }} <strong>{{ $impersonation['name'] }}</strong>
            </span>
            <button type="submit"
                style="flex-shrink: 0; border: 0; border-radius: 999px; padding: 6px 14px; background: #78350f; color: #fff; font-size: 12px; font-weight: 600; cursor: pointer;">
                {{ __('Back to admin') }}
            </button>
        </form>
    </div>
@endif
