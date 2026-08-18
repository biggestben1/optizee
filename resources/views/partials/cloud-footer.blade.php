@php
    $footerBody = \App\Models\Setting::getValue('site.footer_body', '');
@endphp

@if(!empty($footerBody))
    <div class="text-center py-3">
        {!! $footerBody !!}
    </div>
@endif

