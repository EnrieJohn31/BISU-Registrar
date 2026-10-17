// Timestamps are stored in UTC and shown in campus time (System_Plan Part 02).
const CAMPUS_TIME_ZONE = 'Asia/Manila';

const dateTime = new Intl.DateTimeFormat('en-PH', {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone: CAMPUS_TIME_ZONE,
});

export function formatDateTime(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    return dateTime.format(new Date(value));
}
