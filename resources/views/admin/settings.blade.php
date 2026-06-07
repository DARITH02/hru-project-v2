@extends('layouts.app')

@section('content')

{{-- ═══ TOAST ═══ --}}
<div id="toast" class="toast">
    <div id="toastIcon" class="toast-icon">✓</div>
    <span id="toastMsg">Settings Saved Successfully</span>
</div>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <span>ADMIN</span>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">SYSTEM SETTINGS</span>
        </div>
        <h1 class="page-title">Global Configuration</h1>
        <p class="page-subtitle">MANAGE SYSTEM PARAMETERS & BRANDING</p>
    </div>
</div>

<div class="main-grid" style="grid-template-columns: 1fr 350px;">
    
    <div style="display:flex; flex-direction:column; gap:20px;">
        
        {{-- General Settings --}}
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="panel">
                <div class="panel-head" style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 10px var(--accent)"></div>
                    <span style="font-family: var(--font-mono); font-size: 10px; font-weight: 700; letter-spacing: .12em; color: var(--text2)">BRANDING & IDENTITY</span>
                </div>
                <div class="panel-body" style="padding: 24px;">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">System Identity (Name)</label>
                            <input name="app_name" class="form-input" type="text" value="{{ $settings['app_name'] ?? 'AttendAI' }}" placeholder="e.g. My University Attendance">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Branding Sub-text</label>
                            <input name="app_sub" class="form-input" type="text" value="{{ $settings['app_sub'] ?? 'MANAGEMENT SYSTEM' }}" placeholder="e.g. UNIVERSITY SYSTEM">
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:10px">
                        <label class="form-label">Sidebar Logo</label>
                        <div style="display:flex; align-items:center; gap:15px;">
                            <div style="width:60px; height:60px; border-radius:var(--radius-md); background:var(--surface3); border:1px solid var(--border2); display:flex; align-items:center; justify-content:center; overflow:hidden">
                                @if(isset($settings['app_logo']))
                                    <img src="{{ $settings['app_logo'] }}" style="width:100%; height:100%; object-fit:contain">
                                @else
                                    <div style="width:10px; height:10px; border-radius:50%; background:var(--accent); box-shadow:0 0 10px var(--accent)"></div>
                                @endif
                            </div>
                            <div style="flex:1">
                                <input name="app_logo" class="form-input" type="file" accept="image/*">
                                <p style="font-size:9px; color:var(--muted); margin-top:6px; font-family:var(--font-mono)">UPLOAD A SYSTEM LOGO TO REPLACE THE DEFAULT BRAND DOT.</p>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:20px; border-top:1px solid var(--border); padding-top:20px;">
                        <button type="submit" class="btn-primary" style="width:200px; height:42px; font-weight:700; letter-spacing:.05em;">
                            SAVE BRANDING
                        </button>
                    </div>
                </div>
            </div>
        </form>


        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="panel">
                <div class="panel-head" style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--green); box-shadow: 0 0 10px var(--green)"></div>
                    <span style="font-family: var(--font-mono); font-size: 10px; font-weight: 700; letter-spacing: .12em; color: var(--text2)">GEOFENCING & LOCATION NODES</span>
                </div>
                <div class="panel-body" style="padding: 24px;">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Campus Latitude</label>
                            <input name="campus_lat" class="form-input" type="text" value="{{ $settings['campus_lat'] ?? '11.524012' }}" placeholder="11.524012">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Campus Longitude</label>
                            <input name="campus_lng" class="form-input" type="text" value="{{ $settings['campus_lng'] ?? '104.876273' }}" placeholder="104.876273">
                        </div>
                    </div>
                    <div class="form-grid-2" style="margin-top:10px">
                        <div class="form-group">
                            <label class="form-label">Verification Radius (Meters)</label>
                            <input name="campus_radius_meters" class="form-input" type="number" value="{{ $settings['campus_radius_meters'] ?? '250' }}" placeholder="250">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Require Location Tracking</label>
                            <select name="require_location" class="form-input">
                                <option value="true" {{ ($settings['require_location'] ?? 'true') === 'true' ? 'selected' : '' }}>ENABLED (STRICT)</option>
                                <option value="false" {{ ($settings['require_location'] ?? 'true') === 'false' ? 'selected' : '' }}>DISABLED (NOT RECOMMENDED)</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top:20px; border-top:1px solid var(--border); padding-top:20px;">
                        <button type="submit" class="btn-primary" style="width:200px; height:42px; font-weight:700; background:var(--green); border:none; letter-spacing:.05em;">
                            SAVE GEOFENCE
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="panel">
            <div class="panel-head" style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: #0088cc; box-shadow: 0 0 10px #0088cc"></div>
                <span style="font-family: var(--font-mono); font-size: 10px; font-weight: 700; letter-spacing: .12em; color: var(--text2)">TELEGRAM NOTIFICATION ENGINE</span>
            </div>
            <div class="panel-body" style="padding: 24px;">
                <p style="font-size:11px; color:var(--text); margin-bottom:20px; line-height:1.6">
                    REGISTER NEW BOTS TO ENABLE SYSTEM ALERTS. <br>
                    <span style="color:var(--muted); font-size:9px;">NOTE: MESSAGE THE BOT ON TELEGRAM BEFORE ADDING IT TO DETECT THE CHAT ID AUTOMATICALLY.</span>
                </p>
                
                <div style="background:var(--surface2); padding:20px; border-radius:12px; border:1px solid var(--border); margin-bottom:24px;">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Bot Identifier (Name)</label>
                            <input id="new_bot_name" class="form-input" type="text" placeholder="e.g. Master Alert Bot">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bot API Token</label>
                            <input id="new_bot_token" class="form-input" type="password" placeholder="123456789:ABCDefgh...">
                        </div>
                    </div>
                    <button type="button" onclick="addNewBot()" class="btn-primary" style="margin-top:15px; height:36px; padding:0 20px; font-size:10px; background:#0088cc; border:none">
                        REGISTER & SYNC BOT
                    </button>
                </div>

                {{-- Bots List Table --}}
                <div style="margin-top:30px">
                    <div style="font-family:var(--font-mono); font-size:9px; color:var(--muted2); font-weight:700; letter-spacing:.1em; margin-bottom:12px">REGISTERED BOT KERNELS</div>
                    <div style="overflow-x:auto">
                        <table style="width:100%; border-collapse:collapse; font-size:11px;">
                            <thead>
                                <tr style="text-align:left; color:var(--muted); border-bottom:1px solid var(--border)">
                                    <th style="padding:12px 10px; font-weight:600">BOT NAME</th>
                                    <th style="padding:12px 10px; font-weight:600">CHAT ID</th>
                                    <th style="padding:12px 10px; font-weight:600">STATUS</th>
                                    <th style="padding:12px 10px; font-weight:600; text-align:right">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bots as $bot)
                                <tr style="border-bottom:1px solid var(--border2); transition:background .2s" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding:14px 10px;">
                                        <div style="font-weight:700; color:var(--text)">{{ $bot->name }}</div>
                                        <div style="font-size:9px; color:var(--muted); font-family:var(--font-mono)">****{{ substr($bot->bot_token, -6) }}</div>
                                    </td>
                                    <td style="padding:14px 10px;">
                                        @if($bot->chat_id)
                                            <code style="background:var(--surface3); padding:4px 8px; border-radius:4px; color:var(--accent); font-weight:700">{{ $bot->chat_id }}</code>
                                        @else
                                            <span style="color:var(--muted); font-style:italic">NOT SYNCED</span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 10px;">
                                        @if($bot->is_active)
                                            <span style="display:inline-flex; align-items:center; gap:5px; background:rgba(16, 185, 129, 0.1); color:#10b981; padding:4px 10px; border-radius:20px; font-size:9px; font-weight:700">
                                                <span style="width:5px; height:5px; background:#10b981; border-radius:50%"></span> ACTIVE
                                            </span>
                                        @else
                                            <span style="color:var(--muted); font-size:9px; font-weight:600">STANDBY</span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 10px; text-align:right">
                                        <div style="display:flex; justify-content:flex-end; gap:8px">
                                            @if(!$bot->is_active)
                                            <button type="button" onclick="botAction('{{ route('admin.telegram-bots.active', $bot->id) }}')" title="Activate" style="background:none; border:none; color:var(--muted); cursor:pointer"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                                            @endif
                                            <button type="button" onclick="botAction('{{ route('admin.telegram-bots.sync', $bot->id) }}')" title="Sync Chat ID" style="background:none; border:none; color:var(--accent); cursor:pointer"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                                            <button type="button" onclick="botAction('{{ route('admin.telegram-bots.test', $bot->id) }}')" title="Send Test" @if(!$bot->chat_id) disabled style="opacity:.3; cursor:not-allowed" @endif style="background:none; border:none; color:#0088cc; cursor:pointer"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                                            <button type="button" onclick="if(confirm('Delete bot?')) botAction('{{ route('admin.telegram-bots.destroy', $bot->id) }}', 'DELETE')" title="Delete" style="background:none; border:none; color:var(--red); cursor:pointer"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" style="padding:40px; text-align:center; color:var(--muted); font-size:10px; letter-spacing:.05em">NO TELEGRAM BOTS DETECTED IN SYSTEM KERNEL.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Data Export & Reports --}}
        <div class="panel">
            <div class="panel-head" style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--green); box-shadow: 0 0 10px var(--green)"></div>
                <span style="font-family: var(--font-mono); font-size: 10px; font-weight: 700; letter-spacing: .12em; color: var(--text2)">SYSTEM DATA EXPORTS</span>
            </div>
            <div class="panel-body" style="padding: 24px;">
                <p style="font-size:11px; color:var(--text); margin-bottom:20px; line-height:1.6">
                    GENERATE COMPREHENSIVE ATTENDANCE SUMMARIES FOR ALL SUBJECTS AND CLASSES.
                </p>
                
                <div style="background:var(--surface2); padding:15px; border-radius:12px; border:1px solid var(--border); margin-bottom:15px;">
                    <div style="font-size:10px; font-weight:700; color:var(--text2); margin-bottom:12px; font-family:var(--font-mono)">1. CONFIGURE SCOPE</div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Academic Year</label>
                            <select id="report_year" class="form-input">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Semester</label>
                            <select id="report_semester" class="form-input">
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:12px;">
                    {{-- Full Semester --}}
                    <div style="display:flex; align-items:center; gap:12px; background:var(--surface3); padding:12px 15px; border-radius:10px; border:1px solid var(--border)">
                        <div style="flex:1">
                            <div style="font-size:11px; font-weight:700; color:var(--text)">FULL SEMESTER</div>
                            <div style="font-size:9px; color:var(--muted)">COMPLETE TERM SUMMARY</div>
                        </div>
                        <div style="display:flex; gap:8px">
                            <button type="button" onclick="triggerExport('full', 'download')" class="btn-primary" style="height:32px; font-size:9px; width:90px; background:var(--surface2); border:1px solid var(--border); color:var(--text)">DOWNLOAD</button>
                            <button type="button" onclick="triggerExport('full', 'telegram')" class="btn-primary" style="height:32px; font-size:9px; width:90px; background:#0088cc; border:none; color:white">TELEGRAM</button>
                        </div>
                    </div>

                    {{-- Half Semester --}}
                    <div style="display:flex; align-items:center; gap:12px; background:var(--surface3); padding:12px 15px; border-radius:10px; border:1px solid var(--border)">
                        <div style="flex:1">
                            <div style="font-size:11px; font-weight:700; color:var(--text)">HALF SEMESTER</div>
                            <div style="font-size:9px; color:var(--muted)">MID-TERM SNAPSHOT</div>
                        </div>
                        <div style="display:flex; gap:8px">
                            <button type="button" onclick="triggerExport('half', 'download')" class="btn-primary" style="height:32px; font-size:9px; width:90px; background:var(--surface2); border:1px solid var(--border); color:var(--text)">DOWNLOAD</button>
                            <button type="button" onclick="triggerExport('half', 'telegram')" class="btn-primary" style="height:32px; font-size:9px; width:90px; background:#0088cc; border:none; color:white">TELEGRAM</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Right Sidebar --}}
    <div style="display:flex; flex-direction:column; gap:16px;">
        <div class="side-panel" style="background:var(--surface2)">
            <div class="side-panel-head">
                <span style="width:6px;height:6px;border-radius:50%;background:var(--accent);display:inline-block"></span>
                SYSTEM NODE
            </div>
            <div style="padding: 20px;">
                <p style="font-size:10px; color:var(--muted); line-height:1.5; margin-bottom:15px;">
                    YOU ARE ACCESSING THE CORE SYSTEM CONFIGURATION. CHANGES HERE AFFECT ALL CONNECTED NODES AND USER INTERFACES.
                </p>
                <div style="background:var(--surface1); padding:10px; border-radius:8px; border:1px solid var(--border); display:flex; align-items:center; gap:10px;">
                    <div style="width:6px;height:6px;border-radius:50%;background:var(--green)"></div>
                    <div style="font-family:var(--font-mono);font-size:9px;color:var(--text);font-weight:700">OPTIMIZED KERNEL</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showToast(msg, type = 'success') {
        const t = document.getElementById('toast');
        const ic = document.getElementById('toastIcon');
        const tx = document.getElementById('toastMsg');
        if (!t) return;
        
        t.className = `toast show toast-${type}`;
        ic.textContent = type === 'success' ? '✓' : type === 'error' ? '✕' : 'i';
        tx.textContent = msg;
        
        setTimeout(() => t.classList.remove('show'), 3000);
    }

    function addNewBot() {
        const name = document.getElementById('new_bot_name').value;
        const token = document.getElementById('new_bot_token').value;
        
        if(!name || !token) {
            showToast('Please fill all bot fields.', 'error');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.telegram-bots.store") }}';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        
        const nameInput = document.createElement('input');
        nameInput.name = 'name';
        nameInput.value = name;
        
        const tokenInput = document.createElement('input');
        tokenInput.name = 'bot_token';
        tokenInput.value = token;
        
        form.appendChild(csrf);
        form.appendChild(nameInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }

    function botAction(url, method = 'POST') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        if(method !== 'POST') {
            const m = document.createElement('input');
            m.type = 'hidden';
            m.name = '_method';
            m.value = method;
            form.appendChild(m);
        }
        
        document.body.appendChild(form);
        form.submit();
    }

    function triggerExport(type, action) {
        const year = document.getElementById('report_year').value;
        const sem = document.getElementById('report_semester').value;
        
        let url = `{{ route('admin.settings.export') }}?academic_year=${encodeURIComponent(year)}&semester=${sem}&type=${type}&action=${action}`;
        
        if (action === 'download') {
            window.open(url, '_blank');
        } else {
            // Use a form to keep it in-page for the success message
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = '{{ route("admin.settings.export") }}';
            
            const params = { academic_year: year, semester: sem, type: type, action: action };
            for(let key in params) {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = key;
                inp.value = params[key];
                form.appendChild(inp);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

@if(session('success'))
<script>
    window.onload = () => showToast('{{ session("success") }}', 'success');
</script>
@endif

@endsection
