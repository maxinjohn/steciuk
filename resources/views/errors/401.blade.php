<x-error-page
    code="401"
    title="Sign in required"
    message="You need to sign in before you can view this page."
    verse="Draw near to God, and he will draw near to you."
    verse-ref="James 4:8"
    primary-label="Parish admin sign in"
    :primary-url="\App\Support\AdminPanelConfig::url('login')"
    secondary-label="Public site"
    :secondary-url="url('/')"
    :show-admin-link="false"
/>
