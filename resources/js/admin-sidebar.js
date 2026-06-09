/**
 * Parish admin sidebar — accordion groups (one open at a time).
 *
 * Filament pre-hides collapsed groups with inline display:none and uses
 * x-collapse on .fi-sidebar-group-items. Our job is to manage collapsedGroups
 * in the Alpine store and clear stale inline styles when a group expands —
 * without fighting Alpine's fi-collapsed class binding.
 */
const ADMIN_SIDEBAR_NAV_VERSION = '5';

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
    if (! store) {
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

        // Remove inline styles left by Filament's pre-Alpine hide script or a
        // stuck x-collapse height (shows only the first submenu item).
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
    if (! store) {
        return;
    }

    refreshExpandedGroups(store);
    requestAnimationFrame(() => refreshExpandedGroups(store));
    window.setTimeout(() => refreshExpandedGroups(store), 220);
    window.setTimeout(() => refreshExpandedGroups(store), 400);
};

const expandGroupForActivePage = (store) => {
    const labels = getGroupLabels();

    if (! labels.length) {
        return;
    }

    const activeItem = document.querySelector('.fi-sidebar-nav .fi-sidebar-item.fi-active');

    if (! activeItem) {
        scheduleRefresh(store);

        return;
    }

    const groupElement = activeItem.closest('.fi-sidebar-group[data-group-label]');

    if (! groupElement) {
        scheduleRefresh(store);

        return;
    }

    const label = groupElement.dataset.groupLabel;
    store.collapsedGroups = labels.filter((entry) => entry !== label);
    scheduleRefresh(store);
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
        store.toggleCollapsedGroup = function parishToggleCollapsedGroup(group) {
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

    if (localStorage.getItem('adminSidebarNavVersion') !== ADMIN_SIDEBAR_NAV_VERSION) {
        localStorage.setItem('adminSidebarNavVersion', ADMIN_SIDEBAR_NAV_VERSION);
        localStorage.removeItem('collapsedGroups');
        store.collapsedGroups = [...labels];
    } else if (store.collapsedGroups.length === 0) {
        store.collapsedGroups = [...labels];
    }

    expandGroupForActivePage(store);

    return true;
};

const bootSidebar = () => {
    if (! patchSidebarStore()) {
        window.setTimeout(bootSidebar, 50);
        window.setTimeout(bootSidebar, 200);
        window.setTimeout(bootSidebar, 500);
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
    const button = event.target.closest('.fi-sidebar-group-btn, .fi-sidebar-group-collapse-btn');

    if (! button) {
        return;
    }

    scheduleRefresh(window.Alpine?.store('sidebar'));
});
