@extends('layouts.app')

@section('content')
<div class="teacher-monitor" data-date="{{ $date->toDateString() }}">
    <header class="teacher-monitor-top">
        <div>
            <div class="teacher-monitor-title">
                <span class="teacher-monitor-icon">⌗</span>
                <h1>{{ __('admin_teacher_scan_monitor.title') }}</h1>
                <span class="teacher-monitor-live"><i></i>{{ __('admin_teacher_scan_monitor.live') }}</span>
            </div>
            <p>{{ __('admin_teacher_scan_monitor.subtitle') }} - <span id="monitorDateLabel">{{ $date->format('M d, Y') }}</span></p>
        </div>
        <div class="teacher-monitor-controls">
            <div class="teacher-monitor-clock" id="monitorClock">--:--:--</div>
            <select id="monitorShift">
                <option value="all">{{ __('admin_teacher_scan_monitor.all_shifts') }}</option>
                <option value="morning">{{ __('admin_teacher_scan_monitor.morning_shift') }}</option>
                <option value="afternoon">{{ __('admin_teacher_scan_monitor.afternoon_shift') }}</option>
                <option value="evening">{{ __('admin_teacher_scan_monitor.evening_shift') }}</option>
            </select>
            <select id="monitorDepartment">
                <option value="all">{{ __('admin_teacher_scan_monitor.all_departments') }}</option>
            </select>
            <input id="monitorSearch" type="text" placeholder="{{ __('admin_teacher_scan_monitor.search_placeholder') }}">
            <button id="monitorRefresh" type="button"><span id="monitorRefreshIcon">↻</span>{{ __('admin_teacher_scan_monitor.refresh') }}</button>
        </div>
    </header>

    <section class="teacher-monitor-stats">
        <article><i class="bar blue"></i><span>{{ __('admin_teacher_scan_monitor.total') }}</span><strong id="statTotal">0</strong><em>{{ __('admin_teacher_scan_monitor.scheduled_today') }}</em></article>
        <article><i class="bar green"></i><span>{{ __('admin_teacher_scan_monitor.present') }}</span><strong id="statPresent">0</strong><em id="statPresentPct">0%</em></article>
        <article><i class="bar amber"></i><span>{{ __('admin_teacher_scan_monitor.late') }}</span><strong id="statLate">0</strong><em id="statLatePct">0%</em></article>
        <article><i class="bar red"></i><span>{{ __('admin_teacher_scan_monitor.absent') }}</span><strong id="statAbsent">0</strong><em id="statAbsentPct">0%</em></article>
        <article><i class="bar purple"></i><span>{{ __('admin_teacher_scan_monitor.permission') }}</span><strong id="statPermission">0</strong><em id="statPermissionPct">0%</em></article>
    </section>

    <section class="teacher-monitor-grid">
        <article class="teacher-monitor-panel present">
            <header><span>{{ __('admin_teacher_scan_monitor.present') }}</span><strong id="countPresent">0</strong></header>
            <div class="teacher-monitor-list" id="listPresent"></div>
        </article>
        <article class="teacher-monitor-panel late">
            <header><span>{{ __('admin_teacher_scan_monitor.late') }}</span><strong id="countLate">0</strong></header>
            <div class="teacher-monitor-list" id="listLate"></div>
        </article>
        <article class="teacher-monitor-panel absent">
            <header><span>{{ __('admin_teacher_scan_monitor.absent') }}</span><strong id="countAbsent">0</strong></header>
            <div class="teacher-monitor-list" id="listAbsent"></div>
        </article>
        <article class="teacher-monitor-panel permission">
            <header><span>{{ __('admin_teacher_scan_monitor.permission_leave') }}</span><strong id="countPermission">0</strong></header>
            <div class="teacher-monitor-list" id="listPermission"></div>
        </article>
    </section>

    <footer class="teacher-monitor-footer">{{ __('admin_teacher_scan_monitor.footer') }}</footer>
</div>

