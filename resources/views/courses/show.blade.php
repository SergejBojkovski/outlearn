@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">{{ $course->title }}</h1>
                        <p class="text-sm text-gray-500">
                            Category: <a href="{{ route('categories.show', $course->category->id) }}" class="text-blue-500 hover:underline">{{ $course->category->name }}</a>
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('courses.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Back</a>
                        @auth
                            <a href="{{ route('courses.edit', $course->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">Edit</a>
                            <form action="{{ route('courses.destroy', $course->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" onclick="return confirm('Are you sure you want to delete this course?')">
                                    Delete
                                </button>
                            </form>
                        @endauth
                    </div>
                </div>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-2">Description</h2>
                    <div class="bg-gray-50 p-4 rounded text-gray-700">
                        {!! nl2br(e($course->description)) !!}
                    </div>
                </div>

                @if ($course->professor)
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-2">Instructor</h2>
                        <div class="bg-gray-50 p-4 rounded">
                            <a href="{{ route('users.show', $course->professor->id) }}" class="text-blue-500 hover:underline">
                                {{ $course->professor->name }}
                            </a>
                        </div>
                    </div>
                @endif

                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Modules</h2>
                        @auth
                            <a href="{{ route('modules.create', ['course_id' => $course->id]) }}" class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                                Add Module
                            </a>
                        @endauth
                    </div>

                    @if ($course->modules->count() > 0)
                        <div class="space-y-4">
                            @foreach ($course->modules as $module)
                                <div class="border rounded shadow-sm">
                                    <div class="bg-gray-50 p-4 flex justify-between items-center">
                                        <h3 class="text-lg font-medium">{{ $module->title }}</h3>
                                        <div class="flex gap-2">
                                            <a href="{{ route('modules.show', $module->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            @auth
                                                <a href="{{ route('modules.edit', $module->id) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                            @endauth
                                        </div>
                                    </div>
                                    
                                    @if ($module->lessons->count() > 0)
                                        <div class="p-4">
                                            <div class="text-sm font-medium text-gray-700 mb-2">Lessons:</div>
                                            <ul class="ml-4 space-y-2">
                                                @foreach ($module->lessons as $lesson)
                                                    <li class="flex justify-between items-center">
                                                        <a href="{{ route('lessons.show', $lesson->id) }}" class="text-blue-500 hover:underline">
                                                            {{ $lesson->title }}
                                                        </a>
                                                        @auth
                                                            <a href="{{ route('lessons.edit', $lesson->id) }}" class="text-xs text-gray-500 hover:text-gray-700">
                                                                Edit
                                                            </a>
                                                        @endauth
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <div class="p-4 text-gray-500 text-sm">
                                            No lessons in this module yet.
                                            @auth
                                                <a href="{{ route('lessons.create', ['module_id' => $module->id]) }}" class="text-green-500 hover:underline">
                                                    Add a lesson
                                                </a>
                                            @endauth
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 p-4 rounded text-gray-500">
                            No modules available for this course yet.
                        </div>
                    @endif
                </div>

                @auth
                    <div class="mt-8 pt-6 border-t">
                        <h2 class="text-xl font-semibold mb-4">Enrollment</h2>
                        
                        @if (auth()->user()->courses->contains($course->id))
                            <form action="{{ route('courses.unenroll', $course->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                    Unenroll from Course
                                </button>
                            </form>
                        @else
                            <form action="{{ route('courses.enroll', $course->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                    Enroll in Course
                                </button>
                            </form>
                        @endif
                    </div>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection 