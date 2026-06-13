<x-error-page
    code="401"
    title="Sign in required"
    message="You need to sign in before you can view this page."
    primary-label="Member sign in"
    :primary-url="route('login')"
    secondary-label="Back to home"
    :secondary-url="url('/')"
    :show-admin-link="true"
/>
