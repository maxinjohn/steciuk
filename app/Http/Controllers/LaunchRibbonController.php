<?php

namespace App\Http\Controllers;

use App\Services\LaunchModeService;
use App\Services\SecurityLogger;
use App\Support\SitePathGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LaunchRibbonController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $path = SitePathGate::normalizePath(parse_url((string) url()->previous(), PHP_URL_PATH) ?? '');
        $gateId = $request->input('gate_id');
        $gate = LaunchModeService::resolveGateForRibbon(is_string($gateId) ? $gateId : null, $path);

        abort_if($gate === null, 404);

        $isAdmin = LaunchModeService::canPreviewSite();

        abort_unless($isAdmin, 403);

        if (LaunchModeService::gateLaunchStyle($gate) === LaunchModeService::STYLE_COUNTDOWN) {
            LaunchModeService::markGateLaunched((string) $gate['id'], disable: true);

            SecurityLogger::info('launch.early_launch', auth()->id(), [
                'gate_id' => $gate['id'],
                'referer' => $request->headers->get('referer'),
            ]);

            return redirect(LaunchModeService::launchUrl($gate));
        }

        abort_unless(LaunchModeService::gateAllowsAdminRibbon($gate), 403);

        LaunchModeService::markGateLaunched((string) $gate['id'], disable: true);

        SecurityLogger::info('launch.ribbon_cut', auth()->id(), [
            'gate_id' => $gate['id'],
            'referer' => $request->headers->get('referer'),
            'public' => false,
        ]);

        return redirect(LaunchModeService::launchUrl($gate));
    }
}
