@extends('layouts.admin')

@section('page_title', 'Manage System Users')

@section('content')
    <div class="glass-panel p-8 rounded-3xl border border-stone-200 bg-white">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-bold text-stone-900">System Users</h3>
                <span class="text-xxs uppercase tracking-widest font-mono text-stone-400">Manage admins and stylists</span>
            </div>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-[#0A3D91] hover:bg-[#062e70] text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-sm transition">
                + Create New User
            </a>
        </div>
        
        @if(session('success'))
            <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-x-auto rounded-xl border border-stone-200">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                        <th class="py-4 px-5">Name</th>
                        <th class="py-4 px-5">Email</th>
                        <th class="py-4 px-5">Role</th>
                        <th class="py-4 px-5">Studio</th>
                        <th class="py-4 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 bg-white">
                    @forelse($users as $user)
                        <tr class="hover:bg-stone-50/70 transition duration-150 text-stone-700">
                            <td class="py-4 px-5 font-bold text-stone-800">{{ $user->name }}</td>
                            <td class="py-4 px-5 text-stone-500">{{ $user->email }}</td>
                            <td class="py-4 px-5">
                                <x-ui.badge variant="{{ $user->role === 'super_admin' ? 'success' : 'neutral' }}">
                                    {{ str_replace('_', ' ', $user->role) }}
                                </x-ui.badge>
                            </td>
                            <td class="py-4 px-5 text-stone-600">{{ $user->outlet ? $user->outlet->name : '-' }}</td>
                            <td class="py-4 px-5 text-right space-x-2">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-bold transition">
                                    Edit
                                </a>
                                @if(auth()->id() !== $user->id)
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center text-red-600 hover:text-red-800 font-bold transition">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-stone-500 font-medium">Belum ada user yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
