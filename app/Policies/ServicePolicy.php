<?php

namespace App\Policies;

use App\Policies\Concerns\AllowsContentEditors;

class ServicePolicy
{
    use AllowsContentEditors;
}
