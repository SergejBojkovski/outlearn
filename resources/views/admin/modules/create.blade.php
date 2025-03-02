<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Module') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-4 flex items-center">
                        <a href="{{ route('admin.courses.show', $course->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Course
                        </a>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">Creating Module for Course: {{ $course->title }}</h3>
                    </div>

                    <form action="{{ route('admin.modules.store', $course->id) }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 gap-6 mb-6">
                            <!-- Module Name -->
                            <div>
                                <x-label for="name" :value="__('Module Name')" />
                                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Order -->
                            <div>
                                <x-label for="order" :value="__('Order')" />
                                <x-input id="order" class="block mt-1 w-full" type="number" name="order" :value="old('order', $nextOrder)" min="1" required />
                                <p class="text-sm text-gray-500 mt-1">Position in the course (1 is first)</p>
                                @error('order')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-button class="ml-3">
                                {{ __('Create Module') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout> 