<div class="teacher-monitor-modal" id="teacherMonitorModal" aria-hidden="true">
    <div class="teacher-monitor-modal-card">
        <button type="button" id="teacherMonitorModalClose">×</button>
        <div class="teacher-monitor-modal-head">
            <div id="modalAvatar"></div>
            <div>
                <h2 id="modalName"></h2>
                <p id="modalDept"></p>
            </div>
            <span id="modalBadge"></span>
        </div>
        <dl>
            <div><dt>{{ __('admin_teacher_scan_monitor.subject') }}</dt><dd id="modalSubject"></dd></div>
            <div><dt>{{ __('admin_teacher_scan_monitor.class') }}</dt><dd id="modalClass"></dd></div>
            <div><dt>{{ __('admin_teacher_scan_monitor.schedule') }}</dt><dd id="modalSchedule"></dd></div>
            <div><dt>{{ __('admin_teacher_scan_monitor.shift') }}</dt><dd id="modalShift"></dd></div>
            <div><dt>{{ __('admin_teacher_scan_monitor.check_in') }}</dt><dd id="modalIn"></dd></div>
            <div><dt>{{ __('admin_teacher_scan_monitor.check_out') }}</dt><dd id="modalOut"></dd></div>
            <div><dt>{{ __('admin_teacher_scan_monitor.late') }}</dt><dd id="modalLate"></dd></div>
            <div><dt>{{ __('admin_teacher_scan_monitor.method') }}</dt><dd id="modalMethod"></dd></div>
        </dl>
    </div>
</div>
@endsection

