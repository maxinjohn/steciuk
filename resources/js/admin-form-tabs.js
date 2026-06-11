document.addEventListener('DOMContentLoaded', () => {
    const scrollActiveAdminTab = () => {
        document.querySelectorAll('.admin-form-tabs > .fi-tabs').forEach((tabList) => {
            const active = tabList.querySelector('.fi-tabs-item-active, .fi-tabs-item[aria-selected="true"]');

            if (! active) {
                return;
            }

            active.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        });
    };

    scrollActiveAdminTab();

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        if (target.closest('.admin-form-tabs .fi-tabs-item')) {
            window.requestAnimationFrame(scrollActiveAdminTab);
        }
    });

    window.addEventListener('resize', () => {
        window.requestAnimationFrame(scrollActiveAdminTab);
    });
});
