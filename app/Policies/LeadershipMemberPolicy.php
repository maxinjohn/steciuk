<?php

namespace App\Policies;

use App\Policies\Concerns\AllowsContentEditors;

class LeadershipMemberPolicy
{
    use AllowsContentEditors;
}
