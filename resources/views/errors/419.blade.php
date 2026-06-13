<x-error-page
    code="419"
    title="Session expired"
    message="Your session has expired for your security. Refresh the page and try again."
    primary-label="Refresh page"
    :primary-url="url()->current()"
    secondary-label="Back to home"
    :secondary-url="url('/')"
/>
