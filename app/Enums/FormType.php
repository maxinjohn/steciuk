<?php

namespace App\Enums;

enum FormType: string
{
    case Contact = 'contact';
    case PrayerRequest = 'prayer_request';
    case NewMember = 'new_member';
    case EventEnquiry = 'event_enquiry';
    case Volunteer = 'volunteer';
}
