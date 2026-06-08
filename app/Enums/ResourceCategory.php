<?php

namespace App\Enums;

enum ResourceCategory: string
{
    case Liturgy = 'liturgy';
    case Lectionary = 'lectionary';
    case Forms = 'forms';
    case Notices = 'notices';
    case Reports = 'reports';
    case Safeguarding = 'safeguarding';
    case Newsletters = 'newsletters';
}
