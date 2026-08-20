<style>
    #darkModeToggle {
        position: relative;
        width: 68px;
        height: 33px;
        border-radius: 9999px;
        border: none;
        padding: 0;
        cursor: pointer;
        background: linear-gradient(to right, #bae6fd, #f0f9ff);
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.15);
        transition: background .5s ease;
    }

    html.dark #darkModeToggle {
        background: linear-gradient(to right, #1e1b4b, #312e81);
    }

    #darkModeToggle:active .knob {
        width: 10px;
    }

    #darkModeToggle::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 9999px;
        background-image:
            radial-gradient(1.5px 1.5px at 12px 10px, white, transparent),
            radial-gradient(1px 1px at 20px 22px, white, transparent),
            radial-gradient(1.5px 1.5px at 30px 8px, white, transparent);
        opacity: 0;
        transition: opacity .4s ease;
    }

    html.dark #darkModeToggle::before {
        opacity: 0.9;
    }

    .knob {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 30px;
        height: 27px;
        border-radius: 9999px;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        transition:
            transform .45s cubic-bezier(.34, 1.56, .64, 1),
            width .15s ease,
            background .4s ease;
    }

    html.dark .knob {
        transform: translateX(32px);
        background: #27272a;
    }

    .knob svg {
        position: absolute;
        width: 20px;
        height: 20px;
        transition: transform .45s cubic-bezier(.34, 1.56, .64, 1), opacity .3s ease;
    }

    #sunIcon {
        color: #f59e0b;
        opacity: 1;
        transform: rotate(0deg) scale(1);
    }

    #moonIcon {
        color: #d6deff;
        opacity: 0;
        transform: rotate(90deg) scale(0.4);
    }

    html.dark #sunIcon {
        opacity: 0;
        transform: rotate(-90deg) scale(0.4);
    }

    html.dark #moonIcon {
        opacity: 1;
        transform: rotate(0deg) scale(1);
    }

    @media (prefers-reduced-motion: reduce) {

        .knob,
        .knob svg,
        #darkModeToggle,
        #darkModeToggle::before {
            transition: none !important;
        }
    }
</style>

<button id="darkModeToggle" aria-label="Toggle dark mode" aria-pressed="false">
    <div class="knob">
        <svg id="sunIcon" viewBox="0 0 24 24" fill="currentColor">
            <path
                d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z" />
        </svg>
        <svg id="moonIcon" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd"
                d="M9.528 1.718a.75.75 0 01.162.819A8.97 8.97 0 009 6a9 9 0 009 9 8.97 8.97 0 003.463-.69.75.75 0 01.981.98 10.503 10.503 0 01-9.694 6.46c-5.799 0-10.5-4.7-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 01.818.162z"
                clip-rule="evenodd" />
        </svg>
    </div>
</button>

<script>
    (function() {
        const toggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;

        function updateUI(isDark) {
            html.classList.toggle('dark', isDark);
            toggle.setAttribute('aria-pressed', String(isDark));
        }

        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        updateUI(savedTheme === 'dark' || (!savedTheme && prefersDark));

        toggle.addEventListener('click', function() {
            const willBeDark = !html.classList.contains('dark');
            localStorage.setItem('theme', willBeDark ? 'dark' : 'light');
            updateUI(willBeDark);
        });
    })();
</script>
