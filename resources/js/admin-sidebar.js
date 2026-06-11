/**
 * Parish admin sidebar — desktop accordion groups only.
 * Mobile uses Filament's native drawer; do not override positioning or collapse logic there.
 */
const ADMIN_SIDEBAR_NAV_VERSION = '6';
const DESKTOP_BREAKPOINT = 1024;

const isDesktop = () => window.matchMedia(`(min-width: ${DESKTOP_BREAKPOINT}px)`).matches;

const getGroupLabels = () =>
    [...document.querySelectorAll('.fi-sidebar-group[data-group-label]')]
        .map((element) => element.dataset.groupLabel)
        .filter(Boolean);

const ensureCollapsedArray = (store) => {
    if (! Array.isArray(store.collapsedGroups)) {
        store.collapsedGroups = [];
    }

    return store.collapsedGroups;
};

const refreshExpandedGroups = (store) => {
    if (! store || ! isDesktop()) {
        return;
    }

    document.querySelectorAll('.fi-sidebar-group[data-group-label]').forEach((groupElement) => {
        const label = groupElement.dataset.groupLabel;
        const collapsed = store.groupIsCollapsed(label);
        const items = groupElement.querySelector(':scope > .fi-sidebar-group-items');

        groupElement.classList.toggle('fi-parish-expanded', ! collapsed);

        if (! items || collapsed) {
            return;
        }

        for (const property of [
            'display',
            'height',
            'max-height',
            'min-height',
            'overflow',
            'opacity',
            'visibility',
        ]) {
            items.style.removeProperty(property);
        }
    });
};

const scheduleRefresh = (store) => {
    if (! store || ! isDesktop()) {
        return;
    }

    refreshExpandedGroups(store);
    requestAnimationFrame(() => refreshExpandedGroups(store));
    window.setTimeout(() => refreshExpandedGroups(store), 220);
};

const expandGroupForActivePage = (store) => {
    const labels = getGroupLabels();

    if (! labels.length || ! store) {
        return;
    }

    const activeItem = document.querySelector('.fi-sidebar-nav .fi-sidebar-item.fi-active');

    if (! activeItem) {
        if (isDesktop()) {
            scheduleRefresh(store);
        }

        return;
    }

    const groupElement = activeItem.closest('.fi-sidebar-group[data-group-label]');

    if (! groupElement) {
        if (isDesktop()) {
            scheduleRefresh(store);
        }

        return;
    }

    const label = groupElement.dataset.groupLabel;

    if (store.groupIsCollapsed(label)) {
        store.toggleCollapsedGroup(label);
    }

    if (isDesktop()) {
        store.collapsedGroups = labels.filter((entry) => entry !== label);
        scheduleRefresh(store);
    }
};

const patchSidebarStore = () => {
    const store = window.Alpine?.store('sidebar');

    if (! store) {
        return false;
    }

    const labels = getGroupLabels();

    if (! labels.length) {
        return false;
    }

    ensureCollapsedArray(store);

    if (! store.__parishSidebarPatched) {
        const defaultToggle = store.toggleCollapsedGroup.bind(store);

        store.toggleCollapsedGroup = function parishToggleCollapsedGroup(group) {
            if (! isDesktop()) {
                defaultToggle(group);

                return;
            }

            const allLabels = getGroupLabels();

            if (this.groupIsCollapsed(group)) {
                this.collapsedGroups = allLabels.filter((label) => label !== group);
            } else {
                this.collapsedGroups = [...allLabels];
            }

            scheduleRefresh(this);
        };

        store.__parishSidebarPatched = true;
    }

    if (isDesktop()) {
        if (localStorage.getItem('adminSidebarNavVersion') !== ADMIN_SIDEBAR_NAV_VERSION) {
            localStorage.setItem('adminSidebarNavVersion', ADMIN_SIDEBAR_NAV_VERSION);
            localStorage.removeItem('collapsedGroups');
            store.collapsedGroups = [...labels];
        } else if (store.collapsedGroups.length === 0) {
            store.collapsedGroups = [...labels];
        }
    }

    expandGroupForActivePage(store);

    return true;
};

const bootSidebar = () => {
    if (! patchSidebarStore()) {
        window.setTimeout(bootSidebar, 50);
        window.setTimeout(bootSidebar, 200);
    }
};

document.addEventListener('alpine:init', bootSidebar);

document.addEventListener('livewire:navigated', () => {
    window.setTimeout(() => {
        const store = window.Alpine?.store('sidebar');

        if (store) {
            ensureCollapsedArray(store);
            expandGroupForActivePage(store);
        }
    }, 50);
});

document.addEventListener('click', (event) => {
    const target = event.target;

    if (! (target instanceof Element)) {
        return;
    }

    const store = window.Alpine?.store('sidebar');

    if (target.closest('.fi-sidebar-group-btn, .fi-sidebar-group-collapse-btn')) {
        scheduleRefresh(store);

        return;
    }

    if (! isDesktop() && target.closest('.fi-sidebar-item-btn, .fi-sidebar-item a')) {
        store?.close();
    }
});

window.addEventListener('resize', () => {
    const store = window.Alpine?.store('sidebar');

    if (store) {
        expandGroupForActivePage(store);
    }
});
