<?php

namespace App\Policies;

use App\Policies\Concerns\AllowsContentEditors;

class NewsPolicy
{
    use AllowsContentEditors;
}
