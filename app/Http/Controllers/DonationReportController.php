<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DonationReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DonationReportController extends Controller
{
    public function __invoke(Request $request, DonationReportService $reportService): Response
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'scope' => 'required|in:personal,household',
            'from' => 'nullable|date|before_or_equal:today',
            'to' => 'nullable|date|before_or_equal:today',
            'month' => 'nullable|date_format:Y-m',
        ]);

        $validated['include_all_statuses'] = $request->boolean('include_all_statuses');

        return $reportService->memberPdfResponse($user, $validated);
    }
}
