<button id="darkModeToggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-900 transition-colors duration-200"
    aria-label="Toggle dark mode">
    <div id="sunIcon" style="display: none;">
        <x-heroicon-s-sun class="w-6 h-6 text-yellow-400" />
    </div>
    <div id="moonIcon">
        <x-heroicon-s-moon class="w-6 h-6 text-gray-700" />
    </div>
</button>

<script>
    (function() {
        const toggle = document.getElementById('darkModeToggle');
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');
        const html = document.documentElement;

        function updateUI(isDark) {
            if (isDark) {
                html.classList.add('dark');

                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            } else {
                html.classList.remove('dark');
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            }
        }

        // Initialize theme state based on localStorage
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isDarkNow = savedTheme === 'dark' || (!savedTheme && prefersDark);

        updateUI(isDarkNow);

        // Click Handler
        toggle.addEventListener('click', function() {
            const willBeDark = !html.classList.contains('dark');
            localStorage.setItem('theme', willBeDark ? 'dark' : 'light');
            updateUI(willBeDark);
        });
    })();
</script>
