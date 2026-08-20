<div>
    @slot('page_title')
        WhatsApp Cloud API Simulator
    @endslot

    <!-- Masked API Credentials -->
    <div class="glass-panel p-6 rounded-2xl border-stone-800 bg-stone-900/30 mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <span class="text-xxs uppercase tracking-wider text-stone-500 font-bold block mb-1">WhatsApp Secret Token</span>
            <span class="text-sm font-mono text-stone-300 font-semibold block select-none">
                EAAQzU7G3dZAIBACeZBk28**************************************
            </span>
            <span class="text-xxs text-amber-500 mt-1 block">🔐 Encrypted on Server</span>
        </div>
        <div>
            <span class="text-xxs uppercase tracking-wider text-stone-500 font-bold block mb-1">Phone Number ID</span>
            <span class="text-sm font-mono text-stone-300 font-semibold block">1082608182910</span>
        </div>
        <div>
            <span class="text-xxs uppercase tracking-wider text-stone-500 font-bold block mb-1">Webhook Status</span>
            <span class="text-xs text-green-455 font-bold flex items-center mt-1">
                <span class="h-2.5 w-2.5 rounded-full bg-green-500 mr-2 animate-ping"></span>
                Connected (Live)
            </span>
        </div>
    </div>

    <!-- Message logs table -->
    <div class="glass-panel p-6 rounded-2xl border-stone-850">
        <h3 class="text-lg font-bold font-serif gold-gradient-text mb-4">Simulated Message Logs</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-stone-800 text-stone-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Recipient</th>
                        <th class="py-3 px-4">Booking Ref</th>
                        <th class="py-3 px-4">Template</th>
                        <th class="py-3 px-4">Message Body</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-850">
                    @forelse($logs as $log)
                        <tr class="hover:bg-stone-850/30 transition text-stone-300">
                            <td class="py-3.5 px-4 font-mono font-medium">+{{ $log->phone }}</td>
                            <td class="py-3.5 px-4 font-mono text-amber-500">
                                {{ $log->booking ? $log->booking->booking_code : 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono uppercase text-stone-400 text-xxs">
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
                            <td class="py-3.5 px-4 text-stone-500 text-xxs">
                                {{ $log->created_at->format('d M Y H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-stone-500">Belum ada log pengiriman pesan WhatsApp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
