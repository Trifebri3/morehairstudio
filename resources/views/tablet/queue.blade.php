@extends('layouts.tablet')

@section('content')
<div class="h-full flex flex-col justify-between py-6 relative" id="queue-page">

    {{-- Header --}}
    <div class="space-y-6">
        <div class="flex items-center justify-between border-b border-stone-200 pb-5">
            <div>
                <h3 class="text-2xl font-display font-bold text-stone-900 mb-1 tracking-wide">Live Visual Queue</h3>
                <p class="text-stone-500 text-xs font-medium">Papan pemantauan antrean treatment hari ini secara real-time.</p>
            </div>
            <div class="flex items-center gap-3">
                <span id="queue-ts" class="text-[10px] font-mono text-stone-400 hidden sm:block">—</span>
                <div id="queue-status"
                     class="flex items-center space-x-2 text-[10px] uppercase font-mono tracking-widest text-[#c9512d] font-extrabold bg-[#faede7] px-3.5 py-1.5 rounded-full border border-[#faede7]">
                    <span id="queue-dot" class="w-1.5 h-1.5 rounded-full bg-[#c9512d] animate-ping"></span>
                    <span id="queue-label">Live · Menghubungkan...</span>
                </div>
            </div>
        </div>

        {{-- Three Column Queue Board --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4 items-start">

            {{-- Column 1: Waiting --}}
            <div class="bg-white p-6 rounded-3xl border border-stone-200 space-y-5 shadow-2xs">
                <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-blue-800 flex items-center font-display">
                        <span class="h-2 w-2 rounded-full bg-blue-500 mr-2"></span>
                        Menunggu Check-In
                    </h4>
                    <span id="count-waiting" class="px-2.5 py-0.5 bg-blue-50 text-blue-800 rounded-full font-bold text-xxs font-mono">0</span>
                </div>
                <div id="col-waiting" class="space-y-4 overflow-y-auto max-h-[500px] pr-1">
                    <div class="text-center py-12 text-stone-400 queue-empty">
                        <p class="text-xs font-medium">Tidak ada customer menunggu check-in.</p>
                    </div>
                </div>
            </div>

            {{-- Column 2: On Chair --}}
            <div class="bg-white p-6 rounded-3xl border border-stone-200 space-y-5 shadow-2xs">
                <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-[#c9512d] flex items-center font-display">
                        <span class="h-2 w-2 rounded-full bg-[#c9512d] mr-2 animate-ping"></span>
                        On Chair (Sedang Berjalan)
                    </h4>
                    <span id="count-onchair" class="px-2.5 py-0.5 bg-[#faede7] text-[#c9512d] rounded-full font-bold text-xxs font-mono">0</span>
                </div>
                <div id="col-onchair" class="space-y-4 overflow-y-auto max-h-[500px] pr-1">
                    <div class="text-center py-12 text-stone-400 queue-empty">
                        <p class="text-xs font-medium">Tidak ada treatment sedang berjalan.</p>
                    </div>
                </div>
            </div>

            {{-- Column 3: Done --}}
            <div class="glass-panel p-6 rounded-3xl border border-stone-200 bg-stone-50 space-y-5">
                <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-stone-400 flex items-center font-display">
                        <span class="h-2 w-2 rounded-full bg-stone-400 mr-2"></span>
                        Done Today
                    </h4>
                    <span id="count-done" class="px-2.5 py-0.5 bg-stone-150 text-stone-700 rounded font-bold text-xxs font-mono">0</span>
                </div>
                <div id="col-done" class="space-y-4 overflow-y-auto max-h-[500px] pr-1">
                    <div class="text-center py-12 text-stone-400 queue-empty">
                        <p class="text-xs font-medium">Belum ada treatment selesai hari ini.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Back Button --}}
    <div class="pt-6 border-t border-stone-200 flex justify-end mt-8">
        <a href="{{ route('tablet.dashboard') }}"
           class="px-4 py-2 border border-stone-200 rounded-xl text-stone-650 hover:bg-stone-50 text-xs font-bold transition">
            Kembali Ke Menu
        </a>
    </div>
</div>

<style>
    @keyframes card-in {
        from { opacity: 0; transform: translateY(8px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .queue-card-enter { animation: card-in 0.3s cubic-bezier(0.16,1,0.3,1) forwards; }
    .q-progress-bar   { transition: width 1.2s cubic-bezier(0.4,0,0.2,1); will-change: width; }
    #queue-status.offline { background:#fef2f2; border-color:#fecaca; color:#dc2626; }
    #queue-status.offline #queue-dot { background:#dc2626; animation:none; }
    /* Prevent layout shifts from timestamp changing */
    #queue-ts { min-width: 140px; display: inline-block; }
    /* Isolate queue columns from repaints */
    #col-waiting, #col-onchair, #col-done { contain: layout style; }
</style>

<script>
(function() {
    var API_URL  = '{{ route("tablet.queue.data") }}';
    var CSRF     = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var INTERVAL = 5000;
    var rendered = { waiting: {}, on_chair: {}, done: {} };
    var firstLoad = true;
    var timer = null;

    function esc(str) {
        var d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }

    function cardWaiting(b) {
        return '<div id="qcard-' + b.id + '" class="bg-stone-50/70 border border-stone-200 p-5 rounded-2xl hover:border-stone-400 transition duration-300 space-y-4 shadow-2xs queue-card-enter">' +
            '<div>' +
              '<div class="flex justify-between items-center">' +
                '<span class="text-xxs font-mono text-stone-400 font-bold uppercase tracking-wider">' + esc(b.booking_code) + '</span>' +
                '<span class="text-[10px] font-mono text-stone-500 font-bold">' + esc(b.start_time) + ' WIB</span>' +
              '</div>' +
              '<h5 class="font-bold text-base text-stone-900 mt-1.5 font-display">' + esc(b.customer_name) + '</h5>' +
              '<div class="inline-flex items-center mt-2.5 bg-stone-100 text-stone-700 border border-stone-200 px-2.5 py-1 rounded-lg text-xxs font-extrabold uppercase tracking-wide">' +
                '<span>' + esc(b.service_name) + ' (' + b.duration + 'm)</span>' +
              '</div>' +
            '</div>' +
            '<div class="pt-3 border-t border-stone-100 flex justify-between items-center text-xxs text-stone-500 font-medium">' +
              '<span>Stylist: <strong class="text-stone-700">' + esc(b.stylist_name) + '</strong></span>' +
            '</div>' +
            '<a href="' + b.checkin_url + '" class="w-full mt-1.5 py-2 px-4 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs uppercase tracking-wider text-center block transition">Cek-In Customer &rarr;</a>' +
        '</div>';
    }

    function cardOnChair(b) {
        var timing = (b.service_start && b.service_end)
            ? b.service_start + ' - ' + b.service_end + ' WIB'
            : b.duration + ' Menit';
        var prog = '';
        if (b.service_start && b.service_end) {
            prog = '<div class="w-full bg-stone-100 rounded-full h-1.5 overflow-hidden">' +
                     '<div class="bg-[#c9512d] h-full q-progress-bar" id="prog-' + b.id + '" style="width:' + b.progress + '%"></div>' +
                   '</div>' +
                   '<div class="flex justify-between items-center text-[10px] text-stone-500">' +
                     '<span>Progres: <strong id="prog-pct-' + b.id + '">' + b.progress + '%</strong></span>' +
                     '<span class="font-bold text-[#c9512d]">Sisa: <span id="prog-rem-' + b.id + '">' + b.remaining + '</span> Menit</span>' +
                   '</div>';
        }
        return '<div id="qcard-' + b.id + '" class="bg-[#faede7]/30 border border-[#f4cbba] p-5 rounded-2xl transition duration-300 space-y-4 shadow-2xs queue-card-enter">' +
            '<div>' +
              '<div class="flex justify-between items-center">' +
                '<span class="text-xxs font-mono text-stone-500 font-bold uppercase tracking-wider">' + esc(b.booking_code) + '</span>' +
                '<span class="text-[9px] font-mono uppercase bg-[#c9512d] text-white px-2 py-0.5 rounded font-extrabold tracking-wider">Auto-Selesai</span>' +
              '</div>' +
              '<h5 class="font-bold text-base text-stone-900 mt-1.5 font-display">' + esc(b.customer_name) + '</h5>' +
              '<div class="inline-flex items-center mt-2 bg-[#faede7] text-[#c9512d] border border-[#faede7] px-2.5 py-1 rounded-lg text-xxs font-extrabold uppercase tracking-wide">' +
                '<span>' + esc(b.service_name) + ' (' + b.duration + ' mnt)</span>' +
              '</div>' +
            '</div>' +
            '<div class="p-3 bg-white rounded-xl border border-stone-200 space-y-2">' +
              '<div class="flex justify-between items-center text-xs font-mono">' +
                '<span class="text-stone-500 text-[11px]">Waktu Layanan:</span>' +
                '<span class="font-bold text-stone-900">' + timing + '</span>' +
              '</div>' + prog +
            '</div>' +
            '<div class="pt-2 border-t border-stone-100 flex justify-between items-center text-xxs text-stone-500 font-medium">' +
              '<span>Stylist: <strong class="text-stone-700">' + esc(b.stylist_name) + '</strong></span>' +
            '</div>' +
            '<form method="POST" action="' + b.complete_url + '">' +
              '<input type="hidden" name="_token" value="' + CSRF + '">' +
              '<button type="submit" class="w-full py-2 px-3 bg-stone-200 hover:bg-stone-300 text-stone-700 text-xxs font-bold uppercase tracking-wider rounded-xl transition">Selesai Lebih Cepat (Manual Override)</button>' +
            '</form>' +
        '</div>';
    }

    function cardDone(b) {
        return '<div id="qcard-' + b.id + '" class="glass-panel bg-stone-50/50 border border-stone-200 p-5 rounded-2xl opacity-75 hover:opacity-100 transition duration-300 space-y-3 queue-card-enter">' +
            '<div class="flex justify-between items-center">' +
              '<span class="text-xxs font-mono text-stone-450 font-bold uppercase tracking-wider">' + esc(b.booking_code) + '</span>' +
              '<span class="text-[10px] text-green-600 font-extrabold uppercase font-mono">Completed</span>' +
            '</div>' +
            '<h5 class="font-bold text-base text-stone-800 mt-1 font-display">' + esc(b.customer_name) + '</h5>' +
            '<p class="text-xxs text-stone-500 uppercase tracking-wide">' + esc(b.service_name) + '</p>' +
            '<div class="pt-2 mt-2 border-t border-stone-100 flex justify-between items-center text-xxs text-stone-500">' +
              '<span>Stylist: ' + esc(b.stylist_name) + '</span>' +
            '</div>' +
        '</div>';
    }

    function emptyHTML(msg) {
        return '<div class="text-center py-12 text-stone-400 queue-empty"><p class="text-xs font-medium">' + msg + '</p></div>';
    }

    function setText(el, val) {
        /* Guard: only write to DOM if value actually changed */
        if (el && el.textContent !== String(val)) el.textContent = val;
    }
    function setWidth(el, val) {
        var w = val + '%';
        if (el && el.style.width !== w) el.style.width = w;
    }

    function renderColumn(colId, countId, items, buildCard, emptyMsg, key) {
        var col   = document.getElementById(colId);
        var count = document.getElementById(countId);
        if (!col || !count) return;

        /* Only update count if changed */
        setText(count, items.length);

        var newIds = {};
        items.forEach(function(b) { newIds[b.id] = b; });

        // Remove gone cards
        Object.keys(rendered[key]).forEach(function(id) {
            if (!newIds[id]) {
                var el = document.getElementById('qcard-' + id);
                if (el) {
                    el.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                    el.style.opacity = '0';
                    el.style.transform = 'scale(0.96)';
                    setTimeout(function() { if (el.parentNode) el.remove(); }, 260);
                }
            }
        });

        if (items.length === 0) {
            setTimeout(function() {
                if (!col.querySelector('.queue-empty')) {
                    col.querySelectorAll('[id^="qcard-"]').forEach(function(e) { e.remove(); });
                    col.innerHTML = emptyHTML(emptyMsg);
                }
            }, 270);
        } else {
            col.querySelectorAll('.queue-empty').forEach(function(e) { e.remove(); });
            items.forEach(function(b, idx) {
                var existing = document.getElementById('qcard-' + b.id);
                if (!existing) {
                    var tmp = document.createElement('div');
                    tmp.innerHTML = buildCard(b).trim();
                    var card = tmp.firstChild;
                    card.style.animationDelay = firstLoad ? (idx * 60) + 'ms' : '0ms';
                    col.appendChild(card);
                } else {
                    /* Update progress only if values changed — no-flicker */
                    setWidth(document.getElementById('prog-' + b.id), b.progress);
                    setText(document.getElementById('prog-pct-' + b.id), b.progress + '%');
                    setText(document.getElementById('prog-rem-' + b.id), b.remaining);
                }
            });
        }

        rendered[key] = newIds;
    }

    function setStatus(online) {
        var status = document.getElementById('queue-status');
        var label  = document.getElementById('queue-label');
        if (!status || !label) return;
        if (online) {
            /* Always remove offline state, only update label text if needed */
            status.classList.remove('offline');
            if (label.textContent !== 'Live · Realtime') label.textContent = 'Live · Realtime';
        } else {
            status.classList.add('offline');
            if (label.textContent !== 'Offline · Reconnecting...') label.textContent = 'Offline · Reconnecting...';
        }
    }

    function fetchQueue() {
        fetch(API_URL, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            credentials: 'same-origin'
        })
        .then(function(res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function(data) {
            renderColumn('col-waiting', 'count-waiting', data.waiting,  cardWaiting, 'Tidak ada customer menunggu check-in.', 'waiting');
            renderColumn('col-onchair', 'count-onchair', data.on_chair, cardOnChair, 'Tidak ada treatment sedang berjalan.',   'on_chair');
            renderColumn('col-done',    'count-done',    data.done,     cardDone,    'Belum ada treatment selesai hari ini.',  'done');
            var ts = document.getElementById('queue-ts');
            /* Only update timestamp text — no layout shift */
            setText(ts, 'Diperbarui: ' + data.ts);
            setStatus(true);
            firstLoad = false;
        })
        .catch(function(err) {
            console.warn('[Queue API]', err);
            setStatus(false);
        });
    }

    function startPolling() {
        if (timer) return;
        fetchQueue();
        timer = setInterval(fetchQueue, INTERVAL);
    }

    function stopPolling() {
        clearInterval(timer);
        timer = null;
        firstLoad = true;
        rendered  = { waiting: {}, on_chair: {}, done: {} };
    }

    startPolling();
    window.addEventListener('beforeunload', stopPolling);
    document.addEventListener('spa:leave', stopPolling);
})();
</script>
@endsection
