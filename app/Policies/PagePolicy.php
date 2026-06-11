<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AllowsContentEditors;

class PagePolicy
{
    use AllowsContentEditors;
}
