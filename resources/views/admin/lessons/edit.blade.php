<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Lesson') }}
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

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">Editing Lesson for Module: {{ $module->name }}</h3>
                        <p class="text-sm text-gray-600">Course: {{ $course->title }}</p>
                    </div>

                    <form action="{{ route('admin.lessons.update', [$course->id, $module->id, $lesson->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 gap-6 mb-6">
                            <!-- Lesson Title -->
                            <div>
                                <x-label for="title" :value="__('Lesson Title')" />
                                <x-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $lesson->title)" required autofocus />
                                @error('title')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Lesson Content -->
                            <div>
                                <x-label for="content" :value="__('Content')" />
                                <textarea id="content" name="content" rows="10" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('content', $lesson->content) }}</textarea>
                                @error('content')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Video URL -->
                            <div>
                                <x-label for="video_url" :value="__('Video URL (Optional)')" />
                                <x-input id="video_url" class="block mt-1 w-full" type="url" name="video_url" :value="old('video_url', $lesson->video_url)" placeholder="https://www.youtube.com/watch?v=..." />
                                <p class="text-sm text-gray-500 mt-1">Enter a YouTube or Vimeo URL</p>
                                @error('video_url')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Order Number -->
                            <div>
                                <x-label for="order_number" :value="__('Order')" />
                                <x-input id="order_number" class="block mt-1 w-full" type="number" name="order_number" :value="old('order_number', $lesson->order_number)" min="1" required />
                                <p class="text-sm text-gray-500 mt-1">Position in the module (1 is first)</p>
                                @error('order_number')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-button class="ml-3">
                                {{ __('Update Lesson') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout> 