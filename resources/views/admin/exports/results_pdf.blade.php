<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Institutional Semester Results</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.4; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 2px; }
        .header p { margin: 5px 0; font-size: 12px; color: #666; font-weight: bold; }
        
        .meta-grid { display: table; width: 100%; margin-bottom: 20px; border: 1px solid #ddd; background: #f9f9f9; }
        .meta-item { display: table-cell; padding: 10px; border-right: 1px solid #ddd; width: 25%; }
        .meta-item:last-child { border-right: none; }
        .meta-label { font-size: 9px; color: #888; text-transform: uppercase; display: block; margin-bottom: 3px; }
        .meta-value { font-size: 13px; font-weight: bold; color: #000; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #444; color: #fff; font-size: 10px; text-transform: uppercase; padding: 10px; text-align: left; }
        td { padding: 8px 10px; border-bottom: 1px solid #eee; font-size: 11px; }
        tr:nth-child(even) { background: #fafafa; }

        .grade-badge { font-weight: bold; padding: 2px 6px; border-radius: 3px; font-size: 10px; display: inline-block; }
        .pass { color: #10b981; }
        .fail { color: #ef4444; }
        
        .footer { margin-top: 30px; font-size: 9px; color: #aaa; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Institutional Result Sheet</h1>
        <p>OFFICIAL ACADEMIC RECORD · ATTENDAI MANAGEMENT SYSTEM</p>
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
            <span class="meta-label">Avg Institutional Score</span>
            <span class="meta-value">{{ $avgScore }}%</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Student Success Rate</span>
            <span class="meta-value">{{ $passRate }}%</span>
        </div>
    </div>

    @foreach($groupedData as $groupName => $students)
    <div style="margin-top: 30px;">
        <div style="background: #eee; padding: 8px 15px; font-weight: bold; font-size: 14px; border-left: 4px solid #444; margin-bottom: 10px;">
            MAJOR / GROUP: {{ $groupName ?? 'UNASSIGNED' }}
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">NO.</th>
                    <th style="width: 35%;">Student / Code</th>
                    <th style="text-align: center; width: 15%;">Subjects</th>
                    <th style="text-align: center; width: 15%;">Avg Score</th>
                    <th style="text-align: center; width: 15%;">Grade</th>
                    <th style="text-align: right; width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $res)
                <tr>
                    <td style="text-align: center; font-weight: bold; color: #888;">{{ $loop->iteration }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $res['student_name'] }}</div>
                        <div style="color: #888; font-size: 9px;">{{ $res['student_code'] }}</div>
                    </td>
                    <td style="text-align: center;">{{ $res['total_subjects'] }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ round($res['avg_score'], 1) }}</td>
                    <td style="text-align: center;">
                        <span class="grade-badge">{{ $res['grade'] }}</span>
                    </td>
                    <td style="text-align: right; font-weight: bold;" class="{{ $res['status'] === 'PASSED' ? 'pass' : 'fail' }}">
                        {{ $res['status'] }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    <div class="footer">
        Generated on {{ date('M d, Y @ h:i A') }} · This is a system-generated document and does not require a physical signature.
    </div>
</body>
</html>
