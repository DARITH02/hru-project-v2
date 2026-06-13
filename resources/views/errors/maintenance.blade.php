<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>{{ __('admin_settings.maintenance_title') }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@300;400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            mono: ['IBM Plex Mono', 'monospace'],
            sans: ['IBM Plex Sans', 'sans-serif'],
          },
          colors: {
            bg: '#0a0c0f',
            panel: '#0f1318',
            border: '#1e2530',
            muted: '#1a2030',
            dim: '#8a9bb0',
            text: '#c8d8e8',
            bright: '#e8f2ff',
            amber: { DEFAULT: '#f5a623', dim: '#7a5010', glow: 'rgba(245,166,35,0.15)' },
            cyan: { DEFAULT: '#00d4ff', dim: '#004455', glow: 'rgba(0,212,255,0.12)' },
            red: { DEFAULT: '#ff4757', dim: '#4a0f14', glow: 'rgba(255,71,87,0.15)' },
            green: { DEFAULT: '#2ecc71', dim: '#0a3320', glow: 'rgba(46,204,113,0.12)' },
          }
        }
      }
    }
  </script>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: #0a0c0f; font-family: 'IBM Plex Mono', monospace; color: #c8d8e8; overflow-x: hidden; }
    body::before {
      content: '';
      position: fixed; inset: 0; z-index: 50; pointer-events: none;
      background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.03) 2px, rgba(0,0,0,0.03) 4px);
    }
    .grid-bg {
      background-image:
        linear-gradient(rgba(30,37,48,0.6) 1px, transparent 1px),
        linear-gradient(90deg, rgba(30,37,48,0.6) 1px, transparent 1px);
      background-size: 40px 40px;
    }
    .glow-amber { box-shadow: 0 0 20px rgba(245,166,35,0.2), inset 0 0 20px rgba(245,166,35,0.05); }
    .glow-cyan  { box-shadow: 0 0 20px rgba(0,212,255,0.15), inset 0 0 20px rgba(0,212,255,0.04); }
    .glow-red   { box-shadow: 0 0 20px rgba(255,71,87,0.2),  inset 0 0 20px rgba(255,71,87,0.05); }
    .text-glow-amber { text-shadow: 0 0 12px rgba(245,166,35,0.6); }
    .text-glow-cyan  { text-shadow: 0 0 12px rgba(0,212,255,0.5); }
    .text-glow-red   { text-shadow: 0 0 12px rgba(255,71,87,0.5); }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0} }
    .blink { animation: blink 1s step-end infinite; }
    @keyframes ping-slow { 0%{transform:scale(1);opacity:0.8} 100%{transform:scale(2.2);opacity:0} }
    .ping-slow { animation: ping-slow 2s cubic-bezier(0,0,0.2,1) infinite; }
    @keyframes spin-slow { to { transform: rotate(360deg); } }
    .spin-slow { animation: spin-slow 8s linear infinite; }
    @keyframes spin-rev { to { transform: rotate(-360deg); } }
    .spin-rev { animation: spin-rev 12s linear infinite; }
    @keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
    .shimmer {
      background: linear-gradient(90deg, #f5a623 0%, #ffe580 50%, #f5a623 100%);
      background-size: 200% 100%;
      animation: shimmer 2.4s ease-in-out infinite;
    }
    @keyframes slide-in { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
    .slide-in { animation: slide-in 0.5s ease forwards; }
    .delay-1 { animation-delay: 0.1s; opacity: 0; }
    .delay-2 { animation-delay: 0.2s; opacity: 0; }
    .delay-3 { animation-delay: 0.3s; opacity: 0; }
    .delay-4 { animation-delay: 0.4s; opacity: 0; }
    .delay-5 { animation-delay: 0.55s; opacity: 0; }
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: #0a0c0f; }
    ::-webkit-scrollbar-thumb { background: #1e2530; border-radius: 2px; }
    .task-row { transition: background 0.15s; }
    .task-row:hover { background: rgba(30,37,48,0.7); }
    .corner-tl::before, .corner-tl::after,
    .corner-br::before, .corner-br::after {
      content: ''; position: absolute; background: #f5a623;
    }
    .corner-tl::before { top: 0; left: 0; width: 12px; height: 1px; }
    .corner-tl::after  { top: 0; left: 0; width: 1px; height: 12px; }
    .corner-br::before { bottom: 0; right: 0; width: 12px; height: 1px; }
    .corner-br::after  { bottom: 0; right: 0; width: 1px; height: 12px; }
    .text-red-DEFAULT { color: #ff4757; }
    .bg-red-DEFAULT { background-color: #ff4757; }
    .border-red-DEFAULT { border-color: #ff4757; }
    .text-green-DEFAULT { color: #2ecc71; }
    .bg-green-DEFAULT { background-color: #2ecc71; }
    .text-cyan-DEFAULT { color: #00d4ff; }
    .bg-cyan-DEFAULT { background-color: #00d4ff; }
    @media (max-width: 1024px) {
      .maintenance-shell { padding: 1rem; }
      .maintenance-stats, .maintenance-main { grid-template-columns: 1fr; }
      .maintenance-task-panel { grid-column: span 1 / span 1; }
      header { align-items: flex-start; gap: 1rem; flex-direction: column; }
    }
  </style>
</head>
<body class="min-h-screen grid-bg">
  <div class="w-full bg-amber border-b border-amber-dim py-1.5 px-6 flex items-center gap-3">
    <div class="flex items-center gap-2 text-bg text-xs font-semibold tracking-widest uppercase">
      <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
      Scheduled maintenance in progress
    </div>
    <div class="ml-auto text-bg/70 text-xs font-mono">ETA: 02:45:00 remaining</div>
  </div>

  <header class="border-b border-border px-8 py-5 flex items-center justify-between slide-in">
    <div class="flex items-center gap-4">
      <div class="relative w-9 h-9">
        <svg class="w-9 h-9 spin-slow text-amber opacity-30" viewBox="0 0 36 36" fill="none" stroke="currentColor" stroke-width="1">
          <circle cx="18" cy="18" r="16"/><circle cx="18" cy="18" r="10"/>
          <line x1="18" y1="2" x2="18" y2="7"/><line x1="18" y1="29" x2="18" y2="34"/>
          <line x1="2" y1="18" x2="7" y2="18"/><line x1="29" y1="18" x2="34" y2="18"/>
        </svg>
        <svg class="absolute inset-0 w-9 h-9 spin-rev text-cyan opacity-20" viewBox="0 0 36 36" fill="none" stroke="currentColor" stroke-width="0.8">
          <circle cx="18" cy="18" r="13" stroke-dasharray="4 4"/>
        </svg>
        <div class="absolute inset-0 flex items-center justify-center"><div class="w-2 h-2 rounded-full bg-amber"></div></div>
      </div>
      <div>
        <p class="text-xs text-dim tracking-widest uppercase">SysControl v4.2.1</p>
        <p class="text-bright font-semibold text-sm tracking-tight">Maintenance Console</p>
      </div>
    </div>

    <div class="flex items-center gap-6">
      <div class="flex items-center gap-2 text-xs text-dim">
        <span class="w-1.5 h-1.5 rounded-full bg-amber" style="box-shadow:0 0 6px rgba(245,166,35,0.8)"></span>
        MAINT-{{ now()->format('Y-m-d') }}
      </div>
      <div class="flex items-center gap-2 text-xs text-dim">
        <span class="w-1.5 h-1.5 rounded-full bg-red-DEFAULT" style="box-shadow:0 0 6px rgba(255,71,87,0.8)"></span>
        PROD OFFLINE
      </div>
      <div class="bg-muted border border-border px-4 py-2 text-xs font-mono">
        <span class="text-dim">SYS TIME / </span>
        <span class="text-cyan-DEFAULT text-glow-cyan" id="clock">--:--:--</span>
      </div>
    </div>
  </header>

  <div class="maintenance-shell p-8 space-y-6 max-w-screen-2xl mx-auto">
    <div class="slide-in delay-1 relative border border-amber-dim bg-amber-glow rounded-sm p-4 pl-5 flex items-start gap-4 corner-tl corner-br glow-amber">
      <div class="relative shrink-0 mt-0.5">
        <div class="w-2.5 h-2.5 rounded-full bg-amber"></div>
        <div class="absolute inset-0 w-2.5 h-2.5 rounded-full bg-amber ping-slow"></div>
      </div>
      <div class="flex-1">
        <p class="text-amber font-semibold text-sm tracking-wide">CRITICAL MAINTENANCE WINDOW ACTIVE</p>
        <p class="text-amber/60 text-xs mt-1 font-mono">{{ $message }}</p>
      </div>
      <div class="shrink-0 text-right">
        <p class="text-xs text-dim">Access</p>
        <p class="text-xs text-bright font-mono">super-admin only</p>
      </div>
    </div>

    <div class="maintenance-stats slide-in delay-2 grid grid-cols-5 gap-4">
      <div class="bg-panel border border-border rounded-sm p-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-transparent via-amber to-transparent opacity-40"></div>
        <p class="text-xs text-dim tracking-widest uppercase mb-3">Overall Progress</p>
        <p class="text-3xl font-mono font-light text-bright">68<span class="text-dim text-lg">%</span></p>
        <div class="mt-3 h-0.5 bg-border rounded-full overflow-hidden"><div class="h-full shimmer rounded-full" style="width:68%"></div></div>
      </div>
      <div class="bg-panel border border-border rounded-sm p-4">
        <p class="text-xs text-dim tracking-widest uppercase mb-3">Tasks Done</p>
        <p class="text-3xl font-mono font-light text-green-DEFAULT">14<span class="text-dim text-sm">/21</span></p>
        <p class="text-xs text-dim mt-3">4 in progress</p>
      </div>
      <div class="bg-panel border border-border rounded-sm p-4">
        <p class="text-xs text-dim tracking-widest uppercase mb-3">Nodes Active</p>
        <p class="text-3xl font-mono font-light text-cyan-DEFAULT">3<span class="text-dim text-sm">/12</span></p>
        <p class="text-xs text-dim mt-3">9 suspended</p>
      </div>
      <div class="bg-panel border border-border rounded-sm p-4">
        <p class="text-xs text-dim tracking-widest uppercase mb-3">Errors</p>
        <p class="text-3xl font-mono font-light text-red-DEFAULT text-glow-red">2</p>
        <p class="text-xs text-red-DEFAULT/60 mt-3">Requires review</p>
      </div>
      <div class="bg-panel border border-border rounded-sm p-4">
        <p class="text-xs text-dim tracking-widest uppercase mb-3">Time Remaining</p>
        <p class="text-3xl font-mono font-light text-amber text-glow-amber" id="timer">02:45:00</p>
        <p class="text-xs text-dim mt-3">Estimated</p>
      </div>
    </div>

    <div class="maintenance-main grid grid-cols-3 gap-4">
      <div class="maintenance-task-panel col-span-2 slide-in delay-3">
        <div class="bg-panel border border-border rounded-sm overflow-hidden">
          <div class="flex items-center justify-between px-6 py-3.5 border-b border-border">
            <div class="flex items-center gap-3">
              <span class="text-xs text-dim tracking-widest uppercase">Maintenance Tasks</span>
              <span class="text-xs font-mono bg-muted border border-border px-2 py-0.5 text-dim">21 total</span>
            </div>
            <div class="flex items-center gap-2">
              <button onclick="filterTasks('all',this)" class="task-filter active-filter text-xs font-mono px-3 py-1 border border-amber/40 bg-amber-glow text-amber">All</button>
              <button onclick="filterTasks('running',this)" class="task-filter text-xs font-mono px-3 py-1 border border-border text-dim hover:border-border transition-colors">Running</button>
              <button onclick="filterTasks('done',this)" class="task-filter text-xs font-mono px-3 py-1 border border-border text-dim hover:border-border transition-colors">Done</button>
              <button onclick="filterTasks('pending',this)" class="task-filter text-xs font-mono px-3 py-1 border border-border text-dim hover:border-border transition-colors">Pending</button>
              <button onclick="filterTasks('error',this)" class="task-filter text-xs font-mono px-3 py-1 border border-border text-dim hover:border-border transition-colors">Error</button>
            </div>
          </div>

          <div class="grid grid-cols-12 px-6 py-2 border-b border-border text-xs text-dim tracking-widest uppercase">
            <div class="col-span-1">Status</div><div class="col-span-5">Task</div><div class="col-span-2">Node</div><div class="col-span-2">Duration</div><div class="col-span-2">Progress</div>
          </div>

          <div id="task-list" class="divide-y divide-border">
            <div class="task-row grid grid-cols-12 items-center px-6 py-3.5 cursor-pointer" data-status="done">
              <div class="col-span-1"><span class="w-2 h-2 rounded-full bg-green-DEFAULT inline-block" style="box-shadow:0 0 5px rgba(46,204,113,0.7)"></span></div>
              <div class="col-span-5"><p class="text-xs text-bright">Kernel upgrade - NODE-01</p><p class="text-xs text-dim font-mono mt-0.5">linux-kernel 6.8.2 -> 6.9.1</p></div>
              <div class="col-span-2 text-xs font-mono text-dim">node-01.prod</div><div class="col-span-2 text-xs font-mono text-dim">14m 32s</div>
              <div class="col-span-2"><div class="h-0.5 bg-border rounded-full"><div class="h-full bg-green-DEFAULT rounded-full" style="width:100%"></div></div></div>
            </div>
            <div class="task-row grid grid-cols-12 items-center px-6 py-3.5 cursor-pointer" data-status="done">
              <div class="col-span-1"><span class="w-2 h-2 rounded-full bg-green-DEFAULT inline-block" style="box-shadow:0 0 5px rgba(46,204,113,0.7)"></span></div>
              <div class="col-span-5"><p class="text-xs text-bright">Database vacuum - PSQL-PRIMARY</p><p class="text-xs text-dim font-mono mt-0.5">VACUUM FULL ANALYZE</p></div>
              <div class="col-span-2 text-xs font-mono text-dim">db-01.prod</div><div class="col-span-2 text-xs font-mono text-dim">28m 04s</div>
              <div class="col-span-2"><div class="h-0.5 bg-border rounded-full"><div class="h-full bg-green-DEFAULT rounded-full" style="width:100%"></div></div></div>
            </div>
            <div class="task-row grid grid-cols-12 items-center px-6 py-3.5 cursor-pointer" data-status="running">
              <div class="col-span-1"><div class="relative w-2 h-2"><span class="absolute inset-0 rounded-full bg-cyan-DEFAULT" style="box-shadow:0 0 5px rgba(0,212,255,0.7)"></span><span class="absolute inset-0 rounded-full bg-cyan-DEFAULT ping-slow"></span></div></div>
              <div class="col-span-5"><p class="text-xs text-bright">Kernel upgrade - NODE-07</p><p class="text-xs text-dim font-mono mt-0.5">linux-kernel 6.8.2 -> 6.9.1</p></div>
              <div class="col-span-2 text-xs font-mono text-dim">node-07.prod</div><div class="col-span-2 text-xs font-mono text-cyan-DEFAULT">Running 11m</div>
              <div class="col-span-2"><div class="h-0.5 bg-border rounded-full overflow-hidden"><div class="h-full rounded-full shimmer" style="width:52%"></div></div></div>
            </div>
            <div class="task-row grid grid-cols-12 items-center px-6 py-3.5 cursor-pointer bg-red-dim/20" data-status="error">
              <div class="col-span-1"><span class="w-2 h-2 rounded-full bg-red-DEFAULT inline-block" style="box-shadow:0 0 5px rgba(255,71,87,0.7)"></span></div>
              <div class="col-span-5"><p class="text-xs text-bright">Storage migration - SAN-ARRAY-02</p><p class="text-xs text-red-DEFAULT/60 font-mono mt-0.5">ERR: mount point unavailable</p></div>
              <div class="col-span-2 text-xs font-mono text-dim">san-02.prod</div><div class="col-span-2 text-xs font-mono text-red-DEFAULT">Failed 2m ago</div>
              <div class="col-span-2"><span class="text-xs font-mono text-red-DEFAULT/80 border border-red-DEFAULT/30 bg-red-dim px-2 py-0.5">RETRY</span></div>
            </div>
            <div class="task-row grid grid-cols-12 items-center px-6 py-3.5 cursor-pointer opacity-50" data-status="pending">
              <div class="col-span-1"><span class="w-2 h-2 rounded-full bg-border inline-block"></span></div>
              <div class="col-span-5"><p class="text-xs text-bright">Health check sweep - all nodes</p><p class="text-xs text-dim font-mono mt-0.5">Final verification pass</p></div>
              <div class="col-span-2 text-xs font-mono text-dim">all nodes</div><div class="col-span-2 text-xs font-mono text-dim">-</div><div class="col-span-2 text-xs font-mono text-dim">PENDING</div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-col gap-4 slide-in delay-4">
        <div class="bg-panel border border-border rounded-sm overflow-hidden">
          <div class="px-5 py-3.5 border-b border-border"><span class="text-xs text-dim tracking-widest uppercase">Node Status Map</span></div>
          <div class="p-5">
            <div class="grid grid-cols-4 gap-2" id="node-grid"></div>
            <div class="flex items-center gap-4 mt-4 pt-4 border-t border-border">
              <div class="flex items-center gap-1.5 text-xs text-dim"><span class="w-2 h-2 rounded-sm bg-green-DEFAULT"></span>Online</div>
              <div class="flex items-center gap-1.5 text-xs text-dim"><span class="w-2 h-2 rounded-sm bg-cyan-DEFAULT"></span>Maint</div>
              <div class="flex items-center gap-1.5 text-xs text-dim"><span class="w-2 h-2 rounded-sm bg-red-DEFAULT"></span>Error</div>
              <div class="flex items-center gap-1.5 text-xs text-dim"><span class="w-2 h-2 rounded-sm bg-muted border border-border"></span>Off</div>
            </div>
          </div>
        </div>

        <div class="bg-panel border border-border rounded-sm overflow-hidden">
          <div class="px-5 py-3.5 border-b border-border"><span class="text-xs text-dim tracking-widest uppercase">System Metrics</span></div>
          <div class="p-5 space-y-4">
            <div><div class="flex justify-between text-xs mb-1.5"><span class="text-dim">CPU Load</span><span class="font-mono text-amber">34%</span></div><div class="h-0.5 bg-border rounded-full"><div class="h-full bg-amber rounded-full" style="width:34%"></div></div></div>
            <div><div class="flex justify-between text-xs mb-1.5"><span class="text-dim">Memory</span><span class="font-mono text-cyan-DEFAULT">61%</span></div><div class="h-0.5 bg-border rounded-full"><div class="h-full bg-cyan-DEFAULT rounded-full" style="width:61%"></div></div></div>
            <div><div class="flex justify-between text-xs mb-1.5"><span class="text-dim">Disk I/O</span><span class="font-mono text-green-DEFAULT">18%</span></div><div class="h-0.5 bg-border rounded-full"><div class="h-full bg-green-DEFAULT rounded-full" style="width:18%"></div></div></div>
            <div><div class="flex justify-between text-xs mb-1.5"><span class="text-dim">Network Tx</span><span class="font-mono text-dim">4.2 GB/s</span></div><div class="h-0.5 bg-border rounded-full"><div class="h-full bg-border rounded-full" style="width:42%"></div></div></div>
          </div>
        </div>

        <div class="bg-panel border border-border rounded-sm overflow-hidden flex-1">
          <div class="px-5 py-3.5 border-b border-border flex items-center justify-between">
            <span class="text-xs text-dim tracking-widest uppercase">Event Log</span>
            <span class="text-xs font-mono text-cyan-DEFAULT blink">● LIVE</span>
          </div>
          <div class="p-3 space-y-1 font-mono text-xs max-h-64 overflow-y-auto" id="log-output">
            <div class="text-green-DEFAULT">[08:41:02] SSL cert renewed for *.prod.internal</div>
            <div class="text-dim">[08:55:50] NODE-01 kernel upgrade: complete</div>
            <div class="text-red-DEFAULT">[09:12:44] SAN-02 mount error: /dev/sdb2 unavailable</div>
            <div class="text-amber">[09:14:01] NODE-07 kernel upgrade: initiated</div>
            <div class="text-green-DEFAULT">[09:30:15] PSQL-PRIMARY vacuum: complete</div>
          </div>
        </div>
      </div>
    </div>

    <div class="slide-in delay-5 bg-panel border border-border rounded-sm px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <div class="w-1.5 h-1.5 rounded-full bg-amber" style="box-shadow:0 0 6px rgba(245,166,35,0.8)"></div>
        <span class="text-xs text-dim font-mono">Maintenance initiated by <span class="text-bright">system administrator</span> · Window: controlled access</span>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ route('login') }}" class="text-xs font-mono px-4 py-2 border border-border text-dim hover:text-bright hover:border-dim transition-colors">Super Admin Login</a>
      </div>
    </div>
  </div>

  <script>
    function updateClock() {
      const now = new Date();
      const clock = document.getElementById('clock');
      if (clock) clock.textContent = now.toUTCString().split(' ')[4] + ' UTC';
    }
    updateClock(); setInterval(updateClock, 1000);

    let remaining = 2 * 3600 + 45 * 60;
    function updateTimer() {
      const timer = document.getElementById('timer');
      if (!timer) return;
      if (remaining <= 0) { timer.textContent = '00:00:00'; return; }
      remaining--;
      const h = String(Math.floor(remaining / 3600)).padStart(2,'0');
      const m = String(Math.floor((remaining % 3600) / 60)).padStart(2,'0');
      const s = String(remaining % 60).padStart(2,'0');
      timer.textContent = `${h}:${m}:${s}`;
    }
    setInterval(updateTimer, 1000);

    const nodes = [
      {id:'01',s:'online'},{id:'02',s:'maint'},{id:'03',s:'off'},{id:'04',s:'online'},
      {id:'05',s:'off'},{id:'06',s:'off'},{id:'07',s:'maint'},{id:'08',s:'online'},
      {id:'09',s:'off'},{id:'10',s:'off'},{id:'11',s:'off'},{id:'12',s:'error'},
    ];
    const colorMap = {online:'bg-green-DEFAULT',maint:'bg-cyan-DEFAULT',error:'bg-red-DEFAULT',off:'bg-muted border border-border'};
    const glowMap  = {online:'box-shadow:0 0 6px rgba(46,204,113,0.6)',maint:'box-shadow:0 0 6px rgba(0,212,255,0.5)',error:'box-shadow:0 0 6px rgba(255,71,87,0.6)',off:''};
    const grid = document.getElementById('node-grid');
    if (grid) {
      nodes.forEach(n => {
        const d = document.createElement('div');
        d.className = `rounded-sm aspect-square flex flex-col items-center justify-center cursor-pointer transition-opacity hover:opacity-80 ${colorMap[n.s]}`;
        d.style.cssText = glowMap[n.s] + '; padding:4px;';
        d.innerHTML = `<span style="font-family:'IBM Plex Mono';font-size:9px;color:rgba(0,0,0,0.7);font-weight:600;">N${n.id}</span>`;
        d.title = `NODE-${n.id}: ${n.s.toUpperCase()}`;
        grid.appendChild(d);
      });
    }

    function filterTasks(type, btn) {
      document.querySelectorAll('.task-filter').forEach(b => {
        b.className = 'task-filter text-xs font-mono px-3 py-1 border border-border text-dim hover:border-border transition-colors';
      });
      btn.className = 'task-filter text-xs font-mono px-3 py-1 border border-amber/40 bg-amber-glow text-amber';
      document.querySelectorAll('[data-status]').forEach(row => {
        row.style.display = (type === 'all' || row.dataset.status === type) ? '' : 'none';
      });
    }

    const logLines = [
      {cls:'text-cyan-DEFAULT', msg:'NODE-07 kernel upgrade: 52% complete'},
      {cls:'text-dim', msg:'PSQL-REPLICA reindex: 31% complete'},
      {cls:'text-amber', msg:'SAN-02 retry scheduled in 60s'},
    ];
    let li = 0;
    setInterval(() => {
      if (li >= logLines.length) return;
      const log = document.getElementById('log-output');
      if (!log) return;
      const d = document.createElement('div');
      const now = new Date();
      const t = now.toTimeString().slice(0,8);
      d.className = logLines[li].cls;
      d.textContent = `[${t}] ${logLines[li].msg}`;
      log.appendChild(d);
      log.scrollTop = log.scrollHeight;
      li++;
    }, 5000);
  </script>
</body>
</html>
