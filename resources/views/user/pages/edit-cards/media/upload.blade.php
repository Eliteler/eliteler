{{-- Failed Alert --}}
<div class="alert alert-important alert-danger alert-dismissible border-0 shadow-sm mb-3" id="failed"
    style="display: none;" role="alert">
    <div class="d-flex align-items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-circle" width="20"
            height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
            stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <circle cx="12" cy="12" r="9" />
            <line x1="12" y1="8" x2="12.01" y2="8" />
            <polyline points="11 12 12 12 12 16 13 16" />
        </svg>
        <div id="failedMessage"></div>
    </div>
    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
</div>

{{-- Success Alert --}}
<div class="alert alert-important alert-success alert-dismissible border-0 shadow-sm mb-3" id="success"
    style="display: none;" role="alert">
    <div class="d-flex align-items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check" width="20" height="20"
            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
            stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M5 12l5 5l10 -10" />
        </svg>
        <div id="successMessage"></div>
    </div>
    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
</div>

<style>
    .upload-zone-wrapper {
        border: 2px dashed var(--tblr-primary) !important;
        border-radius: var(--tblr-border-radius-lg) !important;
        background-color: var(--tblr-primary-lt) !important;
        min-height: 250px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.25s ease-in-out !important;
        padding: 2.5rem 1.5rem !important;
    }

    .upload-zone-wrapper:hover {
        background-color: rgba(var(--tblr-primary-rgb), 0.12) !important;
        transform: scale(1.005);
    }

    .upload-icon-circle {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background-color: var(--tblr-bg-surface);
        color: var(--tblr-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 14px rgba(var(--tblr-primary-rgb), 0.18);
        margin-bottom: 1.25rem;
        transition: transform 0.25s ease;
    }

    .upload-zone-wrapper:hover .upload-icon-circle {
        transform: translateY(-4px);
    }
</style>

{{-- Modern Dropzone Upload Card --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form action="{{ route('user.multiple') }}" class="dropzone upload-zone-wrapper" id="dropzone"
            enctype="multipart/form-data">
            @csrf
            <div class="dz-message text-center m-0">
                <div class="upload-icon-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-cloud-upload"
                        width="32" height="32" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1" />
                        <polyline points="9 15 12 12 15 15" />
                        <line x1="12" y1="12" x2="12" y2="21" />
                    </svg>
                </div>
                <h3 class="fw-bold mb-1 text-reset">{{ __('Drag & Drop Files Here') }}</h3>
                <p class="text-muted small mb-2">{{ __('or browse directly from your device') }}</p>
                <span
                    class="badge bg-primary-lt px-2 py-1">{{ __('Accepted formats: JPEG, JPG, PNG, GIF, WEBP') }}</span>
            </div>
        </form>
    </div>
</div>
