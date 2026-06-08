<?php

namespace App\Policies;

use App\Policies\Concerns\AllowsContentEditors;

class EventPolicy
{
    use AllowsContentEditors;
}
