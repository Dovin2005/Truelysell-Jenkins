@php
    use Nwidart\Modules\Facades\Module;
@endphp

@if (Module::isEnabled('Installer'))
<style>
    :root {
        --primary: #FD2692;
        --secondary: #0A67F2;
        --primary-hover: #db0077;
        --secondary-hover: #20226f;
    }
</style>
@else
<style>
    :root {
        --primary: {{ getSetting('primary_color', '#FD2692') }};
        --secondary: {{ getSetting('secondary_color', '#0A67F2') }};
        --primary-hover: {{ getSetting('primary_hover_color', '#db0077') }};
        --secondary-hover: {{ getSetting('secondary_hover_color', '#20226f') }};
    }
</style>
@endif