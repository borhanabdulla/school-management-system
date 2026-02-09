<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Component Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 p-10">
    <div class="max-w-4xl mx-auto space-y-8">
        <section>
            <h2 class="text-2xl font-bold mb-4">Buttons</h2>
            
            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Variants</h3>
                    <div class="flex flex-wrap gap-4">
                        <x-button variant="primary">Primary</x-button>
                        <x-button variant="secondary">Secondary</x-button>
                        <x-button variant="success">Success</x-button>
                        <x-button variant="warning">Warning</x-button>
                        <x-button variant="danger">Danger</x-button>
                        <x-button variant="ghost">Ghost</x-button>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Sizes</h3>
                    <div class="flex flex-wrap items-center gap-4">
                        <x-button size="sm">Small</x-button>
                        <x-button size="md">Medium</x-button>
                        <x-button size="lg">Large</x-button>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-bold mb-4">Inputs</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Standard Input -->
                <x-input name="username" label="Username" placeholder="Enter your username" />

                <!-- Input without Label -->
                <x-input name="search" placeholder="Search..." />

                <!-- Input with Value -->
                <x-input name="email" label="Email Address" type="email" value="john@example.com" />

                <!-- Password Input -->
                <x-input name="password" label="Password" type="password" />
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-bold mb-4">Selects</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-select 
                    name="role" 
                    label="User Role" 
                    placeholder="Select a role"
                    :options="['admin' => 'Administrator', 'teacher' => 'Teacher', 'student' => 'Student']" 
                />
                
                <x-select 
                    name="status" 
                    label="Status" 
                    :options="['Active', 'Inactive', 'Pending']" 
                />
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-bold mb-4">Badges</h2>
            <div class="flex flex-wrap gap-4">
                <x-badge variant="primary">Primary</x-badge>
                <x-badge variant="secondary">Secondary</x-badge>
                <x-badge variant="success">Success</x-badge>
                <x-badge variant="warning">Warning</x-badge>
                <x-badge variant="danger">Danger</x-badge>
                <x-badge variant="neutral">Neutral</x-badge>
            </div>
            <div class="flex flex-wrap gap-4 mt-4">
                <x-badge size="sm" variant="success">Small Success</x-badge>
                <x-badge size="md" variant="success">Medium Success</x-badge>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-bold mb-4">Icons</h2>
            <div class="flex flex-wrap items-center gap-6">
                <div class="flex flex-col items-center gap-2">
                    <x-icon name="home" size="lg" />
                    <span class="text-xs text-slate-500">Home</span>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <x-icon name="user" size="lg" class="text-primary" />
                    <span class="text-xs text-slate-500">User</span>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <x-icon name="bell" size="lg" class="text-warning" />
                    <span class="text-xs text-slate-500">Bell</span>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <x-icon name="settings" size="lg" class="text-slate-600" />
                    <span class="text-xs text-slate-500">Settings</span>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-bold mb-4">Cards</h2>
            <x-card>
                <h3 class="text-lg font-semibold mb-2">Card Title</h3>
                <p class="text-slate-600">This is a simple card component used to contain content.</p>
                <div class="mt-4">
                    <x-button variant="primary" size="sm">Action</x-button>
                </div>
            </x-card>
        </section>

        <section>
            <h2 class="text-2xl font-bold mb-4">Tables</h2>
            <x-card class="!p-0">
                <x-table :headers="['Name', 'Email', 'Role', 'Status', 'Actions']">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">John Doe</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">john@example.com</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">Teacher</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-badge variant="success">Active</x-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            <div class="flex gap-2">
                                <x-button variant="ghost" size="sm">Edit</x-button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">Jane Smith</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">jane@example.com</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">Student</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-badge variant="warning">Pending</x-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            <div class="flex gap-2">
                                <x-button variant="ghost" size="sm">Edit</x-button>
                            </div>
                        </td>
                    </tr>
                </x-table>
            </x-card>
        </section>

        <section>
            <h2 class="text-2xl font-bold mb-4">Alerts</h2>
            <div class="space-y-4">
                <x-alert type="info" title="Information">
                    This is an info alert.
                </x-alert>
                <x-alert type="success" title="Success!">
                    Operation completed successfully.
                </x-alert>
                <x-alert type="warning" title="Warning">
                    Please check your input.
                </x-alert>
                <x-alert type="danger" title="Error" dismissible="true">
                    Something went wrong. (Dismissible)
                </x-alert>
            </div>
        </section>

        <section x-data>
            <h2 class="text-2xl font-bold mb-4">Modals</h2>
            <x-button @click="$dispatch('open-modal', 'test-modal')">Open Modal</x-button>

            <x-modal name="test-modal" title="Test Modal">
                <p class="text-slate-600">
                    This is a modal component. It uses Alpine.js for state management.
                </p>
                
                <x-slot name="footer">
                    <x-button variant="secondary" @click="$dispatch('close-modal', 'test-modal')">
                        Cancel
                    </x-button>
                    <x-button variant="primary" @click="$dispatch('close-modal', 'test-modal')">
                        Confirm
                    </x-button>
                </x-slot>
            </x-modal>
        </section>

        <section>
            <h2 class="text-2xl font-bold mb-4">Sidebar Links</h2>
            <div class="w-64 bg-white p-4 rounded-lg shadow">
                <nav class="space-y-1">
                    <x-sidebar-link href="#" :active="true">
                        <x-icon name="home" class="mr-3 flex-shrink-0 h-6 w-6 text-white" />
                        Dashboard
                    </x-sidebar-link>
                    
                    <x-sidebar-link href="#" :active="false">
                        <x-icon name="user" class="mr-3 flex-shrink-0 h-6 w-6 text-slate-400 group-hover:text-slate-500" />
                        Profile
                    </x-sidebar-link>
                    
                    <x-sidebar-link href="#" :active="false">
                        <x-icon name="settings" class="mr-3 flex-shrink-0 h-6 w-6 text-slate-400 group-hover:text-slate-500" />
                        Settings
                    </x-sidebar-link>
                </nav>
            </div>
        </section>
    </div>
</body>
</html>
