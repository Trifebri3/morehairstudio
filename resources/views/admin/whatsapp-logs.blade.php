@extends('layouts.admin')

@section('page_title')
    WhatsApp Cloud API Simulator Logs
@endsection

@section('content')
<div class="space-y-6">
    <!-- Masked API Credentials -->
    <div class="glass-panel p-6 rounded-2xl border border-stone-200 bg-white shadow-sm grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <span class="text-xxs uppercase tracking-wider text-stone-400 font-bold block mb-1">WhatsApp Secret Token</span>
            <span class="text-xs font-mono text-stone-700 font-semibold block select-none">
                EAAQzU7G3dZAIBACeZBk28**************************************
            </span>
            <span class="text-xxs text-emerald-600 mt-1 block">🔐 Encrypted on Server</span>
        </div>
        <div>
            <span class="text-xxs uppercase tracking-wider text-stone-400 font-bold block mb-1">Phone Number ID</span>
            <span class="text-xs font-mono text-stone-700 font-semibold block">1082608182910</span>
        </div>
        <div>
            <span class="text-xxs uppercase tracking-wider text-stone-400 font-bold block mb-1">Webhook Status</span>
            <span class="text-xs text-emerald-600 font-bold flex items-center mt-1">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 mr-2 animate-ping"></span>
                Connected (Live)
            </span>
        </div>
    </div>

    <!-- Message logs table -->
    <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm">
        <h3 class="font-black text-stone-900 text-xs uppercase tracking-wider mb-4">Simulated Message Logs</h3>
        <div class="overflow-x-auto rounded-xl border border-stone-200">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Recipient</th>
                        <th class="py-3 px-4">Booking Ref</th>
                        <th class="py-3 px-4">Template</th>
                        <th class="py-3 px-4">Message Body</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-150 bg-white">
                    @forelse($logs as $log)
                        <tr class="hover:bg-stone-50/50 transition text-stone-700">
                            <td class="py-3.5 px-4 font-mono font-medium">+{{ $log->phone }}</td>
                            <td class="py-3.5 px-4 font-mono text-[#0A3D91] font-bold">
                                {{ $log->booking ? $log->booking->booking_code : 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono uppercase text-stone-550 text-xxs">
                                {{ $log->template_name ?? 'text' }}
                            </td>
                            <td class="py-3.5 px-4 font-light text-xxs leading-relaxed max-w-sm">
                                {!! nl2br(e($log->body)) !!}
                            </td>
                            <td class="py-3.5 px-4">
                                <x-ui.badge variant="{{ $log->status === 'sent' || $log->status === 'delivered' ? 'success' : 'neutral' }}">
                                    {{ $log->status }}
                                </x-ui.badge>
                            </td>
                            <td class="py-3.5 px-4 text-stone-400 text-xxs">
                                {{ $log->created_at->format('d M Y H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-stone-400">Belum ada log pengiriman pesan WhatsApp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
