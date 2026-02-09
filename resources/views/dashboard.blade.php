<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('School Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Message -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900">Welcome, {{ Auth::user()->name }}!</h3>
                    <p class="mt-1 text-sm text-gray-600">Here's a summary of what's happening at your school.</p>
                </div>
            </div>

            <!-- Key Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Stat Card 1: Total Students -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 truncate">Total Students</p>
                                <p class="text-lg font-semibold text-gray-900">1,250</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Stat Card 2: Total Teachers -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                               <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 truncate">Total Teachers</p>
                                <p class="text-lg font-semibold text-gray-900">85</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Stat Card 3: Classes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v11.494m-9-5.747h18" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 truncate">Classes</p>
                                <p class="text-lg font-semibold text-gray-900">45</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Stat Card 4: Events -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 truncate">Upcoming Events</p>
                                <p class="text-lg font-semibold text-gray-900">3</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Announcements -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Announcements</h3>
                        <ul class="divide-y divide-gray-200">
                            <li class="py-4 flex">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Parent-Teacher Meeting</p>
                                    <p class="text-sm text-gray-500">Scheduled for next Friday. Please sign up for a slot.</p>
                                </div>
                            </li>
                            <li class="py-4 flex">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Science Fair Submissions</p>
                                    <p class="text-sm text-gray-500">The deadline for science fair project submissions is November 30th.</p>
                                </div>
                            </li>
                            <li class="py-4 flex">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Holiday Break</p>
                                    <p class="text-sm text-gray-500">The school will be closed for winter break from December 22nd to January 5th.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Upcoming Events</h3>
                        <ul class="divide-y divide-gray-200">
                            <li class="py-3">
                                <p class="text-sm font-medium text-gray-900">Basketball Game vs. Northwood High</p>
                                <p class="text-sm text-gray-500">November 25, 2025 - 7:00 PM</p>
                            </li>
                            <li class="py-3">
                                <p class="text-sm font-medium text-gray-900">School Play: "A Midsummer Night's Dream"</p>
                                <p class="text-sm text-gray-500">December 5-7, 2025 - 8:00 PM</p>
                            </li>
                            <li class="py-3">
                                <p class="text-sm font-medium text-gray-900">Winter Concert</p>
                                <p class="text-sm text-gray-500">December 15, 2025 - 6:00 PM</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
