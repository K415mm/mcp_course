<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaLibrary;
use App\Models\User;
use App\Services\CourseService;

class DashboardController extends Controller
{
    public function __construct(protected CourseService $courseService)
    {
    }

    public function index()
    {
        $items = $this->courseService->getAllItems();
        $modules = count(array_filter($items, fn($i) => $i['type'] === 'module'));
        $workshops = count(array_filter($items, fn($i) => $i['type'] === 'workshop'));

        // Count all lesson files
        $lessonCount = 0;
        foreach ($items as $item) {
            $lessons = $this->courseService->getLessons($item['slug']);
            foreach ($lessons as $data)
                $lessonCount += count($data['lessons']);
        }

        $stats = [
            'users' => User::count(),
            'modules' => $modules,
            'workshops' => $workshops,
            'lessons' => $lessonCount,
            'media' => MediaLibrary::count(),
            'admins' => User::where('role', 'admin')->count(),
        ];

        return view('admin.dashboard', compact('stats', 'items'));
    }
}
