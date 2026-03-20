<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassCourseEnrollment;
use App\Models\CourseEnrollment;
use App\Models\StudentClass;
use App\Models\User;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminCourseController extends Controller
{
    public function __construct(protected CourseService $courseService)
    {
    }

    public function index()
    {
        // Get all courses defined on the filesystem
        $courses = collect($this->courseService->getCourses())->map(function ($course) {
            // Count enrollments (direct)
            $course['direct_enrollments'] = CourseEnrollment::where('course_slug', $course['slug'])->count();
            // Count class enrollments
            $course['class_enrollments'] = ClassCourseEnrollment::where('course_slug', $course['slug'])->count();
            return $course;
        });

        $classes = StudentClass::where('status', 'active')->get();
        $users = User::orderBy('name')->get();

        return view('admin.courses.index', compact('courses', 'classes', 'users'));
    }

    public function assignToClass(Request $request)
    {
        $request->validate([
            'course_slug' => 'required|string',
            'class_id'    => 'required|exists:classes,id',
        ]);

        $exists = ClassCourseEnrollment::where('class_id', $request->class_id)
            ->where('course_slug', $request->course_slug)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('info', 'Class is already enrolled in this course.');
        }

        ClassCourseEnrollment::create([
            'class_id'    => $request->class_id,
            'course_slug' => $request->course_slug,
            'enrolled_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Course assigned to class successfully.');
    }

    public function unassignFromClass(Request $request)
    {
        $request->validate([
            'course_slug' => 'required|string',
            'class_id'    => 'required|exists:classes,id',
        ]);

        ClassCourseEnrollment::where('class_id', $request->class_id)
            ->where('course_slug', $request->course_slug)
            ->delete();

        return redirect()->back()->with('success', 'Course assignment removed from class.');
    }

    public function assignToStudent(Request $request)
    {
        $request->validate([
            'course_slug' => 'required|string',
            'user_id'     => 'required|exists:users,id',
        ]);

        $exists = CourseEnrollment::where('user_id', $request->user_id)
            ->where('course_slug', $request->course_slug)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('info', 'Student is already directly enrolled in this course.');
        }

        CourseEnrollment::create([
            'user_id'     => $request->user_id,
            'course_slug' => $request->course_slug,
            'enrolled_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Course assigned to student successfully.');
    }
}
