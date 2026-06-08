<?php

namespace App\Policies;

use App\Policies\Concerns\AllowsContentEditors;

class GalleryPhotoPolicy
{
    use AllowsContentEditors;
}
