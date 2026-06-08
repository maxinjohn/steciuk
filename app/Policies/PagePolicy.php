<?php

namespace App\Policies;

use App\Policies\Concerns\AllowsContentEditors;

class PagePolicy
{
    use AllowsContentEditors;
}
