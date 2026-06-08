<?php

namespace App\Policies;

use App\Policies\Concerns\AllowsContentEditors;

class MenuItemPolicy
{
    use AllowsContentEditors;
}
