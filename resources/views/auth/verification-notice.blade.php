@php
    $web_template = getConfigData('web_template');
@endphp

@include($web_template . '::Website.pages.auth.verification-notice')