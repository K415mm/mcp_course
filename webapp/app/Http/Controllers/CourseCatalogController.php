<?php

namespace App\Http\Controllers;

use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseCatalogController extends Controller
{
    public function __construct(protected CourseService $courseService)
    {
    }

    public function index()
    {
        $user = Auth::user();
        $enrolledSlugs = $user->enrolledCourseSlugs();
        
        $enrolledCourses = [];
        foreach ($this->courseService->getCourses() as $c) {
            if (in_array('*', $enrolledSlugs) || in_array($c['slug'], $enrolledSlugs)) {
                $enrolledCourses[] = $c;
            }
        }

        return view('courses.index', compact('enrolledCourses'));
    }

    public function show($courseSlug)
    {
        $user = Auth::user();
        abort_if(!$user->canAccessCourse($courseSlug), 403, 'You are not enrolled in this course.');

        $course = $this->courseService->getCourse($courseSlug);
        abort_if(!$course, 404, 'Course not found.');

        $modules = $this->courseService->getModules($courseSlug);

        // Compute access for each module
        foreach ($modules as &$mod) {
            $mod['locked'] = !$user->canAccessModule($mod['number'], $mod['type'], $courseSlug);
        }
        unset($mod);

        return view('courses.show', compact('course', 'modules'));
    }
}
