<?php

namespace App\Policies;

use App\Policies\Concerns\AllowsContentEditors;

class ContentBlockPolicy
{
    use AllowsContentEditors;
}
