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
                $modules = $this->courseService->getModules($c['slug']);
                $c['modules_count'] = count(array_filter($modules, fn($m) => $m['type'] === 'module'));
                $c['workshops_count'] = count(array_filter($modules, fn($m) => $m['type'] === 'workshop'));
                $c['hours_count'] = ($c['modules_count'] * 2) + ($c['workshops_count'] * 3);

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

        // Compute access for each component
        foreach ($modules as &$mod) {
            $isEnrolled = $user->canAccessModule($mod['number'], $mod['type'], $courseSlug);
            
            if ($mod['type'] === 'workshop') {
                $hasCapability = $user->canAccessWorkshop($mod['slug']);
            } else {
                $hasCapability = $user->hasModuleCapability($courseSlug, $mod['slug']);
            }

            $mod['locked'] = !($isEnrolled && $hasCapability);
        }
        unset($mod);

        return view('courses.show', compact('course', 'modules'));
    }
}
