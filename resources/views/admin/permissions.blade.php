@extends('layouts.app')

@section('content')

    {{-- ═══ ASSIGN PERMISSION MODAL ═══ --}}
    <div id="permissionModal" class="modal-overlay">
        <div class="modal-box" style="max-width:540px;">
            <div class="modal-head">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:38px;height:38px;border-radius:12px;background:var(--accent)18;color:var(--accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="modal-title">Assign Student Permission</span>
                        <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.1em;margin-top:2px;">EXCUSED ABSENCE / LEAVE MANAGEMENT</div>
                    </div>
                </div>
                <button onclick="closeModal('permissionModal')" class="modal-close">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf
                <div class="modal-body">

                    <div class="form-group">
                        <label class="form-label">Select Student <span class="req">*</span></label>
                        <select name="student_id" class="form-input" required>
                            <option value="">Choose a student…</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}">{{ $s->user->name }} — {{ $s->student_code }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Start Date <span class="req">*</span></label>
                            <input name="start_date" class="form-input" type="date" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date <span class="req">*</span></label>
                            <input name="end_date" class="form-input" type="date" required value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Permission Type</label>
                        <select name="type" class="form-input">
                            <option value="sick">Sick Leave</option>
                            <option value="event">School Event</option>
                            <option value="personal">Personal Reason</option>
                            <option value="official">Official Duty</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Reason / Notes <span class="req">*</span></label>
                        <textarea name="reason" class="form-input" required placeholder="Briefly explain the reason for this permission…"
                            style="min-height:90px;resize:vertical;"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeModal('permissionModal')" class="btn-secondary">CANCEL</button>
                    <button type="submit" class="btn-primary">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        ASSIGN PERMISSION
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ PAGE HEADER ═══ --}}
    <div class="page-header">
        <div>
            <div class="breadcrumb">
                <span>MANAGEMENT</span>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current">PERMISSIONS</span>
            </div>
            <h1 class="page-title">Student Permissions</h1>
            <p class="page-subtitle">EXCUSED ABSENCES &amp; OFFICIAL LEAVES</p>
        </div>
        <button onclick="openModal('permissionModal')" class="btn-primary" style="gap:7px;">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            ASSIGN PERMISSION
        </button>
    </div>

    {{-- ═══ SUCCESS FLASH ═══ --}}
    @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--radius-md);background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);color:var(--green);font-family:var(--font-mono);font-size:10px;font-weight:700;letter-spacing:.08em;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ═══ PERMISSIONS TABLE ═══ --}}
    <div class="panel">

        {{-- Toolbar --}}
        <div class="catalog-toolbar">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:7px;height:7px;border-radius:50%;background:var(--amber);box-shadow:0 0 8px var(--amber);animation:blink 2s infinite;"></div>
                <span style="font-family:var(--font-mono);font-size:10px;letter-spacing:.12em;color:var(--muted2);">ACTIVE PERMISSIONS</span>
            </div>

            <div class="search-wrap">
                <form action="" method="GET" style="display:contents;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input class="search-input" type="text" name="search"
                        placeholder="Search student name or code…"
                        value="{{ request('search') }}"
                        onchange="this.form.submit()">
                </form>
            </div>

            <div class="toolbar-count">
                <span>{{ $permissions->total() ?? $permissions->count() }}</span> RECORDS
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="att-table" id="permissionsTable">
                <thead>
                    <tr>
                        <th>STUDENT</th>
                        <th>PERMISSION TYPE</th>
                        <th>DURATION</th>
                        <th>REASON</th>
                        <th>ISSUED BY</th>
                        <th style="text-align:right;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $p)
                        @php
                            $typeColors = [
                                'sick'     => ['bg'=>'rgba(239,68,68,.1)',   'color'=>'var(--red)',    'border'=>'rgba(239,68,68,.25)'],
                                'event'    => ['bg'=>'rgba(37,99,235,.1)',   'color'=>'var(--accent)', 'border'=>'rgba(37,99,235,.25)'],
                                'personal' => ['bg'=>'rgba(139,92,246,.1)', 'color'=>'var(--violet)', 'border'=>'rgba(139,92,246,.25)'],
                                'official' => ['bg'=>'rgba(16,185,129,.1)', 'color'=>'var(--emerald)','border'=>'rgba(16,185,129,.25)'],
                            ];
                            $tc = $typeColors[$p->type] ?? $typeColors['personal'];
                            $typeLabel = [
                                'sick'     => 'Sick Leave',
                                'event'    => 'School Event',
                                'personal' => 'Personal',
                                'official' => 'Official Duty',
                            ][$p->type] ?? strtoupper($p->type);

                            $avatarColors = ['#2563EB','#22C55E','#8B5CF6','#F59E0B','#10B981','#EF4444'];
                            $clr = $avatarColors[$p->id % count($avatarColors)];

                            $start  = \Carbon\Carbon::parse($p->start_date);
                            $end    = \Carbon\Carbon::parse($p->end_date);
                            $days   = $start->diffInDays($end) + 1;
                            $isActive = $end->isFuture() || $end->isToday();
                        @endphp
                        <tr class="fade-up">
                            {{-- Student --}}
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-avatar"
                                        style="background:{{ $clr }}22;color:{{ $clr }};border:1px solid {{ $clr }}44;font-size:10px;width:36px;height:36px;border-radius:50%;">
                                        {{ strtoupper(substr($p->student->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="subject-name">{{ $p->student->user->name }}</div>
                                        <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.05em;">
                                            {{ $p->student->student_code }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Type badge --}}
                            <td>
                                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:99px;font-family:var(--font-mono);font-size:9px;font-weight:800;letter-spacing:.08em;background:{{ $tc['bg'] }};color:{{ $tc['color'] }};border:1px solid {{ $tc['border'] }};">
                                    {{ strtoupper($typeLabel) }}
                                </span>
                            </td>

                            {{-- Duration --}}
                            <td>
                                <div style="font-size:12px;font-weight:600;color:var(--text2);">
                                    {{ $start->format('M d') }} – {{ $end->format('M d, Y') }}
                                </div>
                                <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);margin-top:3px;display:flex;align-items:center;gap:5px;">
                                    <span>{{ $days }} {{ Str::plural('day', $days) }}</span>
                                    <span style="opacity:.3">·</span>
                                    @if($isActive)
                                        <span style="color:var(--green);font-weight:700;">ACTIVE</span>
                                    @else
                                        <span style="color:var(--muted2);">EXPIRED</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Reason --}}
                            <td style="max-width:220px;">
                                <div style="font-size:12px;color:var(--text2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $p->reason }}">
                                    {{ $p->reason }}
                                </div>
                            </td>

                            {{-- Issued By --}}
                            <td>
                                <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.05em;">
                                    {{ $p->createdBy->name ?? 'SYSTEM' }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td style="text-align:right;">
                                <form action="{{ route('admin.permissions.destroy', $p->id) }}" method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Revoke this permission? The student\'s absence will become unexcused.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn btn-del" title="Revoke Permission">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="empty-title">No Active Permissions</div>
                                    <div class="empty-desc">Assigned excused absences and leave records will appear here.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($permissions instanceof \Illuminate\Pagination\LengthAwarePaginator && $permissions->hasPages())
            <div style="padding:12px 18px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted);letter-spacing:.08em;">
                    SHOWING {{ $permissions->firstItem() }}–{{ $permissions->lastItem() }} OF {{ $permissions->total() }}
                </span>
                {{ $permissions->links('vendor.pagination.academy') }}
            </div>
        @endif
    </div>

    <script>
        function openModal(id) {
            const m = document.getElementById(id);
            m.classList.add('open');
        }
        function closeModal(id) {
            const m = document.getElementById(id);
            m.classList.remove('open');
        }
        document.querySelectorAll('.modal-overlay').forEach(el => {
            el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
        });
    </script>

@endsection
