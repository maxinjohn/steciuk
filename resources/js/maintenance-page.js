document.addEventListener('DOMContentLoaded', () => {
    initMaintenanceFullscreen();
});

function fullscreenElement() {
    return document.fullscreenElement || document.webkitFullscreenElement || null;
}

export function initMaintenanceFullscreen(root = document) {
    const page = root.querySelector('.maintenance-page');
    const toggle = root.querySelector('[data-maintenance-fullscreen]');

    if (! page || ! toggle) {
        return;
    }

    const isActive = () => fullscreenElement() === page || page.classList.contains('maintenance-page--immersive');

    const syncUi = () => {
        const active = isActive();

        toggle.setAttribute('aria-pressed', active ? 'true' : 'false');
        toggle.hidden = active;
        page.classList.toggle('maintenance-page--fullscreen', active);
    };

    const enterFullscreen = async () => {
        if (page.requestFullscreen) {
            await page.requestFullscreen();

            return;
        }

        if (page.webkitRequestFullscreen) {
            page.webkitRequestFullscreen();

            return;
        }

        page.classList.add('maintenance-page--immersive');
        syncUi();
    };

    toggle.addEventListener('click', async () => {
        if (isActive()) {
            return;
        }

        try {
            await enterFullscreen();
        } catch (_error) {
            page.classList.add('maintenance-page--immersive');
            syncUi();
        }
    });

    document.addEventListener('fullscreenchange', syncUi);
    document.addEventListener('webkitfullscreenchange', syncUi);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && page.classList.contains('maintenance-page--immersive')) {
            page.classList.remove('maintenance-page--immersive');
            syncUi();
        }
    });
    syncUi();
}
