{{--
 | Plugin widgets in the Blade customer panel (App\Support\Plugins\PluginExtensions::widgets()).
 | The React panel (ModernDashboard) loads the same files itself and sets the same config.
 |
 | Each widget is a self-contained ES module (plugins/<Name>/dist/widgets/<name>.js) that reads
 | window.GoBizWidgetConfig and mounts itself once, even if its script is loaded again.
--}}
@php
    $pluginWidgets = \App\Support\Plugins\PluginExtensions::widgets();
@endphp

@if ($pluginWidgets)
    @php
        $widgetConfig = [
            'panel'     => $panel ?? 'blade',
            'baseUrl'   => url('/'),
            'csrfToken' => csrf_token(),
            'locale'    => app()->getLocale(),
            'rtl'       => in_array(app()->getLocale(), ['ar', 'ur', 'he', 'fa'], true),
        ];
    @endphp
    <script>
        window.GoBizWidgetConfig = @json($widgetConfig);
    </script>

    @foreach ($pluginWidgets as $widget)
        @if ($widget['style'])
            <link rel="stylesheet" href="{{ $widget['style'] }}" data-plugin-widget="{{ $widget['plugin'] }}:{{ $widget['name'] }}">
        @endif
        <script type="module" src="{{ $widget['script'] }}" data-plugin-widget="{{ $widget['plugin'] }}:{{ $widget['name'] }}"></script>
    @endforeach
@endif
