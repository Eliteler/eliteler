{{--
    Media-picker helper for image fields (input.media-model / input.image):
    - small thumbnail next to the field once an image is chosen
    - size hint under the field: recommended size (pass ['recommended' => '1280 × 720 px']) and the real size of the chosen image
--}}
<style>
    .media-thumb-wrap { padding: 3px !important; background: var(--tblr-bg-surface, #fff); }
    .media-thumb-box { width: 40px; height: 40px; display: block; border-radius: 6px; background: #f1f5f9 center / cover no-repeat; }
    .media-size-hint { display: block; margin-top: 4px; font-size: 12px; color: var(--tblr-secondary, #6c7a91); }
    .media-size-hint b { font-weight: 600; }
</style>
<script>
    (function() {
        "use strict";
        var base = "{{ rtrim(url('/'), '/') }}";
        var recommended = @json($recommended ?? null);
        var textRecommended = @json(__('Recommended size'));
        var textSelected = @json(__('Selected image'));
        var selector = 'input.media-model, input.image.form-control';

        function source(value) {
            value = (value || '').trim();
            if (!value) return '';
            if (/^(https?:)?\/\//i.test(value) || value.indexOf('data:') === 0) return value;
            return base + '/' + value.replace(/^\/+/, '');
        }

        function hintText(input) {
            var parts = [];
            if (recommended) parts.push(textRecommended + ': <b dir="ltr">' + recommended + '</b>');
            if (input._realSize) parts.push(textSelected + ': <b dir="ltr">' + input._realSize + '</b>');
            return parts.join(' &nbsp;·&nbsp; ');
        }

        function refresh(input) {
            var group = input.parentNode;
            if (!group) return;
            var wrap = input._thumbWrap;
            if (!wrap) {
                wrap = document.createElement('span');
                wrap.className = 'input-group-text media-thumb-wrap';
                wrap.innerHTML = '<span class="media-thumb-box"></span>';
                group.insertBefore(wrap, input);
                input._thumbWrap = wrap;

                var hint = document.createElement('small');
                hint.className = 'media-size-hint';
                if (group.nextSibling) group.parentNode.insertBefore(hint, group.nextSibling);
                else group.parentNode.appendChild(hint);
                input._sizeHint = hint;
            }

            var src = source(input.value);
            if (src !== input._thumbSrc) {
                input._thumbSrc = src;
                input._realSize = null;
                wrap.firstChild.style.backgroundImage = src ? 'url("' + src.replace(/"/g, '%22') + '")' : 'none';
                if (src) {
                    var probe = new Image();
                    probe.onload = function() {
                        if (input._thumbSrc !== src) return;
                        input._realSize = probe.naturalWidth + ' × ' + probe.naturalHeight + ' px';
                        input._sizeHint.innerHTML = hintText(input);
                    };
                    probe.src = src;
                }
            }
            wrap.style.display = src ? '' : 'none';
            input._sizeHint.innerHTML = hintText(input);
            input._sizeHint.style.display = input._sizeHint.innerHTML ? '' : 'none';
        }

        function scan(root) {
            if (root.querySelectorAll) root.querySelectorAll(selector).forEach(refresh);
        }

        document.addEventListener('DOMContentLoaded', function() {
            scan(document);
            // rows added later ("Add image")
            new MutationObserver(function(mutations) {
                mutations.forEach(function(m) {
                    m.addedNodes.forEach(function(node) {
                        if (node.nodeType !== 1) return;
                        if (node.matches && node.matches(selector)) refresh(node);
                        scan(node);
                    });
                });
            }).observe(document.body, { childList: true, subtree: true });
            // the media picker sets the value and closes; edit dialogs load a value and open
            ['hidden.bs.modal', 'shown.bs.modal'].forEach(function(name) {
                document.addEventListener(name, function() { scan(document); });
                if (window.jQuery) jQuery(document).on(name, function() { scan(document); });
            });
            document.addEventListener('change', function(e) {
                if (e.target.matches && e.target.matches(selector)) refresh(e.target);
            });
        });
    })();
</script>
