import { useEffect, useState } from 'react';
import { fetchAdminTeacherAttendanceDashboard } from './services/teacherAttendanceApi';

export default function AdminLiveTeacherAttendanceDashboard({ date = new Date().toISOString().slice(0, 10) }) {
    const [dashboard, setDashboard] = useState({ stats: {}, sessions: [] });

    async function load() {
        const data = await fetchAdminTeacherAttendanceDashboard({ date });
        setDashboard(data);
    }

    useEffect(() => {
        load();

        if (window.Echo) {
            const channel = window.Echo.channel(`teacher-attendance.${date}`)
                .listen('.teacher.attendance.updated', (payload) => {
                    setDashboard((current) => ({
                        ...current,
                        sessions: current.sessions.map((session) => session.id === payload.session.id ? { ...session, ...payload.session } : session),
                    }));
                });

            return () => window.Echo.leaveChannel(channel.name);
        }

        const interval = setInterval(load, 10000);
        return () => clearInterval(interval);
    }, [date]);

    return (
        <main className="teacher-attendance-page">
            <header className="teacher-attendance-header">
                <div>
                    <h1>Teacher Attendance Live</h1>
                    <p>Updates through Laravel Echo/Reverb when configured.</p>
                </div>
            </header>

            <section className="attendance-stats">
                {['scheduled', 'present', 'late', 'absent', 'completed'].map((key) => (
                    <article key={key}>
                        <span>{key.replaceAll('_', ' ')}</span>
                        <strong>{dashboard.stats?.[key] ?? 0}</strong>
                    </article>
                ))}
            </section>

            <section className="attendance-panel">
                {dashboard.sessions.map((session) => (
                    <article key={session.id} className="attendance-row">
                        <strong>{session.teacher?.user?.name || session.teacher_name || 'Teacher'}</strong>
                        <span>{session.subject?.name || session.subject_name || 'Subject'} · Session {session.session_number}</span>
                        <em>{String(session.attendance_status).replaceAll('_', ' ')}</em>
                    </article>
                ))}
            </section>
        </main>
    );
}
