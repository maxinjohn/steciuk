<x-error-page
    code="500"
    :title="'Something went wrong'"
    message="We could not complete your request right now. Please try again in a moment — our team has been notified."
    secondary-label="Try again"
    :secondary-url="url()->current()"
/>
