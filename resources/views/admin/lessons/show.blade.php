<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lesson Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-4 flex items-center">
                        <a href="{{ route('admin.modules.show', [$course->id, $module->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Module
                        </a>
                    </div>

                    <!-- Lesson Info -->
                    <div class="mb-6 border-b pb-4">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-xl font-bold text-gray-800">{{ $lesson->title }}</h3>
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.lessons.edit', [$course->id, $module->id, $lesson->id]) }}" class="inline-flex items-center px-3 py-1 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </a>
                                <form action="{{ route('admin.lessons.delete', [$course->id, $module->id, $lesson->id]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this lesson?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 mb-2">
                            <span class="font-semibold">Course:</span> {{ $course->title }}
                        </div>
                        <div class="text-sm text-gray-600 mb-2">
                            <span class="font-semibold">Module:</span> {{ $module->name }}
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="font-semibold">Order:</span> {{ $lesson->order_number }}
                        </div>
                    </div>
                    
                    <!-- Lesson Content -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Content</h4>
                        <div class="prose max-w-none">
                            {!! nl2br(e($lesson->content)) !!}
                        </div>
                    </div>
                    
                    <!-- Video Section -->
                    @if($lesson->video_url)
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Video</h4>
                            <div class="aspect-w-16 aspect-h-9">
                                <div class="bg-gray-100 p-4 rounded-lg">
                                    <p class="text-gray-700">Video URL: <a href="{{ $lesson->video_url }}" target="_blank" class="text-blue-600 hover:underline">{{ $lesson->video_url }}</a></p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Metadata -->
                    <div class="mt-8 pt-4 border-t">
                        <div class="flex justify-between text-sm text-gray-500">
                            <div>Created: {{ $lesson->created_at->format('M d, Y H:i') }}</div>
                            <div>Last Updated: {{ $lesson->updated_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout> 