<div>
    @slot('page_title')
        Outlet Attendance Logs
    @endslot

    <div class="space-y-6">
        <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
                <div class="flex-grow md:max-w-xs">
                    <x-ui.input placeholder="Search stylists by name..." wire:model.live="search" />
                </div>
                <div class="w-full md:w-48">
                    <x-ui.input type="date" label="" wire:model.live="dateFilter" />
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-stone-200">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-4">Stylist Name</th>
                            <th class="py-3.5 px-4">Date</th>
                            <th class="py-3.5 px-4 text-center">Clock In</th>
                            <th class="py-3.5 px-4 text-center">Clock Out</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4">Device Reference</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse($attendances as $att)
                            <tr class="hover:bg-stone-50/50 transition text-stone-700">
                                <td class="py-3 px-4 font-bold text-stone-900">{{ $att->stylist->name }}</td>
                                <td class="py-3 px-4 font-mono font-medium text-stone-600">
                                    {{ $att->date->format('d M Y') }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-bold text-stone-800">
                                    {{ $att->clock_in ? $att->clock_in->format('H:i') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-bold text-stone-850">
                                    {{ $att->clock_out ? $att->clock_out->format('H:i') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <x-ui.badge variant="{{ $att->status === 'present' ? 'success' : ($att->status === 'late' ? 'neutral' : 'neutral') }}">
                                        {{ $att->status }}
                                    </x-ui.badge>
                                </td>
                                <td class="py-3 px-4 font-mono text-stone-400 text-xxs">
                                    {{ $att->device_info ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-stone-400">No attendance logs found matching query.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-4">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</div>
