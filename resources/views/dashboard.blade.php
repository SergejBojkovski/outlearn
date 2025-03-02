<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- User Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-indigo-100 text-indigo-500 mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Enrolled Courses</div>
                                <div class="text-3xl font-bold">{{ Auth::user()->courses->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Completed Lessons</div>
                                <div class="text-3xl font-bold">{{ Auth::user()->completedLessons->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Achievements</div>
                                <div class="text-3xl font-bold">{{ Auth::user()->achievements->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Courses Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">My Courses</h3>
                        <a href="{{ route('courses.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300">
                            Browse All Courses
                        </a>
                    </div>
                    
                    @if(Auth::user()->courses->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach(Auth::user()->courses as $course)
                                <div class="bg-white rounded-lg shadow overflow-hidden">
                                    @if($course->image_url)
                                        <img class="w-full h-48 object-cover" src="{{ $course->image_url }}" alt="{{ $course->title }}">
                                    @else
                                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="p-6">
                                        <div class="text-xs font-medium text-indigo-600 mb-1">
                                            {{ $course->category ? $course->category->name : 'Uncategorized' }}
                                        </div>
                                        <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $course->title }}</h4>
                                        <p class="text-gray-500 text-sm mb-4">{{ Str::limit($course->description, 100) }}</p>
                                        
                                        <!-- Progress Bar -->
                                        @php
                                            $totalLessons = 0;
                                            $completedLessons = 0;
                                            
                                            foreach($course->modules as $module) {
                                                $totalLessons += $module->lessons->count();
                                                
                                                foreach($module->lessons as $lesson) {
                                                    if(Auth::user()->completedLessons->contains($lesson->id)) {
                                                        $completedLessons++;
                                                    }
                                                }
                                            }
                                            
                                            $progressPercentage = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;
                                        @endphp
                                        
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $progressPercentage }}%"></div>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                                            <span>Progress: {{ round($progressPercentage) }}%</span>
                                            <span>{{ $completedLessons }}/{{ $totalLessons }} lessons</span>
                                        </div>
                                        
                                        <div class="mt-4">
                                            <a href="{{ route('courses.show', $course->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300">
                                                Continue Learning
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">No courses yet</h4>
                            <p class="text-gray-500 mb-4">Explore our course catalog and enroll in courses to start learning!</p>
                            <a href="{{ route('courses.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300">
                                Browse Courses
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Achievements -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Achievements</h3>
                    
                    @if(Auth::user()->achievements->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach(Auth::user()->achievements->sortByDesc(function($achievement) {
                                return $achievement->pivot->awarded_at;
                            })->take(4) as $achievement)
                                <div class="bg-white border rounded-lg p-4 flex flex-col items-center text-center">
                                    @if($achievement->image_url)
                                        <img src="{{ $achievement->image_url }}" alt="{{ $achievement->name }}" class="w-16 h-16 mb-2">
                                    @else
                                        <div class="w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center mb-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                            </svg>
                                        </div>
                                    @endif
                                    <h4 class="font-semibold text-gray-900">{{ $achievement->name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ $achievement->description }}</p>
                                    <div class="text-xs text-gray-400 mt-2">
                                        Awarded {{ \Carbon\Carbon::parse($achievement->pivot->awarded_at)->diffForHumans() }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">No achievements yet</h4>
                            <p class="text-gray-500">Complete course milestones to earn achievements!</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Profile Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row">
                        <!-- Profile Summary -->
                        <div class="flex-1 md:border-r md:pr-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Information</h3>
                            
                            <div class="flex items-center mb-6">
                                <div class="mr-4">
                                    @if(Auth::user()->profile_picture)
                                        <img src="{{ Auth::user()->profile_picture }}" alt="{{ Auth::user()->name }}" class="h-20 w-20 rounded-full">
                                    @else
                                        <div class="h-20 w-20 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="text-xl font-semibold">{{ Auth::user()->name }}</h4>
                                    <p class="text-gray-500">{{ Auth::user()->email }}</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mt-1">
                                        {{ Auth::user()->role ? Auth::user()->role->name : 'User' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h5 class="text-sm font-medium text-gray-500 mb-1">Joined</h5>
                                    <p class="text-gray-900">{{ Auth::user()->created_at->format('M d, Y') }}</p>
                                </div>
                                
                                @if(Auth::user()->role && Auth::user()->role->name === 'student' && Auth::user()->studentData)
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-500 mb-1">Academic Level</h5>
                                        <p class="text-gray-900">{{ Auth::user()->studentData->academic_level ?? 'Not specified' }}</p>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-500 mb-1">Learning Goals</h5>
                                        <p class="text-gray-900">{{ Auth::user()->studentData->learning_goals ?? 'Not specified' }}</p>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-500 mb-1">Interests</h5>
                                        <p class="text-gray-900">{{ Auth::user()->studentData->interests ?? 'Not specified' }}</p>
                                    </div>
                                @endif
                                
                                @if(Auth::user()->role && Auth::user()->role->name === 'professor' && Auth::user()->professorData)
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-500 mb-1">Areas of Expertise</h5>
                                        <p class="text-gray-900">{{ Auth::user()->professorData->areas_of_expertise ?? 'Not specified' }}</p>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-500 mb-1">Qualifications</h5>
                                        <p class="text-gray-900">{{ Auth::user()->professorData->qualifications ?? 'Not specified' }}</p>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-500 mb-1">Office Hours</h5>
                                        <p class="text-gray-900">{{ Auth::user()->professorData->office_hours ?? 'Not specified' }}</p>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="mt-6">
                                <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300">
                                    Edit Profile
                                </a>
                            </div>
                        </div>
                        
                        <!-- Bio Section -->
                        <div class="flex-1 mt-6 md:mt-0 md:pl-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">About Me</h3>
                            
                            @if((Auth::user()->role && Auth::user()->role->name === 'student' && Auth::user()->studentData && Auth::user()->studentData->bio) || 
                               (Auth::user()->role && Auth::user()->role->name === 'professor' && Auth::user()->professorData && Auth::user()->professorData->bio))
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-gray-700">
                                        @if(Auth::user()->role && Auth::user()->role->name === 'student' && Auth::user()->studentData)
                                            {{ Auth::user()->studentData->bio }}
                                        @elseif(Auth::user()->role && Auth::user()->role->name === 'professor' && Auth::user()->professorData)
                                            {{ Auth::user()->professorData->bio }}
                                        @endif
                                    </p>
                                </div>
                            @else
                                <div class="bg-gray-50 rounded-lg p-6 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Your bio is empty</h4>
                                    <p class="text-gray-500 mb-4">Add a bio to tell others about yourself!</p>
                                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300">
                                        Add Bio
                                    </a>
                                </div>
                            @endif
                            
                            @if(Auth::user()->role && Auth::user()->role->name === 'professor')
                                <div class="mt-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Teaching Philosophy</h3>
                                    @if(Auth::user()->professorData && Auth::user()->professorData->teaching_philosophy)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <p class="text-gray-700">{{ Auth::user()->professorData->teaching_philosophy }}</p>
                                        </div>
                                    @else
                                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                                            <p class="text-gray-500">No teaching philosophy added yet.</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
