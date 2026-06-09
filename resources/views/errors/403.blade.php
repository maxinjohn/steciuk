<x-error-page
    code="403"
    title="Access denied"
    message="You do not have permission to view this page. If you believe this is a mistake, please contact the parish office."
    verse="The Lord gives wisdom; from his mouth come knowledge and understanding."
    verse-ref="Proverbs 2:6"
    primary-label="Back to home"
    :primary-url="url('/')"
    secondary-label="Parish admin"
    :secondary-url="\App\Support\AdminPanelConfig::url('login')"
    :show-admin-link="false"
/>