@push('styles')
<style>
    .teacher-monitor{min-height:calc(100vh - 80px);background:var(--bg);color:var(--text);padding:24px;display:flex;flex-direction:column;gap:18px;font-family:var(--font-sans)}
    .teacher-monitor-top{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
    .teacher-monitor-title{display:flex;align-items:center;gap:10px}
    .teacher-monitor-title h1{margin:0;color:var(--text);font-size:22px;font-weight:800;letter-spacing:0}
    .teacher-monitor-icon{width:30px;height:30px;border-radius:8px;display:grid;place-items:center;background:rgba(37,99,235,.12);color:var(--accent);font-weight:900;border:1px solid rgba(37,99,235,.2)}
    .teacher-monitor-live{display:inline-flex;align-items:center;gap:6px;border:1px solid rgba(34,197,94,.28);background:rgba(34,197,94,.1);color:var(--green);border-radius:999px;padding:3px 10px;font-size:11px;font-weight:800}
    .teacher-monitor-live i{width:7px;height:7px;border-radius:50%;background:var(--green);animation:monitorPulse 1.4s ease-in-out infinite}
    .teacher-monitor-top p{margin:4px 0 0;color:var(--muted);font-size:12px}
    .teacher-monitor-controls{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .teacher-monitor-clock,.teacher-monitor-controls select,.teacher-monitor-controls input,.teacher-monitor-controls button{height:34px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text2);padding:0 11px;font-size:12px;outline:none}
    .teacher-monitor-clock{display:grid;place-items:center;font-family:var(--font-mono);color:var(--text)}
    .teacher-monitor-controls input{width:170px}
    .teacher-monitor-controls button{display:inline-flex;align-items:center;gap:7px;border-color:rgba(37,99,235,.35);background:rgba(37,99,235,.1);color:var(--accent);font-weight:800;cursor:pointer}
    .teacher-monitor-controls button.is-loading span{animation:monitorSpin .7s linear infinite}
    .teacher-monitor-stats{display:grid;grid-template-columns:repeat(5,minmax(130px,1fr));gap:12px}
    .teacher-monitor-stats article{position:relative;overflow:hidden;border:1px solid var(--border);border-radius:12px;background:var(--surface);padding:14px;box-shadow:var(--shadow-sm)}
    .teacher-monitor-stats .bar{position:absolute;left:0;right:0;bottom:0;height:2px}
    .teacher-monitor-stats .blue{background:linear-gradient(90deg,#3b82f6,#2563eb)} .teacher-monitor-stats .green{background:linear-gradient(90deg,#22c55e,#16a34a)} .teacher-monitor-stats .amber{background:linear-gradient(90deg,#f59e0b,#d97706)} .teacher-monitor-stats .red{background:linear-gradient(90deg,#ef4444,#dc2626)} .teacher-monitor-stats .purple{background:linear-gradient(90deg,#a855f7,#9333ea)}
    .teacher-monitor-stats span{display:block;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-size:10px;font-weight:800}
    .teacher-monitor-stats strong{display:block;margin-top:8px;font-family:var(--font-mono);font-size:32px;line-height:1;color:var(--accent)}
    .teacher-monitor-stats article:nth-child(2) strong{color:var(--green)}.teacher-monitor-stats article:nth-child(3) strong{color:var(--amber)}.teacher-monitor-stats article:nth-child(4) strong{color:var(--red)}.teacher-monitor-stats article:nth-child(5) strong{color:var(--violet)}
    .teacher-monitor-stats em{display:block;margin-top:5px;color:var(--muted2);font-size:11px;font-style:normal}
    .teacher-monitor-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .teacher-monitor-panel{border-radius:12px;overflow:hidden;min-height:280px;display:flex;flex-direction:column;background:var(--surface);border:1px solid var(--border);box-shadow:var(--shadow-sm)}
    .teacher-monitor-panel.present{border-top:3px solid var(--green)}.teacher-monitor-panel.late{border-top:3px solid var(--amber)}.teacher-monitor-panel.absent{border-top:3px solid var(--red)}.teacher-monitor-panel.permission{border-top:3px solid var(--violet)}
    .teacher-monitor-panel header{display:flex;align-items:center;justify-content:space-between;padding:13px 16px;border-bottom:1px solid var(--border);background:var(--surface2)}
    .teacher-monitor-panel header span{font-size:14px;font-weight:800}.teacher-monitor-panel.present header span{color:var(--green)}.teacher-monitor-panel.late header span{color:var(--amber)}.teacher-monitor-panel.absent header span{color:var(--red)}.teacher-monitor-panel.permission header span{color:var(--violet)}
    .teacher-monitor-panel header strong{min-width:28px;text-align:center;border-radius:999px;padding:2px 8px;font-size:11px;background:var(--surface);border:1px solid var(--border);color:var(--text)}
    .teacher-monitor-list{padding:12px;display:grid;gap:8px;max-height:360px;overflow:auto}
    .monitor-card{display:flex;align-items:center;gap:11px;border:1px solid var(--border);border-radius:10px;background:var(--surface);padding:10px;cursor:pointer;transition:background .15s,border-color .15s,transform .15s}
    .monitor-card:hover{background:var(--surface2);border-color:var(--border2);transform:translateY(-1px)}
    .monitor-card.is-new{animation:monitorFlash 1.3s ease}
    .monitor-avatar{width:38px;height:38px;border-radius:50%;display:grid;place-items:center;font-size:12px;font-weight:900;flex:0 0 auto}
    .monitor-main{min-width:0;flex:1}.monitor-main strong{display:block;color:var(--text);font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.monitor-main span{display:block;margin-top:2px;color:var(--muted);font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .monitor-meta{text-align:right;font-size:11px;color:var(--text2);font-family:var(--font-mono)}.monitor-meta em{display:block;margin-top:3px;font-style:normal;color:var(--muted)}
    .monitor-empty{border:1px dashed var(--border2);border-radius:10px;padding:24px;text-align:center;color:var(--muted);font-size:12px;background:var(--surface2)}
    .teacher-monitor-footer{text-align:center;color:var(--muted2);font-size:11px;padding-bottom:4px}
    .teacher-monitor-modal{position:fixed;inset:0;z-index:100;display:none;align-items:center;justify-content:center;background:rgba(15,23,42,.58);padding:16px}.teacher-monitor-modal.is-open{display:flex}
    .teacher-monitor-modal-card{position:relative;width:min(420px,100%);border:1px solid var(--border);border-radius:16px;background:var(--surface);padding:18px;color:var(--text);box-shadow:var(--shadow-lg)}
    .teacher-monitor-modal-card>button{position:absolute;right:12px;top:10px;border:0;background:transparent;color:var(--muted);font-size:26px;cursor:pointer}.teacher-monitor-modal-card>button:hover{color:var(--text)}
    .teacher-monitor-modal-head{display:flex;align-items:center;gap:12px;margin-bottom:14px}.teacher-monitor-modal-head div:first-child{width:48px;height:48px;border-radius:50%;display:grid;place-items:center;font-weight:900}.teacher-monitor-modal-head h2{margin:0;color:var(--text);font-size:17px}.teacher-monitor-modal-head p{margin:3px 0 0;color:var(--muted);font-size:12px}.teacher-monitor-modal-head span{margin-left:auto;border-radius:8px;padding:5px 9px;font-size:11px;font-weight:900;text-transform:uppercase}
    .teacher-monitor-modal-card dl{display:grid;gap:0;margin:0}.teacher-monitor-modal-card dl div{display:flex;align-items:center;justify-content:space-between;gap:12px;border-top:1px solid var(--border);padding:10px 0}.teacher-monitor-modal-card dt{color:var(--muted);font-size:12px}.teacher-monitor-modal-card dd{margin:0;text-align:right;color:var(--text2);font-size:12px;font-family:var(--font-mono)}
    @keyframes monitorPulse{0%,100%{opacity:1}50%{opacity:.28}}@keyframes monitorSpin{to{transform:rotate(360deg)}}@keyframes monitorFlash{0%{box-shadow:0 0 0 0 rgba(59,130,246,.8)}100%{box-shadow:0 0 0 12px rgba(59,130,246,0)}}@media(max-width:1100px){.teacher-monitor-stats{grid-template-columns:repeat(3,1fr)}.teacher-monitor-grid{grid-template-columns:1fr}}@media(max-width:700px){.teacher-monitor{padding:14px}.teacher-monitor-stats{grid-template-columns:repeat(2,1fr)}.teacher-monitor-controls{width:100%}.teacher-monitor-controls input,.teacher-monitor-controls select{flex:1;min-width:135px}.teacher-monitor-title h1{font-size:18px}}
</style>
@endpush

@push('scripts')
<script>
    window.teacherMonitorInitial = @json($initialPayload);
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const state = {
        payload: window.teacherMonitorInitial || { stats: {}, sessions: [], departments: [] },
        filtered: [],
        seen: new Set(),
        date: document.querySelector('.teacher-monitor').dataset.date,
    };

    const els = {
        shift: document.getElementById('monitorShift'),
        department: document.getElementById('monitorDepartment'),
        search: document.getElementById('monitorSearch'),
        refresh: document.getElementById('monitorRefresh'),
        refreshIcon: document.getElementById('monitorRefreshIcon'),
        clock: document.getElementById('monitorClock'),
        lists: {
            present: document.getElementById('listPresent'),
            late: document.getElementById('listLate'),
            absent: document.getElementById('listAbsent'),
            permission: document.getElementById('listPermission'),
        },
    };

    const colors = {
        present: { text: 'var(--green)', bg: 'rgba(34,197,94,.12)', border: 'rgba(34,197,94,.32)' },
        late: { text: 'var(--amber)', bg: 'rgba(245,158,11,.14)', border: 'rgba(245,158,11,.35)' },
        absent: { text: 'var(--red)', bg: 'rgba(239,68,68,.12)', border: 'rgba(239,68,68,.32)' },
        permission: { text: 'var(--violet)', bg: 'rgba(139,92,246,.12)', border: 'rgba(139,92,246,.32)' },
    };

    function updateClock() {
        els.clock.textContent = new Date().toLocaleTimeString([], { hour12: false });
    }

    function setText(id, value) {
        const node = document.getElementById(id);
        if (node) node.textContent = value;
    }

    function renderDepartments() {
        const current = els.department.value;
        els.department.innerHTML = '<option value="all">All departments</option>';
        (state.payload.departments || []).forEach((department) => {
            const option = document.createElement('option');
            option.value = department.toLowerCase().replaceAll(' ', '-');
            option.textContent = department;
            els.department.appendChild(option);
        });
        els.department.value = [...els.department.options].some((option) => option.value === current) ? current : 'all';
    }

    function renderStats() {
        const stats = state.payload.stats || {};
        setText('statTotal', stats.total || 0);
        setText('statPresent', stats.present || 0);
        setText('statLate', stats.late || 0);
        setText('statAbsent', stats.absent || 0);
        setText('statPermission', stats.permission || 0);
        setText('statPresentPct', `${stats.present_pct || 0}%`);
        setText('statLatePct', `${stats.late_pct || 0}%`);
        setText('statAbsentPct', `${stats.absent_pct || 0}%`);
        setText('statPermissionPct', `${stats.permission_pct || 0}%`);
    }

    function applyFilters() {
        const q = els.search.value.trim().toLowerCase();
        const shift = els.shift.value;
        const department = els.department.value;
        state.filtered = (state.payload.sessions || []).filter((item) => {
            const matchesSearch = !q || [item.teacher_name, item.subject, item.class_name, item.department].join(' ').toLowerCase().includes(q);
            const matchesShift = shift === 'all' || item.shift === shift;
            const matchesDepartment = department === 'all' || item.department_key === department;
            return matchesSearch && matchesShift && matchesDepartment;
        });
        renderLists();
    }

    function renderLists() {
        ['present', 'late', 'absent', 'permission'].forEach((group) => {
            const items = state.filtered.filter((item) => item.group === group);
            setText(`count${group[0].toUpperCase()}${group.slice(1)}`, items.length);
            els.lists[group].innerHTML = items.length ? items.map(cardHtml).join('') : `<div class="monitor-empty">No ${group} teachers</div>`;
        });

        document.querySelectorAll('[data-monitor-session]').forEach((card) => {
            card.addEventListener('click', () => openModal(Number(card.dataset.monitorSession)));
        });
    }

    function cardHtml(item) {
        const groupColor = colors[item.group] || colors.absent;
        const isNew = state.seen.has(item.id) ? '' : ' is-new';
        state.seen.add(item.id);
        const time = item.check_in_time || item.scheduled_start_time || '--:--';
        const method = item.scan_method ? item.scan_method.replaceAll('_', ' ') : 'waiting';
        return `
            <article class="monitor-card${isNew}" data-monitor-session="${item.id}">
                <div class="monitor-avatar" style="background:${groupColor.bg};color:${groupColor.text};border:1px solid ${groupColor.border}">${escapeHtml(item.teacher_initials || 'T')}</div>
                <div class="monitor-main">
                    <strong>${escapeHtml(item.teacher_name)}</strong>
                    <span>${escapeHtml(item.subject)} · Session ${item.session_number} · ${escapeHtml(item.class_name)}</span>
                </div>
                <div class="monitor-meta">
                    ${escapeHtml(time)}
                    <em>${escapeHtml(method)}</em>
                </div>
            </article>
        `;
    }

    function openModal(id) {
        const item = (state.payload.sessions || []).find((session) => session.id === id);
        if (!item) return;
        const groupColor = colors[item.group] || colors.absent;
        document.getElementById('modalAvatar').style.background = groupColor.bg;
        document.getElementById('modalAvatar').style.color = groupColor.text;
        document.getElementById('modalAvatar').style.border = `1px solid ${groupColor.border}`;
        document.getElementById('modalAvatar').textContent = item.teacher_initials || 'T';
        document.getElementById('modalName').textContent = item.teacher_name;
        document.getElementById('modalDept').textContent = item.department;
        document.getElementById('modalBadge').textContent = item.status.replaceAll('_', ' ');
        document.getElementById('modalBadge').style.background = groupColor.bg;
        document.getElementById('modalBadge').style.color = groupColor.text;
        document.getElementById('modalSubject').textContent = item.subject;
        document.getElementById('modalClass').textContent = item.class_name;
        document.getElementById('modalSchedule').textContent = `${item.scheduled_start_time || '--:--'} - ${item.scheduled_end_time || '--:--'}`;
        document.getElementById('modalShift').textContent = item.shift;
        document.getElementById('modalIn').textContent = item.check_in_time || '-';
        document.getElementById('modalOut').textContent = item.check_out_time || '-';
        document.getElementById('modalLate').textContent = item.late_minutes ? `${item.late_minutes}m` : '-';
        document.getElementById('modalMethod').textContent = item.scan_method ? item.scan_method.replaceAll('_', ' ') : '-';
        document.getElementById('teacherMonitorModal').classList.add('is-open');
    }

    function closeModal() {
        document.getElementById('teacherMonitorModal').classList.remove('is-open');
    }

    async function loadData() {
        els.refresh.classList.add('is-loading');
        try {
            const response = await fetch(`{{ route('admin.teacher-attendance.scan-monitor.data') }}?date=${encodeURIComponent(state.date)}`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (!response.ok) return;
            state.payload = await response.json();
            renderDepartments();
            renderStats();
            applyFilters();
        } finally {
            els.refresh.classList.remove('is-loading');
        }
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
    }

    els.shift.addEventListener('change', applyFilters);
    els.department.addEventListener('change', applyFilters);
    els.search.addEventListener('input', applyFilters);
    els.refresh.addEventListener('click', loadData);
    document.getElementById('teacherMonitorModalClose').addEventListener('click', closeModal);
    document.getElementById('teacherMonitorModal').addEventListener('click', (event) => {
        if (event.target.id === 'teacherMonitorModal') closeModal();
    });

    updateClock();
    setInterval(updateClock, 1000);
    renderDepartments();
    renderStats();
    applyFilters();

    if (window.Echo) {
        window.Echo.channel(`teacher-attendance.${state.date}`)
            .listen('.teacher.attendance.updated', loadData);
    }

    setInterval(loadData, 10000);
});
</script>
@endpush
