<x-error-page
    code="503"
    title="Temporarily unavailable"
    :message="\App\Models\Setting::get('maintenance_mode_message', 'We are performing maintenance. Please check back shortly.')"
    verse="Wait for the Lord; be strong and take heart and wait for the Lord."
    verse-ref="Psalm 27:14"
/>
