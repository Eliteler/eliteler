{{-- Small thumbnail next to every media-picker input (input.media-model), shown once an image is chosen --}}
<style>
    .media-thumb-wrap { padding: 3px !important; background: var(--tblr-bg-surface, #fff); }
    .media-thumb-box { width: 40px; height: 40px; display: block; border-radius: 6px; background: #f1f5f9 center / cover no-repeat; }
</style>
<script>
    (function() {
        "use strict";
        var base = "{{ rtrim(url('/'), '/') }}";

        function source(value) {
            value = (value || '').trim();
            if (!value) return '';
            if (/^(https?:)?\/\//i.test(value) || value.indexOf('data:') === 0) return value;
            return base + '/' + value.replace(/^\/+/, '');
        }

        function refresh(input) {
            var wrap = input._thumbWrap;
            if (!wrap) {
                wrap = document.createElement('span');
                wrap.className = 'input-group-text media-thumb-wrap';
                wrap.innerHTML = '<span class="media-thumb-box"></span>';
                input.parentNode.insertBefore(wrap, input);
                input._thumbWrap = wrap;
            }
            var src = source(input.value);
            wrap.firstChild.style.backgroundImage = src ? 'url("' + src.replace(/"/g, '%22') + '")' : 'none';
            wrap.style.display = src ? '' : 'none';
        }

        function scan(root) {
            if (root.querySelectorAll) root.querySelectorAll('input.media-model').forEach(refresh);
        }

        document.addEventListener('DOMContentLoaded', function() {
            scan(document);
            // rows added later ("Add image")
            new MutationObserver(function(mutations) {
                mutations.forEach(function(m) {
                    m.addedNodes.forEach(function(node) {
                        if (node.nodeType !== 1) return;
                        if (node.matches && node.matches('input.media-model')) refresh(node);
                        scan(node);
                    });
                });
            }).observe(document.body, { childList: true, subtree: true });
            // the media picker sets the value and closes: refresh then
            document.addEventListener('hidden.bs.modal', function() { scan(document); });
            if (window.jQuery) jQuery(document).on('hidden.bs.modal', function() { scan(document); });
            document.addEventListener('change', function(e) {
                if (e.target.matches && e.target.matches('input.media-model')) refresh(e.target);
            });
        });
    })();
</script>
