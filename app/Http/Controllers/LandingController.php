<?php

namespace App\Http\Controllers;

use App\Models\AppRelease;
use App\Models\LandingSetting;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LandingController extends Controller
{
    /**
     * Display the public landing page.
     */
    public function index()
    {
        $latestRelease = AppRelease::published()->first();
        $allReleases = AppRelease::published()->get();

        $hero = LandingSetting::getGroup('hero');
        $features = LandingSetting::getGroup('features');
        $screenshots = LandingSetting::getGroup('screenshots');
        $about = LandingSetting::getGroup('about');
        $footer = LandingSetting::getGroup('footer');

        return view('landing', compact(
            'latestRelease',
            'allReleases',
            'hero',
            'features',
            'screenshots',
            'about',
            'footer',
        ));
    }

    /**
     * Download the latest published APK.
     */
    public function downloadLatest()
    {
        $release = AppRelease::published()->first();

        if (!$release) {
            abort(404, 'No release available for download.');
        }

        return $this->serveApk($release);
    }

    /**
     * Download a specific release APK.
     */
    public function download(AppRelease $release)
    {
        if (!$release->is_published || !$release->published_at || $release->published_at->isFuture()) {
            abort(404, 'This release is not available.');
        }

        return $this->serveApk($release);
    }

    /**
     * Serve the APK file with proper headers and throttled download counting.
     */
    private function serveApk(AppRelease $release): BinaryFileResponse
    {
        // Throttle download counter: only increment once per 30 seconds per session
        $throttleKey = "download_counted_{$release->id}";
        if (!session($throttleKey)) {
            $release->incrementDownloadCount();
            session([$throttleKey => true]);
        }

        $filePath = Storage::disk('public')->path($release->file_path);
        $fileName = "HRIS-ESS-v{$release->version}.apk";

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
}
