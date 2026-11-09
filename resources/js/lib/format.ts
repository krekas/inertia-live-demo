const moneyFormatters = new Map<string, Intl.NumberFormat>();

export function formatMoney(cents: number, currency = 'EUR'): string {
    let formatter = moneyFormatters.get(currency);

    if (!formatter) {
        formatter = new Intl.NumberFormat(undefined, {
            style: 'currency',
            currency,
        });
        moneyFormatters.set(currency, formatter);
    }

    return formatter.format(cents / 100);
}

export function formatHour(iso: string, timeZone = 'UTC'): string {
    return new Intl.DateTimeFormat(undefined, {
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23',
        timeZone,
    }).format(new Date(iso));
}

export function formatDateTime(iso: string, timeZone = 'UTC'): string {
    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'medium',
        timeZone,
    }).format(new Date(iso));
}

export function formatOrderNumber(id: number): string {
    return `#${String(id).padStart(5, '0')}`;
}
