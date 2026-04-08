<!-- Add to homescreen for Chrome on Android -->
<meta name="mobile-web-app-capable" content="{{ $config['display'] == 'standalone' ? 'yes' : 'no' }}">

<!-- Add to homescreen for Safari on iOS -->
<meta name="apple-mobile-web-app-capable" content="{{ $config['display'] == 'standalone' ? 'yes' : 'no' }}">
<meta name="apple-mobile-web-app-status-bar-style" content="{{ $config['status_bar'] }}">

<script type="text/javascript">
    // Initialize the service worker
    if ('serviceWorker' in navigator) {
        fetch('/generate-service-worker')
            .then(response => response.json())
            .then(data => {
                navigator.serviceWorker.register(data.serviceWorkerUrl)
                    .then((registration) => {
                        console.log('Service Worker registered with scope:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('Service Worker registration failed:', error);
                    });
            });
    }

    // Check if the app is installed
    window.addEventListener('beforeinstallprompt', function(event) {
        event.preventDefault();
        // Show the "Add to Home Screen" button
        document.getElementById('pwaModal').classList.remove('hidden');
        document.getElementById('pwaModal').classList.add('show');
        document.getElementById('pwaModal').style.display = 'block';;

        $("#addToHomeScreenButton").on("click", function() {
            event.prompt();
            event.userChoice.then(function(choiceResult) {
                if (choiceResult.outcome === 'accepted') {
                    document.getElementById('pwaModal').classList.add('hidden');
                    document.getElementById('pwaModal').classList.remove('show');
                    document.getElementById('pwaModal').style.display = 'none';;
                } else {
                    document.getElementById('pwaModal').classList.add('hidden');
                    document.getElementById('pwaModal').classList.remove('show');
                    document.getElementById('pwaModal').style.display = 'none';;
                }
            });
        });

        $("#closeModal").on("click", function() {
            document.getElementById('pwaModal').classList.add('hidden');
            document.getElementById('pwaModal').classList.remove('show');
            document.getElementById('pwaModal').style.display = 'none';;
        });
    });
</script>
