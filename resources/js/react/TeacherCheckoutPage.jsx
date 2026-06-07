import { useEffect, useState } from 'react';
import { fetchRequiredTeacherCheckouts, submitTeacherCheckout } from './services/teacherAttendanceApi';

export default function TeacherCheckoutPage() {
    const [sessions, setSessions] = useState([]);

    async function load() {
        const data = await fetchRequiredTeacherCheckouts();
        setSessions(data.sessions || []);
    }

    useEffect(() => {
        load();
    }, []);

    async function checkout(sessionId) {
        await submitTeacherCheckout(sessionId);
        await load();
    }

    return (
        <main className="teacher-attendance-page">
            <header className="teacher-attendance-header">
                <div>
                    <h1>Teacher Checkout</h1>
                    <p>Each session records its own checkout, including session 2.</p>
                </div>
            </header>

            <section className="attendance-grid">
                {sessions.map((session) => (
                    <article key={session.id} className="attendance-panel">
                        <span>{session.check_in_method === 'auto_session' ? 'Auto check-in' : 'Checked in'}</span>
                        <h2>{session.subject?.name || 'Subject'} · Session {session.session_number}</h2>
                        <p>{session.scheduled_start_time?.slice(11, 16)} - {session.scheduled_end_time?.slice(11, 16)}</p>
                        <button type="button" onClick={() => checkout(session.id)}>Check Out</button>
                    </article>
                ))}
                {sessions.length === 0 && <div className="attendance-panel">No sessions currently require checkout.</div>}
            </section>
        </main>
    );
}
