<?php

namespace App\Policies;

use App\Policies\Concerns\AllowsContentEditors;

class FormSubmissionPolicy
{
    use AllowsContentEditors;
}
