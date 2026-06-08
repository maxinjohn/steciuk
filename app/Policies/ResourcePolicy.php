<?php

namespace App\Policies;

use App\Policies\Concerns\AllowsContentEditors;

class ResourcePolicy
{
    use AllowsContentEditors;
}
