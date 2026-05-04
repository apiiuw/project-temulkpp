@extends('layouts.superadmin')

@section('container')
<div class="min-h-screen bg-stone-50/50 px-4 py-8 md:px-8">
    <div class="mx-auto max-w-7xl">
        {{-- Header Section --}}
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-stone-900 md:text-3xl">Perizinan Menu</h1>
                <p class="mt-1 text-sm font-medium text-stone-500">Atur akses menu untuk setiap role pengguna dalam sistem.</p>
            </div>
            <div>
                <button form="permissionsForm" type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-amber-900 px-8 py-3 text-sm font-bold text-white transition-all hover:bg-amber-800 hover:shadow-lg active:scale-95">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>

        <form id="permissionsForm" action="{{ route('superadmin.permissions.update') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                @foreach ($roles as $role)
                    <div class="flex flex-col gap-6">
                        {{-- Role Card --}}
                        <div class="rounded-[2.5rem] border border-stone-200 bg-white p-8 shadow-sm">
                            <div class="mb-6 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $role === 'superadmin' ? 'bg-stone-900 text-amber-100' : ($role === 'pimpinan' ? 'bg-amber-100 text-amber-900' : 'bg-red-100 text-red-900') }}">
                                    @if($role === 'superadmin')
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                                    @elseif($role === 'pimpinan')
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632.02-.219.037-.441.037-.666 0-.01 0-.02.001-.031a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198a9.094 9.094 0 0 1-3.741-.479M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" /></svg>
                                    @else
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-xl font-black capitalize text-stone-900">{{ $role }}</h3>
                                    <p class="text-xs font-bold uppercase tracking-widest text-stone-400">Akses Menu</p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                @foreach ($menus as $category => $categoryMenus)
                                    <div>
                                        <p class="mb-3 text-[10px] font-black uppercase tracking-[0.2em] text-stone-400">{{ $category }}</p>
                                        <div class="space-y-2">
                                            @foreach ($categoryMenus as $menu)
                                                <label class="group flex cursor-pointer items-center justify-between rounded-2xl border border-stone-100 bg-stone-50/50 p-4 transition-all hover:border-amber-200 hover:bg-amber-50/30">
                                                    <div class="flex items-center gap-3">
                                                        <div class="text-stone-400 transition-colors group-hover:text-amber-600">
                                                            {!! $menu->icon !!}
                                                        </div>
                                                        <span class="text-sm font-bold text-stone-700 transition-colors group-hover:text-stone-900">{{ $menu->name }}</span>
                                                    </div>
                                                    <div class="relative">
                                                        <input 
                                                            type="checkbox" 
                                                            name="permissions[{{ $role }}][]" 
                                                            value="{{ $menu->id }}"
                                                            {{ in_array($menu->id, $rolePermissions[$role]) ? 'checked' : '' }}
                                                            class="peer sr-only"
                                                        >
                                                        <div class="h-6 w-11 rounded-full bg-stone-200 transition-colors peer-checked:bg-amber-600"></div>
                                                        <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-5"></div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </form>
    </div>
</div>

@if(session('success'))
    <div id="toast" class="fixed bottom-8 right-8 z-[100] animate-bounce-in">
        <div class="flex items-center gap-3 rounded-2xl bg-stone-900 px-6 py-4 text-white shadow-2xl">
            <div class="rounded-full bg-green-500 p-1">
                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm font-bold">{{ session('success') }}</p>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const toast = document.getElementById('toast');
            toast.classList.add('opacity-0', 'translate-y-4');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    </script>
@endif

@endsection
