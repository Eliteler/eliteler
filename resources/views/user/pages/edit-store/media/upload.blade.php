{{-- Failed --}}
<div class="alert alert-important alert-danger alert-dismissible" id="failed" role="alert">
    <div class="d-flex">
        <div id="failedMessage"></div>
    </div>
    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
</div>

{{-- Success --}}
<div class="alert alert-important alert-success alert-dismissible" id="success" role="alert">
    <div class="d-flex">
        <div id="successMessage"></div>
    </div>
    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
</div>

{{-- Upload multiple images --}}
<div class="col-sm-12 col-lg-12 mb-4">
    <form action="{{ route('user.multiple') }}" class="dropzone" id="dropzone" enctype="multipart/form-data">
        @csrf
        <div class="dz-message">
            {{ __('Drag and Drop Single/Multiple Files Here') }} <br>
        </div>
    </form>
</div>
