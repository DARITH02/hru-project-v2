<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Issues & Blacklist Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.4; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #ef4444; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 22px; text-transform: uppercase; letter-spacing: 2px; color: #ef4444; }
        .header p { margin: 5px 0; font-size: 11px; color: #666; font-weight: bold; }
        
        .meta-grid { display: table; width: 100%; margin-bottom: 20px; border: 1px solid #ddd; background: #f9f9f9; }
        .meta-item { display: table-cell; padding: 10px; border-right: 1px solid #ddd; width: 25%; }
        .meta-item:last-child { border-right: none; }
        .meta-label { font-size: 8px; color: #888; text-transform: uppercase; display: block; margin-bottom: 3px; }
        .meta-value { font-size: 13px; font-weight: bold; color: #000; }

        .section-title { font-size: 14px; font-weight: bold; background: #f3f4f6; padding: 6px 12px; border-left: 4px solid #ef4444; margin-top: 25px; margin-bottom: 10px; text-transform: uppercase; }
        .section-title.at-risk { border-left-color: #f59e0b; color: #b45309; background: #fffbeb; }

        table { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 20px; }
        th { background: #4b5563; color: #fff; font-size: 9px; text-transform: uppercase; padding: 8px; text-align: left; }
        td { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 10.5px; }
        tr:nth-child(even) { background: #fafafa; }

        .badge { font-weight: bold; padding: 2px 6px; border-radius: 3px; font-size: 8px; text-transform: uppercase; display: inline-block; }
        .badge.blacklisted { background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; }
        .badge.at-risk { background: #fef3c7; color: #d97706; border: 1px solid #fcd34d; }

        .footer { margin-top: 40px; font-size: 9px; color: #aaa; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Attendance Issues & Blacklist Report</h1>
        <p>OFFICIAL ATTENDANCE MONITORING RECORD · ATTENDAI SYSTEM</p>
    </div>

    <div class="meta-grid">
        <div class="meta-item">
            <span class="meta-label">Academic Year</span>
            <span class="meta-value">{{ $academicYear }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Semester</span>
            <span class="meta-value">Term {{ $semester }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Total Blacklisted</span>
            <span class="meta-value">{{ $totalBlacklisted }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Total At Risk</span>
            <span class="meta-value">{{ $totalAtRisk }}</span>
        </div>
    </div>

    {{-- 🛑 SECTION 1: BLACKLISTED --}}
    <h2 class="section-title">🚫 Blacklisted Students (30+ Absences)</h2>
    @if($blacklistedGrouped->isEmpty())
        <p style="font-size: 11px; color: #666; font-style: italic; padding-left: 10px;">No blacklisted students recorded in this semester.</p>
    @else
        @foreach($blacklistedGrouped as $groupName => $items)
            <div style="margin-left: 10px; margin-top: 10px;">
                <div style="font-weight: bold; font-size: 11.5px; color: #374151; margin-bottom: 5px; font-family: monospace;">
                    👥 Group: {{ strtoupper($groupName) }} ({{ count($items) }} students)
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%; text-align: center;">NO.</th>
                            <th style="width: 35%;">Student Name / Code</th>
                            <th style="width: 30%;">Major</th>
                            <th style="width: 15%; text-align: center;">Absences</th>
                            <th style="width: 15%; text-align: right;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td style="text-align: center; color: #888;">{{ $loop->iteration }}</td>
                                <td>
                                    <div style="font-weight: bold;">{{ $item['student']->user->name }}</div>
                                    <div style="color: #666; font-size: 8.5px; font-family: monospace;">{{ $item['student']->student_code }}</div>
                                </td>
                                <td>
                                    {{ $item['student']->major->name ?? $item['student']->group->major->name ?? 'N/A' }}
                                </td>
                                <td style="text-align: center; font-weight: bold; color: #dc2626; font-size: 12px;">
                                    {{ $item['absences'] }}
                                </td>
                                <td style="text-align: right;">
                                    <span class="badge blacklisted">Blacklisted</span>
                                    @if($item['restore_count'] > 0)
                                        <div style="font-size: 8px; color: #666; margin-top: 2px;">Restored: {{ $item['restore_count'] }}/2</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

    {{-- ⚠️ SECTION 2: AT RISK --}}
    <h2 class="section-title at-risk">⚠️ At-Risk Students (10-29 Absences)</h2>
    @if($atRiskGrouped->isEmpty())
        <p style="font-size: 11px; color: #666; font-style: italic; padding-left: 10px;">No at-risk students recorded in this semester.</p>
    @else
        @foreach($atRiskGrouped as $groupName => $items)
            <div style="margin-left: 10px; margin-top: 10px;">
                <div style="font-weight: bold; font-size: 11.5px; color: #374151; margin-bottom: 5px; font-family: monospace;">
                    👥 Group: {{ strtoupper($groupName) }} ({{ count($items) }} students)
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%; text-align: center;">NO.</th>
                            <th style="width: 35%;">Student Name / Code</th>
                            <th style="width: 30%;">Major</th>
                            <th style="width: 15%; text-align: center;">Absences</th>
                            <th style="width: 15%; text-align: right;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td style="text-align: center; color: #888;">{{ $loop->iteration }}</td>
                                <td>
                                    <div style="font-weight: bold;">{{ $item['student']->user->name }}</div>
                                    <div style="color: #666; font-size: 8.5px; font-family: monospace;">{{ $item['student']->student_code }}</div>
                                </td>
                                <td>
                                    {{ $item['student']->major->name ?? $item['student']->group->major->name ?? 'N/A' }}
                                </td>
                                <td style="text-align: center; font-weight: bold; color: #d97706; font-size: 12px;">
                                    {{ $item['absences'] }}
                                </td>
                                <td style="text-align: right;">
                                    <span class="badge at-risk">At Risk</span>
                                    @if($item['restore_count'] > 0)
                                        <div style="font-size: 8px; color: #666; margin-top: 2px;">Restored: {{ $item['restore_count'] }}/2</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

    <div class="footer">
        Generated on {{ date('M d, Y @ h:i A') }} · ATTENDAI Intelligence System · System-generated confidential document.
    </div>
</body>
</html>
