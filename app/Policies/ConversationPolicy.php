<?php

namespace App\Policies;

use App\Policies\Concerns\AllowsContentEditors;

class ConversationPolicy
{
    use AllowsContentEditors;
}
