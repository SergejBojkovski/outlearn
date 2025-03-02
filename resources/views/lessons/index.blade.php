<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lessons') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    @if(isset($module))
                        Lessons for: {{ $module->title }}
                    @elseif(isset($course))
                        Lessons for: {{ $course->title }}
                    @else
                        All Lessons
                    @endif
                </h3>
                @auth
                    <a href="{{ route('lessons.create', isset($module) ? ['module_id' => $module->id] : (isset($course) ? ['course_id' => $course->id] : [])) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Lesson
                    </a>
                @endauth
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('lessons.index') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <!-- Search -->
                            <div class="col-span-1 md:col-span-2">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <div class="relative">
                                    <input type="text" name="search" id="search" value="{{ request('search') }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Enter lesson title...">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Course Filter -->
                            <div>
                                <label for="course_id" class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                                <select id="course_id" name="course_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">All Courses</option>
                                    @foreach($courses as $course_item)
                                        <option value="{{ $course_item->id }}" {{ (request('course_id') == $course_item->id || (isset($course) && $course->id == $course_item->id)) ? 'selected' : '' }}>
                                            {{ $course_item->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Module Filter -->
                            <div>
                                <label for="module_id" class="block text-sm font-medium text-gray-700 mb-1">Module</label>
                                <select id="module_id" name="module_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">All Modules</option>
                                    @foreach($modules as $module_item)
                                        <option value="{{ $module_item->id }}" {{ (request('module_id') == $module_item->id || (isset($module) && $module->id == $module_item->id)) ? 'selected' : '' }}>
                                            {{ $module_item->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <!-- Sort By -->
                            <div>
                                <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                <select id="sort" name="sort" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="order_asc" {{ request('sort') == 'order_asc' ? 'selected' : '' }}>Order (Low to High)</option>
                                    <option value="order_desc" {{ request('sort') == 'order_desc' ? 'selected' : '' }}>Order (High to Low)</option>
                                    <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                                    <option value="title_desc" {{ request('sort') == 'title_desc' ? 'selected' : '' }}>Title (Z-A)</option>
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                    <option value="duration_shortest" {{ request('sort') == 'duration_shortest' ? 'selected' : '' }}>Duration (Shortest First)</option>
                                    <option value="duration_longest" {{ request('sort') == 'duration_longest' ? 'selected' : '' }}>Duration (Longest First)</option>
                                </select>
                            </div>

                            <div class="flex items-center justify-end space-x-3">
                                <button type="reset" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Reset
                                </button>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($lessons->count() > 0)
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <ul class="divide-y divide-gray-200">
                        @foreach($lessons as $lesson)
                            <li>
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 mr-3">
                                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                    @if($lesson->type == 'video')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    @elseif($lesson->type == 'text')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                    @elseif($lesson->type == 'quiz')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                        </svg>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <a href="{{ route('lessons.show', $lesson->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                    {{ $lesson->title }}
                                                </a>
                                                <div class="mt-1 flex items-center">
                                                    <span class="text-xs text-gray-500">
                                                        Module: <a href="{{ route('modules.show', $lesson->module->id) }}" class="text-gray-700 hover:text-indigo-500">{{ $lesson->module->title }}</a>
                                                    </span>
                                                    <span class="mx-2 text-gray-500">&middot;</span>
                                                    <span class="text-xs text-gray-500">
                                                        Course: <a href="{{ route('courses.show', $lesson->module->course->id) }}" class="text-gray-700 hover:text-indigo-500">{{ $lesson->module->course->title }}</a>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="flex flex-col items-end mr-6">
                                                <span class="text-sm text-gray-900">Order: {{ $lesson->order }}</span>
                                                @if($lesson->duration)
                                                    <div class="mt-1 flex items-center text-xs text-gray-500">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {{ $lesson->duration }} min
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('lessons.show', $lesson->id) }}" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    View
                                                </a>
                                                @auth
                                                    <a href="{{ route('lessons.edit', $lesson->id) }}" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Edit
                                                    </a>
                                                    <form action="{{ route('lessons.destroy', $lesson->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" onclick="return confirm('Are you sure you want to delete this lesson?')" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endauth
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mt-6">
                    {{ $lessons->withQueryString()->links() }}
                </div>
            @else
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-16 text-center sm:px-6">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No lessons found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if(isset($module))
                                No lessons have been created for this module yet.
                            @elseif(isset($course))
                                No lessons have been created for this course yet.
                            @else
                                No lessons match your search criteria.
                            @endif
                        </p>
                        <div class="mt-6">
                            @auth
                                <a href="{{ route('lessons.create', isset($module) ? ['module_id' => $module->id] : (isset($course) ? ['course_id' => $course->id] : [])) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Create Lesson
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 