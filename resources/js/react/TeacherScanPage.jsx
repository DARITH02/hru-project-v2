import { useEffect, useState } from 'react';
import { fetchTeacherAttendanceToday, submitTeacherQrCheckIn } from './services/teacherAttendanceApi';

export default function TeacherScanPage() {
    const [token, setToken] = useState('');
    const [sessions, setSessions] = useState([]);
    const [message, setMessage] = useState('');
    const [location, setLocation] = useState({});

    useEffect(() => {
        fetchTeacherAttendanceToday().then((data) => setSessions(data.sessions || []));
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
                setLocation({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                });
            });
        }
    }, []);

    async function submit(event) {
        event.preventDefault();
        setMessage('');
        const data = await submitTeacherQrCheckIn({ token, ...location });
        setMessage(`Checked in ${data.session?.subject?.name || 'session 1'}. Later same-subject sessions are auto checked in.`);
        const today = await fetchTeacherAttendanceToday();
        setSessions(today.sessions || []);
    }

    return (
        <main className="teacher-attendance-page">
            <header className="teacher-attendance-header">
                <div>
                    <h1>Teacher QR Check-In</h1>
                    <p>Session 1 requires QR. Session 2 uses session 1 check-in for the same subject and date.</p>
                </div>
            </header>

            {message && <div className="notice notice-success">{message}</div>}

            <section className="attendance-grid">
                <form className="attendance-panel" onSubmit={submit}>
                    <label htmlFor="teacher-qr-token">QR Token</label>
                    <textarea id="teacher-qr-token" value={token} onChange={(event) => setToken(event.target.value)} required autoFocus />
                    <button type="submit">Validate QR & Check In</button>
                </form>

                <div className="attendance-panel">
                    <h2>Today</h2>
                    {sessions.map((session) => (
                        <article key={session.id} className="attendance-row">
                            <strong>{session.subject?.name || 'Subject'} · Session {session.session_number}</strong>
                            <span>{session.scheduled_start_time?.slice(11, 16)} - {session.scheduled_end_time?.slice(11, 16)}</span>
                            <em>{String(session.attendance_status).replaceAll('_', ' ')}</em>
                        </article>
                    ))}
                </div>
            </section>
        </main>
    );
}
