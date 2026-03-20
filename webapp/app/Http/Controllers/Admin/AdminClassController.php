<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminClassController extends Controller
{
    public function index()
    {
        $classes = StudentClass::withCount('students')->get();
        return view('admin.classes.index', compact('classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'year'        => 'nullable|string|max:10',
            'status'      => ['required', Rule::in(['active', 'archived'])],
        ]);

        StudentClass::create($data);
        return redirect()->route('admin.classes.index')->with('success', 'Class created successfully.');
    }

    public function update(Request $request, StudentClass $class)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'year'        => 'nullable|string|max:10',
            'status'      => ['required', Rule::in(['active', 'archived'])],
        ]);

        $class->update($data);
        return redirect()->back()->with('success', 'Class updated successfully.');
    }

    public function destroy(StudentClass $class)
    {
        $class->delete();
        return redirect()->route('admin.classes.index')->with('success', 'Class deleted.');
    }

    public function show(StudentClass $class)
    {
        $class->load('students');
        $enrolledCourses = $class->courseSlugs();
        
        // For assigning students, we show users not yet in this class
        $availableUsers = \App\Models\User::where('role', '!=', 'guest')
            ->whereNotIn('id', $class->students->pluck('id'))
            ->orderBy('name')
            ->get();
            
        // Get courses from CourseService
        $courseService = app(\App\Services\CourseService::class);
        $allCourses = $courseService->getCourses();
        
        return view('admin.classes.show', compact('class', 'availableUsers', 'allCourses', 'enrolledCourses'));
    }

    public function addStudent(Request $request, StudentClass $class)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        if (!$class->students()->where('user_id', $request->user_id)->exists()) {
            $class->students()->attach($request->user_id);
            return redirect()->back()->with('success', 'Student added to class.');
        }
        return redirect()->back()->with('error', 'Student already in this class.');
    }

    public function removeStudent(StudentClass $class, \App\Models\User $user)
    {
        $class->students()->detach($user->id);
        return redirect()->back()->with('success', 'Student removed from class.');
    }
}
