const CUT_PREP_MS = 750;
const CUT_ANIMATION_MS = 2400;
const SPLASH_HOLD_MS = 2800;

export function initLaunchCountdown(root = document) {
    const page = root.querySelector('.launch-page');
    const shell = root.querySelector('[data-launch-countdown]');

    if (! page || ! shell) {
        return;
    }

    const targetIso = page.dataset.countdownAt || shell.dataset.countdownAt;

    if (! targetIso) {
        return;
    }

    const units = {
        days: shell.querySelector('[data-unit="days"]'),
        hours: shell.querySelector('[data-unit="hours"]'),
        minutes: shell.querySelector('[data-unit="minutes"]'),
        seconds: shell.querySelector('[data-unit="seconds"]'),
    };

    const pad = (value) => String(Math.max(0, value)).padStart(2, '0');

    const tick = () => {
        const target = Date.parse(targetIso);
        const remaining = target - Date.now();

        if (Number.isNaN(target)) {
            return;
        }

        if (remaining <= 0) {
            Object.values(units).forEach((node) => {
                if (node) {
                    node.textContent = '00';
                }
            });

            window.setTimeout(() => window.location.reload(), 1200);

            return;
        }

        const totalSeconds = Math.floor(remaining / 1000);
        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        if (units.days) {
            units.days.textContent = pad(days);
        }

        if (units.hours) {
            units.hours.textContent = pad(hours);
        }

        if (units.minutes) {
            units.minutes.textContent = pad(minutes);
        }

        if (units.seconds) {
            units.seconds.textContent = pad(seconds);
        }
    };

    tick();
    window.setInterval(tick, 1000);
}

function spawnConfetti(host, count = 48) {
    if (! host) {
        return;
    }

    host.innerHTML = '';

    for (let index = 0; index < count; index += 1) {
        const piece = document.createElement('span');
        piece.className = 'launch-confetti-piece';
        piece.style.setProperty('--x', `${Math.random() * 100}%`);
        piece.style.setProperty('--delay', `${Math.random() * 0.55}s`);
        piece.style.setProperty('--hue', `${Math.floor(Math.random() * 360)}`);
        piece.style.setProperty('--drift', `${(Math.random() - 0.5) * 8}rem`);
        piece.style.setProperty('--lift', `${4 + Math.random() * 10}rem`);
        host.appendChild(piece);
    }
}

function showSplash(screen) {
    const splash = screen.querySelector('[data-launch-splash]');
    const page = document.querySelector('.launch-page');

    if (! splash) {
        return;
    }

    screen.classList.add('launch-ribbon-screen--splash');
    page?.classList.add('launch-page--splash');
    splash.hidden = false;

    window.requestAnimationFrame(() => {
        splash.classList.add('launch-splash--active');
        window.setTimeout(() => {
            splash.classList.add('launch-splash--reveal');
        }, 60);
    });
}

export function initLaunchRibbonCeremony(root = document) {
    const screen = root.querySelector('[data-launch-ribbon-ceremony]');
    const form = root.querySelector('[data-launch-ribbon-form]');
    const cutButton = root.querySelector('[data-launch-ribbon-cut]');
    const page = root.querySelector('.launch-page');

    if (! screen || ! form || ! cutButton) {
        return;
    }

    let submitting = false;

    cutButton.addEventListener('click', (event) => {
        event.preventDefault();

        if (submitting) {
            return;
        }

        submitting = true;
        cutButton.disabled = true;
        cutButton.classList.add('launch-ribbon-screen__cut--busy');

        screen.classList.add('launch-ribbon-screen--snipping');
        page?.classList.add('launch-page--preparing');

        window.setTimeout(() => {
            screen.classList.remove('launch-ribbon-screen--snipping');
            screen.classList.add('launch-ribbon-screen--cutting');
            page?.classList.add('launch-page--celebrate');

            spawnConfetti(screen.querySelector('.launch-ribbon-screen__confetti'), 56);
            spawnConfetti(screen.querySelector('.launch-ribbon-screen__confetti--burst'), 72);
        }, CUT_PREP_MS);

        window.setTimeout(() => {
            showSplash(screen);
        }, CUT_PREP_MS + CUT_ANIMATION_MS);

        window.setTimeout(() => {
            form.submit();
        }, CUT_PREP_MS + CUT_ANIMATION_MS + SPLASH_HOLD_MS);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initLaunchCountdown();
    initLaunchRibbonCeremony();
    initLaunchFullscreen();
});

function fullscreenElement() {
    return document.fullscreenElement || document.webkitFullscreenElement || null;
}

export function initLaunchFullscreen(root = document) {
    const page = root.querySelector('.launch-page');
    const toggle = root.querySelector('[data-launch-fullscreen]');

    if (! page || ! toggle) {
        return;
    }

    const isActive = () => fullscreenElement() === page || page.classList.contains('launch-page--immersive');

    const syncUi = () => {
        const active = isActive();

        toggle.setAttribute('aria-pressed', active ? 'true' : 'false');
        toggle.hidden = active;
        page.classList.toggle('launch-page--fullscreen', active);
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

        page.classList.add('launch-page--immersive');
        syncUi();
    };

    toggle.addEventListener('click', async () => {
        if (isActive()) {
            return;
        }

        try {
            await enterFullscreen();
        } catch (_error) {
            page.classList.add('launch-page--immersive');
            syncUi();
        }
    });

    document.addEventListener('fullscreenchange', syncUi);
    document.addEventListener('webkitfullscreenchange', syncUi);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && page.classList.contains('launch-page--immersive')) {
            page.classList.remove('launch-page--immersive');
            syncUi();
        }
    });
    syncUi();
}
