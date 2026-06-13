<?php

use App\Http\Controllers\LaunchRibbonController;
use App\Http\Controllers\DonationReportController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\GiveController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\MinistryController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OfflineController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SermonController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceWorkerController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/manifest.webmanifest', ManifestController::class)->name('manifest');
Route::get('/sw.js', ServiceWorkerController::class)->name('sw');
Route::get('/offline', OfflineController::class)->name('offline');

Route::post('/launch/cut-ribbon', LaunchRibbonController::class)
    ->name('launch.cut-ribbon');

Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');

Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');

Route::get('/sermons', [SermonController::class, 'index'])->name('sermons.index');

Route::get('/ministries', [MinistryController::class, 'index'])->name('ministries.index');
Route::get('/ministries/{slug}', [MinistryController::class, 'show'])->name('ministries.show');

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/{slug}', [GalleryController::class, 'show'])->name('gallery.show');

Route::get('/resources', [ResourceController::class, 'index'])->name('resources.index');
Route::get('/service-times', [ServiceController::class, 'index'])->name('services.index');

Route::get('/give', GiveController::class)->name('give');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

Route::middleware('guest')->group(function (): void {
    Route::view('/register', 'auth.register')->name('register');
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::get('/reset-password/{token}', fn (string $token) => view('auth.reset-password', ['token' => $token]))->name('password.reset');
});

Route::view('/registration/pending', 'auth.registration-pending')->name('registration.pending');

Route::middleware(['auth', 'member.approved'])->group(function (): void {
    Route::view('/account', 'auth.account')->name('account');
    Route::get('/account/giving/export', DonationReportController::class)->name('account.giving.export');
    Route::post('/logout', LogoutController::class)->name('logout');
});

Route::get('/.well-known/security.txt', function () {
    $contact = config('security.security_contact', 'admin@steciuk.org');

    return response(
        implode("\n", [
            'Contact: mailto:'.$contact,
            'Preferred-Languages: en',
            'Policy: '.url('/privacy'),
        ])."\n",
        200,
        ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'public, max-age=86400'],
    );
})->name('security.txt');

Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('pages.show');
