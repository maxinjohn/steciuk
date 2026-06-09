<x-error-page
    code="419"
    title="Session expired"
    message="Your session has expired for your security. Refresh the page and try again."
    verse="Be still, and know that I am God."
    verse-ref="Psalm 46:10"
    primary-label="Refresh page"
    :primary-url="url()->current()"
    secondary-label="Back to home"
    :secondary-url="url('/')"
/>
