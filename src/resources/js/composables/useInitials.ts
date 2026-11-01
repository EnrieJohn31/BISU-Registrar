export type UseInitialsReturn = {
    getInitials: (fullName?: string) => string;
};

function getInitial(name: string): string {
    return Array.from(name)[0] ?? '';
}

export function getInitials(fullName?: string): string {
    if (!fullName) {
        return '';
    }

    // Skip words such as "(Demo)" so punctuation never becomes an initial.
    const names = fullName
        .trim()
        .split(/\s+/u)
        .filter((name) => /^\p{L}/u.test(name));

    if (names.length === 0) {
        return '';
    }

    if (names.length === 1) {
        return getInitial(names[0]).toUpperCase();
    }

    return `${getInitial(names[0])}${getInitial(names[names.length - 1])}`.toUpperCase();
}

export function useInitials(): UseInitialsReturn {
    return { getInitials };
}
