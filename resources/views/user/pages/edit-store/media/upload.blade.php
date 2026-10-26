{{-- ==========================================================
     Toasts
========================================================== --}}
<div class="ml-toast-stack" aria-live="polite">
    <div class="alert alert-danger alert-dismissible border-0 shadow-lg mb-2 ml-toast" id="failed"
        style="display: none;" role="alert">
        <div class="d-flex align-items-center">
            <i class="ti ti-alert-triangle me-2 fs-3"></i>
            <div id="failedMessage" class="flex-fill"></div>
        </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></a>
    </div>

    <div class="alert alert-success alert-dismissible border-0 shadow-lg mb-2 ml-toast" id="success"
        style="display: none;" role="alert">
        <div class="d-flex align-items-center">
            <i class="ti ti-circle-check me-2 fs-3"></i>
            <div id="successMessage" class="flex-fill"></div>
        </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></a>
    </div>
</div>

<div id="showAlert"></div>

{{-- ==========================================================
     Upload card
========================================================== --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('user.multiple') }}" class="dropzone" id="dropzone" enctype="multipart/form-data">
            @csrf
            <div class="dz-message">
                <div class="mb-3">
                    <span class="avatar avatar-lg bg-primary-lt">
                        <i class="ti ti-cloud-upload fs-1"></i>
                    </span>
                </div>
                <h3>{{ __('Drag images here, or click to browse') }}</h3>
                <span class="text-muted">{{ __('JPEG, JPG, PNG or GIF · single file or multiple at once') }}</span>
            </div>
        </form>
    </div>
</div>

<style>
    #dropzone.dropzone {
        border: 2px dashed var(--tblr-border-color) !important;
        border-radius: var(--tblr-border-radius, 4px);
        transition: border-color .15s ease, background-color .15s ease;
    }

    #dropzone.dropzone:hover,
    #dropzone.dropzone.dz-drag-hover {
        border-color: var(--tblr-primary) !important;
        background: var(--tblr-primary-lt, rgba(32, 107, 196, .06));
    }

    .ml-toast-stack {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 1080;
        width: min(380px, calc(100vw - 2rem));
    }

    .ml-toast {
        animation: ml-toast-in .2s ease-out;
    }

    @keyframes ml-toast-in {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .ml-toast {
            animation: none !important;
        }
    }
</style>
