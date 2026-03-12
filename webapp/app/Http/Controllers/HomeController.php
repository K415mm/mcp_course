<?php

namespace App\Http\Controllers;

use App\Services\CourseService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(protected CourseService $courseService)
    {
    }

    public function index()
    {
        $items = $this->courseService->getAllItems();
        $modules = array_filter($items, fn($i) => $i['type'] === 'module');
        $workshops = array_filter($items, fn($i) => $i['type'] === 'workshop');

        return view('home', [
            'title' => config('course.title'),
            'modules' => array_values($modules),
            'workshops' => array_values($workshops),
            'allItems' => $items,
        ]);
    }
}
