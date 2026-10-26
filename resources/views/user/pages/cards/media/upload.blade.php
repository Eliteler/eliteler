{{-- ==========================================================
     Toasts / Alerts Stack
========================================================== --}}
<div class="ml-toast-stack" aria-live="polite">
    <div class="alert alert-danger alert-dismissible border-0 shadow-lg mb-2 ml-toast" id="failed"
        style="display: none;" role="alert">
        <div class="d-flex align-items-center">
            <i class="ti ti-alert-triangle me-2 fs-2"></i>
            <div id="failedMessage" class="flex-fill small"></div>
        </div>
        <button type="button" class="btn-close" onclick="this.closest('.alert').style.display='none';"
            aria-label="{{ __('Close') }}"></button>
    </div>

    <div class="alert alert-success alert-dismissible border-0 shadow-lg mb-2 ml-toast" id="success"
        style="display: none;" role="alert">
        <div class="d-flex align-items-center">
            <i class="ti ti-circle-check me-2 fs-2"></i>
            <div id="successMessage" class="flex-fill small"></div>
        </div>
        <button type="button" class="btn-close" onclick="this.closest('.alert').style.display='none';"
            aria-label="{{ __('Close') }}"></button>
    </div>
</div>

<div id="showAlert" class="mb-3"></div>

{{-- ==========================================================
     Upload Dropzone Area
========================================================== --}}
<div class="upload-container mb-4">
    <form action="{{ route('user.multiple') }}" class="dropzone" id="dropzone" enctype="multipart/form-data">
        @csrf
        <div class="dz-message py-3">
            <div class="mb-2">
                <span class="avatar avatar-md bg-primary-lt rounded-circle">
                    <i class="ti ti-cloud-upload fs-2 text-primary"></i>
                </span>
            </div>
            <h4 class="mb-1 text-body">{{ __('Drag images here, or click to browse') }}</h4>
            <span class="text-secondary small">{{ __('JPEG, JPG, PNG or GIF · single or multiple files') }}</span>
        </div>
    </form>
</div>

<style>
    /* Dropzone Custom Styling */
    #dropzone.dropzone {
        border: 2px dashed var(--tblr-border-color, #cbd5e1) !important;
        border-radius: 8px;
        background: var(--tblr-bg-surface-secondary, #f8fafc);
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        padding: 1.5rem 1rem;
        min-height: auto;
    }

    #dropzone.dropzone:hover,
    #dropzone.dropzone.dz-drag-hover {
        border-color: var(--tblr-primary, #206bc4) !important;
        background: var(--tblr-primary-lt, rgba(32, 107, 196, 0.06)) !important;
    }

    /* Toast Stack Positioning */
    .ml-toast-stack {
        position: fixed;
        top: 1.25rem;
        right: 1.25rem;
        z-index: 1090;
        width: min(360px, calc(100vw - 2.5rem));
    }

    .ml-toast {
        animation: ml-toast-in 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes ml-toast-in {
        from {
            opacity: 0;
            transform: translateY(-10px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .ml-toast {
            animation: none !important;
        }
    }
</style>
