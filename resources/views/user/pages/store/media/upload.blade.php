{{-- Failed Alert --}}
<div class="alert alert-important alert-danger alert-dismissible border-0 shadow-sm mb-3" id="failed"
    style="display: none;" role="alert">
    <div class="d-flex">
        <div id="failedMessage"></div>
    </div>
    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
</div>

{{-- Success Alert --}}
<div class="alert alert-important alert-success alert-dismissible border-0 shadow-sm mb-3" id="success"
    style="display: none;" role="alert">
    <div class="d-flex">
        <div id="successMessage"></div>
    </div>
    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
</div>

{{-- Upgraded Dropzone Upload Card --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('user.multiple') }}" class="dropzone py-5 bg-transparent" id="dropzone"
            enctype="multipart/form-data"
            style="border: 2px dashed var(--tblr-border-color, #dbdfe6) !important; border-radius: 8px !important;">
            @csrf
            <div class="dz-message py-3">
                <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-secondary" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M7 18a6 6 0 0 1 0 -12a5 5 0 0 1 8 0a6 6 0 0 1 .5 12" />
                        <path d="M9 15l3 -3l3 3" />
                        <path d="M12 12l0 9" />
                    </svg>
                </div>
                <h3>{{ __('Drag and Drop Single/Multiple Files Here') }}</h3>
                <span class="text-muted small d-block mt-1">{{ __('Accepted formats: JPEG, JPG, PNG, GIF') }}</span>
            </div>
        </form>
    </div>
</div>
