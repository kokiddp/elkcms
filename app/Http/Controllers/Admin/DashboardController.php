<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\CMS\ContentModels\TestPost;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        $stats = [
            'total_content' => $this->getTotalContent(),
            'total_users' => User::count(),
            'total_translations' => Translation::count(),
            'translation_progress' => $this->getTranslationProgress(),
            'recent_content' => $this->getRecentContent(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Get total content count.
     */
    protected function getTotalContent(): int
    {
        return TestPost::count();
    }

    /**
     * Get translation progress by locale.
     */
    protected function getTranslationProgress(): array
    {
        $supportedLanguages = config('languages.supported', []);
        $locales = array_keys($supportedLanguages);
        $progress = [];

        foreach ($locales as $locale) {
            $progress[$locale] = Translation::where('locale', $locale)->count();
        }

        return $progress;
    }

    /**
     * Get recent content items.
     */
    protected function getRecentContent(): \Illuminate\Support\Collection
    {
        return TestPost::latest()->take(5)->get();
    }
}
