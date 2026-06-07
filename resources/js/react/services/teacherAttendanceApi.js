import axios from 'axios';

export function fetchTeacherAttendanceToday() {
    return axios.get('/api/teacher/attendance/today').then(({ data }) => data);
}

export function submitTeacherQrCheckIn(payload) {
    return axios.post('/api/teacher/attendance/qr/check-in', payload).then(({ data }) => data);
}

export function fetchRequiredTeacherCheckouts() {
    return axios.get('/api/teacher/attendance/required-checkouts').then(({ data }) => data);
}

export function submitTeacherCheckout(sessionId, payload = { method: 'manual' }) {
    return axios.post(`/api/teacher/attendance/sessions/${sessionId}/check-out`, payload).then(({ data }) => data);
}

export function fetchAdminTeacherAttendanceDashboard(params) {
    return axios.get('/api/admin/teacher-attendance/dashboard', { params }).then(({ data }) => data);
}
