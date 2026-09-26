<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f5f0e7] text-slate-800 antialiased scroll-smooth">
    <div class="flex min-h-screen">
        <aside class="w-72 shrink-0 border-r border-[#d9cab3] bg-[#1d3c34] p-6 text-[#f8f3eb]">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight text-white">
                Real <span class="text-[#d9cab3]">Estate</span>
            </a>

            <nav class="mt-8 space-y-2">
                <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">Overview</a>
                <a href="#properties" data-target-section="properties" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Properties</a>
                <a href="#blog-posts" data-target-section="blog-posts" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Blog &amp; News</a>
                <a href="#workers" data-target-section="workers" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Workers</a>
                <a href="#users" data-target-section="users" class="nav-section-link flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Users</a>
                <a href="{{ route('home') }}" class="flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">Home</a>
            </nav>

            <div class="mt-10 rounded-[1.25rem] border border-white/10 bg-white/5 p-4">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#d9cab3]">Signed in</p>
                <p class="mt-3 text-lg font-bold text-white">{{ auth()->user()->name }}</p>
                <p class="text-sm text-[#dfeee4]">Administrator</p>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-10">
                @csrf
                <button type="submit" class="w-full rounded-xl border border-[#d9cab3] bg-transparent px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/5">
                    Logout
                </button>
            </form>
        </aside>

        <main class="flex-1 px-6 py-12">
        <div id="overview" class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between scroll-mt-24">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Control Panel</p>
                <h1 class="mt-2 text-4xl font-black text-[#1d3c34]">Admin Dashboard</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-[#f2e4cb] px-3 py-1 text-sm font-semibold text-[#1d3c34]">{{ auth()->user()->name ?? 'Admin' }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-[#cdbb97] bg-white px-4 py-2 text-sm font-semibold text-[#1d3c34] transition hover:bg-[#f1e4cf]">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="mt-6 rounded-xl border border-[#d7e5d2] bg-[#edf9ee] px-4 py-3 text-sm font-medium text-[#214f3a]">
                {{ session('success') }}
            </div>
        @endif

        <div data-section="overview" class="section-panel">
            <div class="mt-8 grid gap-6 md:grid-cols-4">
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Total Properties</p>
                    <p class="mt-3 text-3xl font-black text-[#1d3c34]">128</p>
                </div>
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#edf3ee] p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Active Listings</p>
                    <p class="mt-3 text-3xl font-black text-[#2d5d4d]">94</p>
                </div>
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#f4efe7] p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Workers</p>
                    <p class="mt-3 text-3xl font-black text-[#2d5d4d]">{{ $workers->count() }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#f9ebd8] p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Pending Reviews</p>
                    <p class="mt-3 text-3xl font-black text-[#b7842d]">11</p>
                </div>
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-2">
                <section class="rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                    <h2 class="text-2xl font-black text-[#1d3c34]">Recent Properties</h2>
                    <ul class="mt-5 space-y-4">
                        <li class="flex items-center justify-between border-b border-[#e7ddca] pb-3">
                            <span class="font-medium text-slate-700">Modern Villa</span>
                            <span class="rounded-full bg-[#dfeee4] px-2.5 py-1 text-xs font-bold text-[#1d3c34]">Approved</span>
                        </li>
                        <li class="flex items-center justify-between border-b border-[#e7ddca] pb-3">
                            <span class="font-medium text-slate-700">City Apartment</span>
                            <span class="rounded-full bg-[#f9ecd0] px-2.5 py-1 text-xs font-bold text-[#9b6c17]">Reviewing</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="font-medium text-slate-700">Family Home</span>
                            <span class="rounded-full bg-[#edf2ee] px-2.5 py-1 text-xs font-bold text-[#2d5d4d]">Published</span>
                        </li>
                    </ul>
                </section>

            </div>
        </div>

        @php
            $editingProperty = request()->query('edit_property');
            $propertyToEdit = $editingProperty ? $properties->firstWhere('id', (int) $editingProperty) : null;
        @endphp

        <section id="properties" data-section="properties" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-black text-[#1d3c34]">Property Management</h2>
                <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">Admin Access</span>
            </div>

            <div class="mt-6 rounded-[1.25rem] border border-[#e7ddca] bg-[#f9f4ed] p-5">
                @if ($propertyToEdit)
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h3 class="text-xl font-black text-[#1d3c34]">Edit Property</h3>
                        <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-[#2d5d4d]">Cancel</a>
                    </div>
                @else
                    <h3 class="text-xl font-black text-[#1d3c34]">Create Property</h3>
                @endif

                <form method="POST" action="{{ $propertyToEdit ? route('dashboard.properties.update', $propertyToEdit) : route('dashboard.properties.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf
                    @if ($propertyToEdit)
                        @method('PUT')
                    @endif

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                        <input type="text" name="title" value="{{ old('title', $propertyToEdit?->title) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                        <textarea name="description" rows="4" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('description', $propertyToEdit?->description) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Price</label>
                        <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $propertyToEdit?->price) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Type</label>
                        <select name="type" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="sale" {{ old('type', $propertyToEdit?->type) === 'sale' ? 'selected' : '' }}>Sale</option>
                            <option value="rent" {{ old('type', $propertyToEdit?->type) === 'rent' ? 'selected' : '' }}>Rent</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
                        <select name="category" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="home" {{ old('category', $propertyToEdit?->property_category ?? $propertyToEdit?->category) === 'home' ? 'selected' : '' }}>Home</option>
                            <option value="villa" {{ old('category', $propertyToEdit?->property_category ?? $propertyToEdit?->category) === 'villa' ? 'selected' : '' }}>Villa</option>
                            <option value="apartment" {{ old('category', $propertyToEdit?->property_category ?? $propertyToEdit?->category) === 'apartment' ? 'selected' : '' }}>Apartment</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Bedrooms</label>
                        <input type="number" name="bedrooms" min="0" value="{{ old('bedrooms', $propertyToEdit?->bedrooms) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Bathrooms</label>
                        <input type="number" name="bathrooms" min="0" value="{{ old('bathrooms', $propertyToEdit?->bathrooms) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Area (sq ft)</label>
                        <input type="number" name="area" min="0" value="{{ old('area', $propertyToEdit?->area) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Status</label>
                        <select name="status" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="draft" {{ old('status', $propertyToEdit?->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $propertyToEdit?->status) === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="available" {{ old('status', $propertyToEdit?->status) === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="sold" {{ old('status', $propertyToEdit?->status) === 'sold' ? 'selected' : '' }}>Sold</option>
                            <option value="archived" {{ old('status', $propertyToEdit?->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">City</label>
                        <input type="text" name="city" value="{{ old('city', $propertyToEdit?->city) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Address</label>
                        <input type="text" name="address" value="{{ old('address', $propertyToEdit?->address) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Property Image</label>
                        <input type="file" name="image" accept="image/*" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 file:mr-3 file:rounded-full file:border-0 file:bg-[#1d3c34] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        @if ($propertyToEdit && $propertyToEdit->image_path)
                            <img src="{{ asset('storage/' . $propertyToEdit->image_path) }}" alt="{{ $propertyToEdit->title }}" class="mt-3 h-28 w-full rounded-xl object-cover shadow-sm ring-1 ring-[#d9cab3]">
                        @endif
                    </div>

                    <div class="md:col-span-2 flex items-center gap-3">
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <input type="checkbox" name="featured" value="1" {{ old('featured', $propertyToEdit?->featured) ? 'checked' : '' }} class="h-4 w-4 rounded border-[#d9cab3] text-[#1d3c34] focus:ring-[#2d5d4d]">
                            Featured property
                        </label>
                    </div>

                    <div class="md:col-span-2 flex gap-3">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            {{ $propertyToEdit ? 'Update Property' : 'Create Property' }}
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section id="workers" data-section="workers" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-black text-[#1d3c34]">Workers Management</h2>
                <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">Local Ethiopia Time</span>
            </div>

            <div class="mt-6 rounded-[1.25rem] border border-[#d9cab3] bg-[#f9f4ed] p-5">
                <h3 class="text-xl font-black text-[#1d3c34]">Add Worker</h3>
                <form method="POST" action="{{ route('dashboard.workers.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Name</label>
                        <input type="text" name="name" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                        <input type="email" name="email" required class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Department</label>
                        <input type="text" name="department" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Phone</label>
                        <input type="text" name="phone" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            Add Worker
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-6 overflow-hidden rounded-[1.25rem] border border-[#e7ddca]">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#1d3c34] text-[#f8f3eb]">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Name</th>
                            <th class="px-5 py-4 font-semibold">Department</th>
                            <th class="px-5 py-4 font-semibold">Phone</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($workers as $worker)
                            <tr class="border-t border-[#e7ddca] bg-white">
                                <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $worker->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $worker->department }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $worker->phone }}</td>
                                <td class="px-5 py-4">
                                    @php
                                        $status = $worker->latestAttendance?->status ?? 'pending';
                                    @endphp
                                    @if ($status === 'present')
                                        <span class="rounded-full bg-[#dfeee4] px-2.5 py-1 text-xs font-bold text-[#1d3c34]">Present</span>
                                    @elseif ($status === 'late')
                                        <span class="rounded-full bg-[#f9ecd0] px-2.5 py-1 text-xs font-bold text-[#9b6c17]">Late</span>
                                    @elseif ($status === 'absent')
                                        <span class="rounded-full bg-[#f8ddd9] px-2.5 py-1 text-xs font-bold text-[#a24339]">Absent</span>
                                    @else
                                        <span class="rounded-full bg-[#edf2ee] px-2.5 py-1 text-xs font-bold text-[#2d5d4d]">Pending</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <form method="POST" action="{{ route('workers.recordAttendance', $worker) }}">
                                        @csrf
                                        <button type="submit" class="rounded-full bg-[#1d3c34] px-4 py-2 text-xs font-bold uppercase tracking-[0.12em] text-white transition hover:bg-[#254d43]">
                                            Record Attendance
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        @php
            $editUserId = request()->query('edit_user');
            $userToEdit = $editUserId ? $users->firstWhere('id', (int) $editUserId) : null;
        @endphp

        <section id="users" data-section="users" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-[#f9f4ed] p-6 shadow-sm">
            <h2 class="text-2xl font-black text-[#1d3c34]">User Manager</h2>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
                <div class="overflow-hidden rounded-[1.25rem] border border-[#e7ddca] bg-white">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#2d5d4d] text-[#f8f3eb]">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Name</th>
                                <th class="px-5 py-4 font-semibold">Email</th>
                                <th class="px-5 py-4 font-semibold">Role</th>
                                <th class="px-5 py-4 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="border-t border-[#e7ddca]">
                                    <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $user->name }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $user->email }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full {{ $user->role === 'admin' ? 'bg-[#dfeee4]' : 'bg-[#f9ecd0]' }} px-2.5 py-1 text-xs font-bold {{ $user->role === 'admin' ? 'text-[#1d3c34]' : 'text-[#9b6c17]' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('dashboard', ['edit_user' => $user->id, 'section' => 'users']) }}" class="rounded-full bg-[#1d3c34] px-3 py-1.5 text-xs font-bold text-white">Edit</a>
                                            <form method="POST" action="{{ route('dashboard.users.delete', $user) }}" onsubmit="return confirm('Delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border border-[#c86b5c] bg-[#fef3f1] px-3 py-1.5 text-xs font-bold text-[#a24339]" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="rounded-[1.25rem] border border-[#d9cab3] bg-white p-5">
                    <h3 class="text-xl font-black text-[#1d3c34]">{{ $userToEdit ? 'Edit User' : 'Add User' }}</h3>
                    <form method="POST" action="{{ $userToEdit ? route('dashboard.users.update', $userToEdit) : route('dashboard.users.store') }}" class="mt-5 space-y-4">
                        @csrf
                        @if ($userToEdit)
                            @method('PUT')
                        @endif
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Name</label>
                            <input type="text" name="name" value="{{ old('name', $userToEdit?->name) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $userToEdit?->email) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">{{ $userToEdit ? 'New Password (optional)' : 'Password' }}</label>
                            <input type="password" name="password" {{ $userToEdit ? '' : 'required' }} class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Role</label>
                            <select name="role" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                                <option value="user" {{ old('role', $userToEdit?->role) === 'user' ? 'selected' : '' }}>User</option>
                                <option value="admin" {{ old('role', $userToEdit?->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-3">
                            <button type="submit" class="w-full rounded-xl bg-[#1d3c34] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                                {{ $userToEdit ? 'Update User' : 'Save User' }}
                            </button>
                            @if ($userToEdit)
                                <a href="{{ route('dashboard', ['section' => 'users']) }}" class="w-full rounded-xl border border-[#d9cab3] bg-white px-4 py-3 text-center text-sm font-bold text-[#1d3c34]">
                                    Cancel edit
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </section>

        @php
            $editPostId = request()->query('edit_post');
            $postToEdit = $editPostId ? $posts->firstWhere('id', (int) $editPostId) : null;
        @endphp

        @if (auth()->user()->canCreateContent())
            <section id="blog-posts" data-section="blog-posts" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-black text-[#1d3c34]">{{ $postToEdit ? 'Edit Blog & News Post' : 'Blog & News Post' }}</h2>
                    <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">
                        {{ $postToEdit ? 'Edit Mode' : 'Admin Access' }}
                    </span>
                </div>

                <form method="POST" action="{{ $postToEdit ? route('dashboard.posts.update', $postToEdit) : route('dashboard.posts.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-4 md:grid-cols-2">
                    @csrf
                    @if ($postToEdit)
                        @method('PUT')
                    @endif

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                        <input type="text" name="title" value="{{ old('title', $postToEdit?->title) }}" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $postToEdit?->slug) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Type</label>
                        <select name="type" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="blog" {{ old('type', $postToEdit?->type) === 'blog' ? 'selected' : '' }}>Blog</option>
                            <option value="news" {{ old('type', $postToEdit?->type) === 'news' ? 'selected' : '' }}>News</option>
                            <option value="listing" {{ old('type', $postToEdit?->type) === 'listing' ? 'selected' : '' }}>New Listing</option>
                            <option value="project" {{ old('type', $postToEdit?->type) === 'project' ? 'selected' : '' }}>Featured Project</option>
                            <option value="job" {{ old('type', $postToEdit?->type) === 'job' ? 'selected' : '' }}>Organization Job</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
                        <input type="text" name="category" value="{{ old('category', $postToEdit?->category) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Tags</label>
                        <input type="text" name="tags" value="{{ old('tags', $postToEdit?->tags) }}" placeholder="market,harar,property" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Author</label>
                        <input type="text" name="author_name" value="{{ old('author_name', $postToEdit?->author_name ?? auth()->user()->name) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Status</label>
                        <select name="status" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                            <option value="draft" {{ old('status', $postToEdit?->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $postToEdit?->status) === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="scheduled" {{ old('status', $postToEdit?->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Publication date</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', $postToEdit?->published_at?->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Featured image</label>
                        <input type="file" name="image" accept="image/*" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Social image</label>
                        <input type="file" name="social_image" accept="image/*" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">SEO title</label>
                        <input type="text" name="seo_title" value="{{ old('seo_title', $postToEdit?->seo_title) }}" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">SEO description</label>
                        <textarea name="seo_description" rows="2" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('seo_description', $postToEdit?->seo_description) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Related post IDs</label>
                        <input type="text" name="related_posts" value="{{ old('related_posts', $postToEdit?->related_post_ids) }}" placeholder="1, 2, 3" class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Content</label>
                        <textarea name="content" rows="8" required class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">{{ old('content', $postToEdit?->content) }}</textarea>
                    </div>

                    <div class="md:col-span-2 flex flex-wrap gap-3">
                        <button type="submit" class="rounded-full bg-[#1d3c34] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#254d43]">
                            {{ $postToEdit ? 'Update Post' : 'Publish Post' }}
                        </button>
                        @if ($postToEdit)
                            <a href="{{ route('dashboard', ['section' => 'blog-posts']) }}" class="rounded-full border border-[#d9cab3] bg-white px-5 py-3 text-sm font-bold text-[#1d3c34]">Cancel edit</a>
                        @endif
                    </div>
                </form>
            </section>
        @endif

        <section class="mt-10 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-black text-[#1d3c34]">Content Library</h2>
                <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">{{ $posts->count() }} posts</span>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="mt-5 grid gap-3 md:grid-cols-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts" class="rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5">
                <select name="type" class="rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5">
                    <option value="">All types</option>
                    <option value="blog" {{ request('type') === 'blog' ? 'selected' : '' }}>Blog</option>
                    <option value="news" {{ request('type') === 'news' ? 'selected' : '' }}>News</option>
                    <option value="listing" {{ request('type') === 'listing' ? 'selected' : '' }}>New Listing</option>
                    <option value="project" {{ request('type') === 'project' ? 'selected' : '' }}>Featured Project</option>
                    <option value="job" {{ request('type') === 'job' ? 'selected' : '' }}>Organization Job</option>
                </select>
                <select name="status" class="rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3 py-2.5">
                    <option value="">All status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                </select>
                <button type="submit" class="rounded-xl bg-[#1d3c34] px-4 py-2.5 text-sm font-bold text-white">Apply filters</button>
            </form>

            <div class="mt-6 overflow-hidden rounded-[1.25rem] border border-[#e7ddca]">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#2d5d4d] text-[#f8f3eb]">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Title</th>
                            <th class="px-5 py-4 font-semibold">Type</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 font-semibold">Date</th>
                            <th class="px-5 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr class="border-t border-[#e7ddca] bg-white">
                                <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $post->title }}</td>
                                <td class="px-5 py-4 text-slate-600 uppercase">{{ $post->type }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full {{ $post->status === 'published' ? 'bg-[#dfeee4] text-[#1d3c34]' : ($post->status === 'scheduled' ? 'bg-[#f9ecd0] text-[#9b6c17]' : 'bg-[#edf2ee] text-[#2d5d4d]') }} px-2.5 py-1 text-xs font-bold">
                                        {{ ucfirst($post->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $post->published_at?->format('M d, Y') ?? $post->scheduled_for?->format('M d, Y') ?? 'Not set' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('dashboard', ['edit_post' => $post->id, 'section' => 'blog-posts']) }}" class="rounded-full bg-[#1d3c34] px-3 py-2 text-xs font-bold text-white">Edit</a>
                                        <form method="POST" action="{{ route('dashboard.posts.togglePublish', $post) }}">
                                            @csrf
                                            <button type="submit" class="rounded-full border border-[#d9cab3] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                                                {{ $post->status === 'published' ? 'Unpublish' : 'Publish' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('dashboard.posts.delete', $post) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-2 text-xs font-bold text-[#a24339]" onclick="return confirm('Delete this post?')">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-slate-500">No posts match the current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-10 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-black text-[#1d3c34]">Property List</h2>
                <span class="rounded-full bg-[#edf2ee] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[#1d3c34]">{{ $properties->count() }} items</span>
            </div>

            <div class="mt-6 overflow-hidden rounded-[1.25rem] border border-[#e7ddca]">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#2d5d4d] text-[#f8f3eb]">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Title</th>
                            <th class="px-5 py-4 font-semibold">Price</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 font-semibold">Featured</th>
                            <th class="px-5 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($properties as $property)
                            <tr class="border-t border-[#e7ddca] bg-white">
                                <td class="px-5 py-4 font-semibold text-[#1d3c34]">{{ $property->title }}</td>
                                <td class="px-5 py-4 text-slate-600">${{ number_format($property->price, 0) }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full {{ $property->status === 'published' ? 'bg-[#dfeee4] text-[#1d3c34]' : ($property->status === 'archived' ? 'bg-[#f8ddd9] text-[#a24339]' : 'bg-[#f9ecd0] text-[#9b6c17]') }} px-2.5 py-1 text-xs font-bold">
                                        {{ ucfirst($property->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $property->featured ? 'Yes' : 'No' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('dashboard', ['edit_property' => $property->id, 'section' => 'properties']) }}" class="rounded-full bg-[#1d3c34] px-3 py-2 text-xs font-bold text-white">Edit</a>
                                        <form method="POST" action="{{ route('dashboard.properties.togglePublish', $property) }}">
                                            @csrf
                                            <button type="submit" class="rounded-full border border-[#b9a98b] bg-white px-3 py-2 text-xs font-bold text-[#1d3c34]">
                                                {{ $property->is_active ? 'Unpublish' : 'Publish' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('dashboard.properties.toggleSold', $property) }}">
                                            @csrf
                                            <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#f8f3eb] px-3 py-2 text-xs font-bold text-[#1d3c34]">
                                                {{ $property->status === 'sold' ? 'Mark Available' : 'Mark Sold' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('dashboard.properties.archive', $property) }}">
                                            @csrf
                                            <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#f9f4ed] px-3 py-2 text-xs font-bold text-[#1d3c34]" {{ $property->status === 'archived' ? 'disabled' : '' }}>
                                                Archive
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('dashboard.properties.destroy', $property) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-[#d9cab3] bg-[#fff1f0] px-3 py-2 text-xs font-bold text-[#a24339]" onclick="return confirm('Delete this property?')">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-slate-500">No properties yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($posts->isNotEmpty())
            <section class="mt-10 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 shadow-sm">
                <h2 class="text-2xl font-black text-[#1d3c34]">Latest Blog & News</h2>
                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    @foreach ($posts as $post)
                        <article class="rounded-[1.25rem] border border-[#e7ddca] bg-[#f9f4ed] p-5">
                            <div class="flex items-center justify-between gap-3">
                                <span class="rounded-full bg-[#dfeee4] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#1d3c34]">{{ $post->type }}</span>
                                <span class="text-xs text-slate-500">{{ $post->user->name }}</span>
                            </div>
                            <h3 class="mt-4 text-xl font-black text-[#1d3c34]">{{ $post->title }}</h3>
                            <p class="mt-3 line-clamp-4 text-sm leading-6 text-slate-600">{{ $post->content }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navLinks = document.querySelectorAll('.nav-section-link');
            const panels = document.querySelectorAll('.section-panel');

            function activateSection(sectionName) {
                panels.forEach(panel => {
                    const visible = panel.dataset.section === sectionName;
                    panel.classList.toggle('hidden', !visible);
                });

                navLinks.forEach(link => {
                    const active = link.dataset.targetSection === sectionName;
                    link.classList.toggle('bg-white/10', active);
                    link.classList.toggle('text-white', active);
                    link.classList.toggle('text-[#dfeee4]', !active);
                    link.classList.toggle('font-semibold', true);
                });
            }

            navLinks.forEach(link => {
                link.addEventListener('click', function (event) {
                    const target = this.dataset.targetSection;
                    if (!target) {
                        return;
                    }

                    event.preventDefault();
                    activateSection(target);
                    const targetPanel = document.querySelector('[data-section="' + target + '"]');
                    if (targetPanel) {
                        targetPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });

            const params = new URLSearchParams(window.location.search);
            const paramSection = params.get('section');
            const editSection = params.has('edit_user') ? 'users'
                : params.has('edit_post') ? 'blog-posts'
                : params.has('edit_property') ? 'properties'
                : null;
            const initialHash = window.location.hash.replace('#', '');
            const initialSection = initialHash || paramSection || editSection || 'overview';

            if (document.querySelector('[data-section="' + initialSection + '"]')) {
                activateSection(initialSection);
            }
        });
    </script>
</body>
</html>
