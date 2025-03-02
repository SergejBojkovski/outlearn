<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create New User') }}
            </h2>
            <a href="{{ route('admin.users') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Users
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>

                                <!-- Name -->
                                <div>
                                    <x-input-label for="name" :value="__('Name')" />
                                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <!-- Email -->
                                <div>
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <!-- Password -->
                                <div>
                                    <x-input-label for="password" :value="__('Password')" />
                                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                </div>

                                <!-- Role -->
                                <div>
                                    <x-input-label for="role_id" :value="__('Role')" />
                                    <select id="role_id" name="role_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                {{ ucfirst($role->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Role-specific Information -->
                            <div id="role-specific-fields" class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900">Role-specific Information</h3>
                                
                                <!-- Student Fields (initially hidden) -->
                                <div id="student-fields" class="space-y-4 hidden">
                                    <div>
                                        <x-input-label for="student_number" :value="__('Student Number')" />
                                        <x-text-input id="student_number" class="block mt-1 w-full" type="text" name="student_number" :value="old('student_number')" />
                                        <x-input-error :messages="$errors->get('student_number')" class="mt-2" />
                                    </div>
                                </div>
                                
                                <!-- Professor Fields (initially hidden) -->
                                <div id="professor-fields" class="space-y-4 hidden">
                                    <div>
                                        <x-input-label for="department" :value="__('Department')" />
                                        <x-text-input id="department" class="block mt-1 w-full" type="text" name="department" :value="old('department')" />
                                        <x-input-error :messages="$errors->get('department')" class="mt-2" />
                                    </div>
                                    
                                    <div>
                                        <x-input-label for="specialization" :value="__('Specialization')" />
                                        <x-text-input id="specialization" class="block mt-1 w-full" type="text" name="specialization" :value="old('specialization')" />
                                        <x-input-error :messages="$errors->get('specialization')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <x-primary-button class="ml-3">
                                {{ __('Create User') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role_id');
            const studentFields = document.getElementById('student-fields');
            const professorFields = document.getElementById('professor-fields');
            
            // Function to toggle role-specific fields
            function toggleRoleFields() {
                const selectedOption = roleSelect.options[roleSelect.selectedIndex];
                const roleName = selectedOption.text.toLowerCase();
                
                // Hide all role-specific fields first
                studentFields.classList.add('hidden');
                professorFields.classList.add('hidden');
                
                // Show the relevant fields based on the selected role
                if (roleName === 'student') {
                    studentFields.classList.remove('hidden');
                } else if (roleName === 'professor') {
                    professorFields.classList.remove('hidden');
                }
            }
            
            // Initial toggle based on the selected role (if any)
            toggleRoleFields();
            
            // Add event listener for role select changes
            roleSelect.addEventListener('change', toggleRoleFields);
        });
    </script>
</x-admin-layout> 