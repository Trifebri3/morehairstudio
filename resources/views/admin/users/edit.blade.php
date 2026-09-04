@extends('layouts.admin')

@section('page_title', 'Edit User')

@section('content')
    <div class="glass-panel p-8 rounded-3xl border border-stone-200 bg-white max-w-2xl mx-auto">
        <div class="mb-6">
            <h3 class="text-lg font-bold text-stone-900">Edit System User</h3>
            <span class="text-xxs uppercase tracking-widest font-mono text-stone-400">Update account information</span>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200">
                <ul class="list-disc list-inside text-sm text-red-600">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1.5">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-3 rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:ring-2 focus:ring-[#0A3D91] focus:border-[#0A3D91] transition text-sm">
            </div>

            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1.5">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-3 rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:ring-2 focus:ring-[#0A3D91] focus:border-[#0A3D91] transition text-sm">
            </div>

            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1.5">Password (Leave blank to keep current)</label>
                <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:ring-2 focus:ring-[#0A3D91] focus:border-[#0A3D91] transition text-sm" placeholder="••••••••">
                <p class="mt-1 text-xs text-stone-500">Only fill this if you want to change the password.</p>
            </div>

            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1.5">Role</label>
                <select name="role" required class="w-full px-4 py-3 rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:ring-2 focus:ring-[#0A3D91] focus:border-[#0A3D91] transition text-sm">
                    <option value="super_admin" {{ (old('role') ?? $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="outlet_admin" {{ (old('role') ?? $user->role) == 'outlet_admin' ? 'selected' : '' }}>Outlet Admin</option>
                    <option value="stylist" {{ (old('role') ?? $user->role) == 'stylist' ? 'selected' : '' }}>Stylist</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1.5">Assigned Studio (Optional)</label>
                <select name="outlet_id" class="w-full px-4 py-3 rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:ring-2 focus:ring-[#0A3D91] focus:border-[#0A3D91] transition text-sm">
                    <option value="">-- No Specific Studio --</option>
                    @foreach($outlets as $outlet)
                        <option value="{{ $outlet->id }}" {{ (old('outlet_id') ?? $user->outlet_id) == $outlet->id ? 'selected' : '' }}>{{ $outlet->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-4 flex items-center space-x-4">
                <button type="submit" class="px-6 py-3 bg-[#0A3D91] hover:bg-[#062e70] text-white font-bold rounded-xl text-sm shadow-lg shadow-blue-900/20 transition-all">
                    Update User
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-6 py-3 bg-white hover:bg-stone-50 text-stone-700 font-bold rounded-xl text-sm border border-stone-200 transition-all">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
