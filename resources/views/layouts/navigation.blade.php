<nav x-data="{ open: false }" class="flex items-center justify-center p-4 bg-white w-full">
    <div class="w-[70%] flex items-center justify-between">
        <div class="flex items-center">
            <a href="{{ route('home') }}" class="text-green-700 font-bold text-xl flex items-center">
                <img 
                    src="{{ asset('images/Vector (Stroke).png') }}" 
                    alt="ourlearn logo" 
                    class="mr-2 h-6 w-auto" 
                />
                ourlearn
            </a>
        </div>
        
        <div class="hidden md:flex space-x-6">
            <a href="{{ route('home') }}" class="text-gray-700 hover:text-gray-900">Home</a>
            <a href="{{ route('courses') }}" class="text-gray-700 hover:text-gray-900">Courses Board</a>
            <a href="{{ route('profile') }}" class="text-gray-700 hover:text-gray-900">My Profile</a>
        </div>
        
        <div class="flex items-center space-x-4">
            <button class="text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
            
            @guest
                <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900">Log in</a>
                <a href="{{ route('register') }}" class="bg-green-700 text-white px-4 py-2 rounded-md hover:bg-green-800">Sign Up</a>
            @else
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center text-gray-700 hover:text-gray-900">
                        {{ Auth::user()->name }}
                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            @endguest
        </div>
    </div>
</nav>
