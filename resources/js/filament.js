import 'flowbite';

// Create and append the progress bar element
const progressBar = document.createElement('div');
progressBar.id = 'page-loading-bar';
progressBar.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    width: 0%;
    height: 3px;
    background: linear-gradient(90deg, #18181b, #3f3f46, #cbd5e1);
    z-index: 99999;
    transition: width 0.3s ease-out, opacity 0.2s ease;
    opacity: 0;
    pointer-events: none;
    box-shadow: 0 0 8px rgba(24, 24, 27, 0.4);
`;

// Apply dark-mode adaptive colors if theme is dark
if (document.documentElement.classList.contains('dark')) {
    progressBar.style.background = 'linear-gradient(90deg, #ffffff, #cbd5e1, #a1a1aa)';
    progressBar.style.boxShadow = '0 0 8px rgba(255, 255, 255, 0.4)';
}

document.body.appendChild(progressBar);

// Functions to control loading state
let loadingTimeout = null;

function startLoading() {
    if (loadingTimeout) clearTimeout(loadingTimeout);
    progressBar.style.opacity = '1';
    progressBar.style.width = '0%';
    // Force reflow
    progressBar.offsetWidth;
    progressBar.style.width = '60%';
    
    loadingTimeout = setTimeout(() => {
        progressBar.style.width = '85%';
    }, 600);
}

function stopLoading() {
    if (loadingTimeout) clearTimeout(loadingTimeout);
    progressBar.style.width = '100%';
    loadingTimeout = setTimeout(() => {
        progressBar.style.opacity = '0';
        loadingTimeout = setTimeout(() => {
            progressBar.style.width = '0%';
        }, 300);
    }, 150);
}

// Trigger loading on page start (finish animation on load)
progressBar.style.opacity = '1';
progressBar.style.width = '40%';
window.addEventListener('load', () => {
    stopLoading();
});

// Trigger loading on page unload (navigation to another page)
window.addEventListener('beforeunload', () => {
    startLoading();
});

// Hook into Livewire AJAX requests for sorting, search, filtering
document.addEventListener('DOMContentLoaded', () => {
    if (window.Livewire) {
        Livewire.hook('message.sent', () => {
            startLoading();
        });
        Livewire.hook('message.processed', () => {
            stopLoading();
        });
        Livewire.hook('message.failed', () => {
            stopLoading();
        });
    }
});
