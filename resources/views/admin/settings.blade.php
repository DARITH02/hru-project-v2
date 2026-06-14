@extends('layouts.app')

@section('content')

{{-- ═══ TOAST ═══ --}}
<div id="toast" class="toast">
    <div id="toastIcon" class="toast-icon">✓</div>
    <span id="toastMsg">{{ __('admin_settings.toast_saved') }}</span>
</div>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <span>{{ __('admin_settings.breadcrumb_admin') }}</span>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">{{ __('admin_settings.breadcrumb_settings') }}</span>
        </div>
        <h1 class="page-title">{{ __('admin_settings.title') }}</h1>
        <p class="page-subtitle">{{ __('admin_settings.subtitle') }}</p>
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
                    <span style="font-family: var(--font-mono); font-size: 10px; font-weight: 700; letter-spacing: .12em; color: var(--text2)">{{ __('admin_settings.branding_identity') }}</span>
                </div>
                <div class="panel-body" style="padding: 24px;">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_settings.system_identity') }}</label>
                            <input name="app_name" class="form-input" type="text" value="{{ $settings['app_name'] ?? 'AttendAI' }}" placeholder="{{ __('admin_settings.system_identity_placeholder') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_settings.branding_subtext') }}</label>
                            <input name="app_sub" class="form-input" type="text" value="{{ $settings['app_sub'] ?? 'MANAGEMENT SYSTEM' }}" placeholder="{{ __('admin_settings.branding_subtext_placeholder') }}">
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:10px">
                        <label class="form-label">{{ __('admin_settings.sidebar_logo') }}</label>
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
                                <p style="font-size:9px; color:var(--muted); margin-top:6px; font-family:var(--font-mono)">{{ __('admin_settings.logo_help') }}</p>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:20px; border-top:1px solid var(--border); padding-top:20px;">
                        <button type="submit" class="btn-primary" style="width:200px; height:42px; font-weight:700; letter-spacing:.05em;">
                            {{ __('admin_settings.save_branding') }}
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
                    <span style="font-family: var(--font-mono); font-size: 10px; font-weight: 700; letter-spacing: .12em; color: var(--text2)">{{ __('admin_settings.geofence_title') }}</span>
                </div>
                <div class="panel-body" style="padding: 24px;">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_settings.campus_latitude') }}</label>
                            <input name="campus_lat" class="form-input" type="text" value="{{ $settings['campus_lat'] ?? '11.524012' }}" placeholder="11.524012">
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_settings.campus_longitude') }}</label>
                            <input name="campus_lng" class="form-input" type="text" value="{{ $settings['campus_lng'] ?? '104.876273' }}" placeholder="104.876273">
                        </div>
                    </div>
                    <div class="form-grid-2" style="margin-top:10px">
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_settings.verification_radius') }}</label>
                            <input name="campus_radius_meters" class="form-input" type="number" value="{{ $settings['campus_radius_meters'] ?? '250' }}" placeholder="250">
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_settings.require_location') }}</label>
                            <select name="require_location" class="form-input">
                                <option value="true" {{ ($settings['require_location'] ?? 'true') === 'true' ? 'selected' : '' }}>{{ __('admin_settings.enabled_strict') }}</option>
                                <option value="false" {{ ($settings['require_location'] ?? 'true') === 'false' ? 'selected' : '' }}>{{ __('admin_settings.disabled_not_recommended') }}</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top:20px; border-top:1px solid var(--border); padding-top:20px;">
                        <button type="submit" class="btn-primary" style="width:200px; height:42px; font-weight:700; background:var(--green); border:none; letter-spacing:.05em;">
                            {{ __('admin_settings.save_geofence') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="panel">
            <div class="panel-head" style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: #0088cc; box-shadow: 0 0 10px #0088cc"></div>
                <span style="font-family: var(--font-mono); font-size: 10px; font-weight: 700; letter-spacing: .12em; color: var(--text2)">{{ __('admin_settings.telegram_engine') }}</span>
            </div>
            <div class="panel-body" style="padding: 24px;">
                <p style="font-size:11px; color:var(--text); margin-bottom:20px; line-height:1.6">
                    {{ __('admin_settings.telegram_intro') }} <br>
                    <span style="color:var(--muted); font-size:9px;">{{ __('admin_settings.telegram_note') }}</span>
                </p>
                
                <div style="background:var(--surface2); padding:20px; border-radius:12px; border:1px solid var(--border); margin-bottom:24px;">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_settings.bot_identifier') }}</label>
                            <input id="new_bot_name" class="form-input" type="text" placeholder="{{ __('admin_settings.bot_name_placeholder') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_settings.bot_token') }}</label>
                            <input id="new_bot_token" class="form-input" type="password" placeholder="123456789:ABCDefgh...">
                        </div>
                    </div>
                    <button type="button" onclick="addNewBot()" class="btn-primary" style="margin-top:15px; height:36px; padding:0 20px; font-size:10px; background:#0088cc; border:none">
                        {{ __('admin_settings.register_sync_bot') }}
                    </button>
                </div>

                {{-- Bots List Table --}}
                <div style="margin-top:30px">
                    <div style="font-family:var(--font-mono); font-size:9px; color:var(--muted2); font-weight:700; letter-spacing:.1em; margin-bottom:12px">{{ __('admin_settings.registered_bots') }}</div>
                    <div style="overflow-x:auto">
                        <table style="width:100%; border-collapse:collapse; font-size:11px;">
                            <thead>
                                <tr style="text-align:left; color:var(--muted); border-bottom:1px solid var(--border)">
                                    <th style="padding:12px 10px; font-weight:600">{{ __('admin_settings.bot_name') }}</th>
                                    <th style="padding:12px 10px; font-weight:600">{{ __('admin_settings.chat_id') }}</th>
                                    <th style="padding:12px 10px; font-weight:600">{{ __('admin_settings.status') }}</th>
                                    <th style="padding:12px 10px; font-weight:600; text-align:right">{{ __('admin_settings.actions') }}</th>
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
                                            <span style="color:var(--muted); font-style:italic">{{ __('admin_settings.not_synced') }}</span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 10px;">
                                        @if($bot->is_active)
                                            <span style="display:inline-flex; align-items:center; gap:5px; background:rgba(16, 185, 129, 0.1); color:#10b981; padding:4px 10px; border-radius:20px; font-size:9px; font-weight:700">
                                                <span style="width:5px; height:5px; background:#10b981; border-radius:50%"></span> {{ __('admin_settings.active') }}
                                            </span>
                                        @else
                                            <span style="color:var(--muted); font-size:9px; font-weight:600">{{ __('admin_settings.standby') }}</span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 10px; text-align:right">
                                        <div style="display:flex; justify-content:flex-end; gap:8px">
                                            @if(!$bot->is_active)
                                            <button type="button" onclick="botAction('{{ route('admin.telegram-bots.active', $bot->id) }}')" title="{{ __('admin_settings.activate') }}" style="background:none; border:none; color:var(--muted); cursor:pointer"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                                            @endif
                                            <button type="button" onclick="botAction('{{ route('admin.telegram-bots.sync', $bot->id) }}')" title="{{ __('admin_settings.sync_chat_id') }}" style="background:none; border:none; color:var(--accent); cursor:pointer"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                                            <button type="button" onclick="botAction('{{ route('admin.telegram-bots.test', $bot->id) }}')" title="{{ __('admin_settings.send_test') }}" @if(!$bot->chat_id) disabled style="opacity:.3; cursor:not-allowed" @endif style="background:none; border:none; color:#0088cc; cursor:pointer"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                                            <button type="button" onclick="confirmAction(@js(__('admin_settings.delete_bot_confirm')), () => botAction('{{ route('admin.telegram-bots.destroy', $bot->id) }}', 'DELETE'))" title="{{ __('admin_settings.delete') }}" style="background:none; border:none; color:var(--red); cursor:pointer"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" style="padding:40px; text-align:center; color:var(--muted); font-size:10px; letter-spacing:.05em">{{ __('admin_settings.no_bots') }}</td>
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
                <span style="font-family: var(--font-mono); font-size: 10px; font-weight: 700; letter-spacing: .12em; color: var(--text2)">{{ __('admin_settings.data_exports') }}</span>
            </div>
            <div class="panel-body" style="padding: 24px;">
                <p style="font-size:11px; color:var(--text); margin-bottom:20px; line-height:1.6">
                    {{ __('admin_settings.exports_intro') }}
                </p>
                
                <div style="background:var(--surface2); padding:15px; border-radius:12px; border:1px solid var(--border); margin-bottom:15px;">
                    <div style="font-size:10px; font-weight:700; color:var(--text2); margin-bottom:12px; font-family:var(--font-mono)">{{ __('admin_settings.configure_scope') }}</div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_settings.academic_year') }}</label>
                            <select id="report_year" class="form-input">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('admin_settings.semester') }}</label>
                            <select id="report_semester" class="form-input">
                                <option value="1">{{ __('admin_settings.semester_1') }}</option>
                                <option value="2">{{ __('admin_settings.semester_2') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:12px;">
                    {{-- Full Semester --}}
                    <div style="display:flex; align-items:center; gap:12px; background:var(--surface3); padding:12px 15px; border-radius:10px; border:1px solid var(--border)">
                        <div style="flex:1">
                            <div style="font-size:11px; font-weight:700; color:var(--text)">{{ __('admin_settings.full_semester') }}</div>
                            <div style="font-size:9px; color:var(--muted)">{{ __('admin_settings.complete_term_summary') }}</div>
                        </div>
                        <div style="display:flex; gap:8px">
                            <button type="button" onclick="triggerExport('full', 'download')" class="btn-primary" style="height:32px; font-size:9px; width:90px; background:var(--surface2); border:1px solid var(--border); color:var(--text)">{{ __('admin_settings.download') }}</button>
                            <button type="button" onclick="triggerExport('full', 'telegram')" class="btn-primary" style="height:32px; font-size:9px; width:90px; background:#0088cc; border:none; color:white">{{ __('admin_settings.telegram') }}</button>
                        </div>
                    </div>

                    {{-- Half Semester --}}
                    <div style="display:flex; align-items:center; gap:12px; background:var(--surface3); padding:12px 15px; border-radius:10px; border:1px solid var(--border)">
                        <div style="flex:1">
                            <div style="font-size:11px; font-weight:700; color:var(--text)">{{ __('admin_settings.half_semester') }}</div>
                            <div style="font-size:9px; color:var(--muted)">{{ __('admin_settings.midterm_snapshot') }}</div>
                        </div>
                        <div style="display:flex; gap:8px">
                            <button type="button" onclick="triggerExport('half', 'download')" class="btn-primary" style="height:32px; font-size:9px; width:90px; background:var(--surface2); border:1px solid var(--border); color:var(--text)">{{ __('admin_settings.download') }}</button>
                            <button type="button" onclick="triggerExport('half', 'telegram')" class="btn-primary" style="height:32px; font-size:9px; width:90px; background:#0088cc; border:none; color:white">{{ __('admin_settings.telegram') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Right Sidebar --}}
    <div style="display:flex; flex-direction:column; gap:16px;">
        @if(auth()->user()?->isSuperAdmin())
            <div class="side-panel" style="background:var(--surface2); border-color:{{ ($maintenanceStatus['enabled'] ?? false) ? 'rgba(245,158,11,.45)' : 'var(--border)' }};">
                <div class="side-panel-head">
                    <span style="width:6px;height:6px;border-radius:50%;background:{{ ($maintenanceStatus['enabled'] ?? false) ? 'var(--amber)' : 'var(--green)' }};display:inline-block;box-shadow:0 0 10px {{ ($maintenanceStatus['enabled'] ?? false) ? 'rgba(245,158,11,.65)' : 'rgba(34,197,94,.55)' }}"></span>
                    {{ __('admin_settings.maintenance_control') }}
                </div>
                <div style="padding:20px;display:flex;flex-direction:column;gap:14px;">
                    <div style="border:1px solid var(--border);background:var(--surface);border-radius:8px;padding:12px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px;">
                            <span style="font-family:var(--font-mono);font-size:9px;color:var(--muted);font-weight:800;letter-spacing:.08em;text-transform:uppercase;">{{ __('admin_settings.current_status') }}</span>
                            @if($maintenanceStatus['enabled'] ?? false)
                                <span style="font-family:var(--font-mono);font-size:9px;color:var(--amber);font-weight:800;">{{ __('admin_settings.maintenance_active') }}</span>
                            @else
                                <span style="font-family:var(--font-mono);font-size:9px;color:var(--green);font-weight:800;">{{ __('admin_settings.maintenance_inactive') }}</span>
                            @endif
                        </div>
                        <p style="font-size:10px;color:var(--muted);line-height:1.55;">
                            {{ ($maintenanceStatus['enabled'] ?? false) ? __('admin_settings.maintenance_active_desc') : __('admin_settings.maintenance_inactive_desc') }}
                        </p>
                    </div>

                    @if($maintenanceStatus['enabled'] ?? false)
                        <form method="POST" action="{{ route('admin.settings.maintenance.disable') }}" onsubmit="return confirmSubmit(event, @js(__('admin_settings.disable_maintenance_confirm')));">
                            @csrf
                            <button type="submit" class="btn-primary" style="width:100%;height:38px;background:var(--green);border:none;font-weight:800;">
                                {{ __('admin_settings.disable_maintenance') }}
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.settings.maintenance.enable') }}" onsubmit="return confirmSubmit(event, @js(__('admin_settings.enable_maintenance_confirm')));">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">{{ __('admin_settings.maintenance_message') }}</label>
                                <textarea name="message" class="form-input" rows="4" maxlength="500" style="resize:vertical;min-height:92px;">{{ $maintenanceStatus['message'] ?? 'System maintenance is active. Please try again later.' }}</textarea>
                            </div>
                            <button type="submit" class="btn-primary" style="width:100%;height:38px;background:var(--amber);border:none;color:#111827;font-weight:900;">
                                {{ __('admin_settings.enable_maintenance') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        <div class="side-panel" style="background:var(--surface2)">
            <div class="side-panel-head">
                <span style="width:6px;height:6px;border-radius:50%;background:var(--accent);display:inline-block"></span>
                {{ __('admin_settings.system_node') }}
            </div>
            <div style="padding: 20px;">
                <p style="font-size:10px; color:var(--muted); line-height:1.5; margin-bottom:15px;">
                    {{ __('admin_settings.system_node_desc') }}
                </p>
                <div style="background:var(--surface1); padding:10px; border-radius:8px; border:1px solid var(--border); display:flex; align-items:center; gap:10px;">
                    <div style="width:6px;height:6px;border-radius:50%;background:var(--green)"></div>
                    <div style="font-family:var(--font-mono);font-size:9px;color:var(--text);font-weight:700">{{ __('admin_settings.optimized_kernel') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const settingsTranslations = @json(trans('admin_settings'));
    const settingsText = (key, fallback = key) => settingsTranslations[key] || fallback;

    function showToast(msg, type = 'success') {
        const t = document.getElementById('toast');
        const ic = document.getElementById('toastIcon');
        const tx = document.getElementById('toastMsg');
        if (!t) return;
        
        t.className = `toast show toast-${type}`;
        ic.textContent = type === 'success' ? '✓' : type === 'error' ? '✕' : 'i';
        tx.textContent = window.__t ? window.__t(msg) : msg;
        
        setTimeout(() => t.classList.remove('show'), 3000);
    }

    function addNewBot() {
        const name = document.getElementById('new_bot_name').value;
        const token = document.getElementById('new_bot_token').value;
        
        if(!name || !token) {
            showToast(settingsText('fill_bot_fields', 'Please fill all bot fields.'), 'error');
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
