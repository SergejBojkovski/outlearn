<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reports & Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Students Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 bg-opacity-75 text-white mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm font-medium uppercase leading-4">Students</p>
                                <p class="text-3xl font-bold text-gray-900">{{ $studentCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professors Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-75 text-white mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm font-medium uppercase leading-4">Professors</p>
                                <p class="text-3xl font-bold text-gray-900">{{ $professorCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed Lessons Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 bg-opacity-75 text-white mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm font-medium uppercase leading-4">Completed Lessons</p>
                                <p class="text-3xl font-bold text-gray-900">{{ $completedLessonsCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- User Growth Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">User Growth</h3>
                        <div class="h-64 bg-gray-100 flex items-center justify-center">
                            <p class="text-gray-500">User growth chart will be displayed here</p>
                        </div>
                    </div>
                </div>

                <!-- Course Popularity Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Course Popularity</h3>
                        <div class="h-64 bg-gray-100 flex items-center justify-center">
                            <p class="text-gray-500">Course popularity chart will be displayed here</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Reports Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Reports</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="#" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <h4 class="font-medium text-indigo-600">User Engagement Report</h4>
                            <p class="text-sm text-gray-500 mt-1">Analyze user activity and engagement metrics</p>
                        </a>
                        
                        <a href="#" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <h4 class="font-medium text-indigo-600">Course Completion Report</h4>
                            <p class="text-sm text-gray-500 mt-1">View course completion rates and statistics</p>
                        </a>
                        
                        <a href="#" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <h4 class="font-medium text-indigo-600">Achievement Distribution</h4>
                            <p class="text-sm text-gray-500 mt-1">Analyze achievement distribution among users</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout> 