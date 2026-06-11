<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DonationService;
use App\Support\GivingPageConfig;
use App\Services\PageContext;
use App\Enums\DonationMethod;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GiveController extends Controller
{
    public function __invoke(): View
    {
        /** @var User|null $user */
        $user = Auth::user();
        $summary = null;

        if ($user instanceof User && $user->canSignInToMemberPortal()) {
            $summary = app(DonationService::class)->accountSummary($user);
        }

        return PageContext::view('give.index', 'give', [
            'heading' => GivingPageConfig::pageHeading(),
            'intro' => GivingPageConfig::pageIntro(),
            'anonymousIntro' => GivingPageConfig::anonymousIntro(),
            'memberIntro' => GivingPageConfig::memberIntro(),
            'bankDetails' => GivingPageConfig::bankDetails(),
            'hasBankDetails' => GivingPageConfig::hasBankDetails(),
            'paymentMethods' => DonationMethod::options(),
            'memberSummary' => $summary,
            'canReportGiving' => $user instanceof User && $user->canSignInToMemberPortal(),
        ]);
    }
}
