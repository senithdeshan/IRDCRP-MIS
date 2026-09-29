{{-- Inline fallback also works when Apache serves the page but asset URLs fail. --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.Alpine) return;

        {!! str_replace('export default ', '', file_get_contents(resource_path('js/agreement-progress.js'))) !!}
        {!! str_replace('export default ', '', file_get_contents(resource_path('js/eoi-entry-location.js'))) !!}

        document.addEventListener('alpine:init', () => {
            window.Alpine.data('agreementProgress', agreementProgress);
            window.Alpine.data('eoiEntryLocation', eoiEntryLocation);
        }, { once: true });

        {!! file_get_contents(resource_path('js/vendor/cdn.min.js')) !!}
    }, { once: true });
</script>
