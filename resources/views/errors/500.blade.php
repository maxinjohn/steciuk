<x-error-page
    code="500"
    :title="'Something went wrong'"
    message="We could not complete your request right now. Please try again in a moment — our team has been notified."
    verse="Cast all your anxiety on him because he cares for you."
    verse-ref="1 Peter 5:7"
    secondary-label="Try again"
    :secondary-url="url()->current()"
/>
