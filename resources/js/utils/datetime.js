const DATETIME_WITH_TZ_RE = /(Z|[+-]\d{2}:\d{2})$/i;
const LOCAL_DATETIME_RE = /^(\d{4})-(\d{2})-(\d{2})(?:[T ](\d{2}):(\d{2})(?::(\d{2}))?)?$/;

const pad = (value) => String(value).padStart(2, '0');

export const parseDateTimeValue = (value) => {
  if (!value) return null;

  if (value instanceof Date) {
    return Number.isNaN(value.getTime()) ? null : value;
  }

  const source = String(value).trim();
  if (!source) return null;

  if (DATETIME_WITH_TZ_RE.test(source)) {
    const date = new Date(source);
    return Number.isNaN(date.getTime()) ? null : date;
  }

  const match = source.match(LOCAL_DATETIME_RE);
  if (match) {
    const [, year, month, day, hour = '00', minute = '00', second = '00'] = match;
    return new Date(
      Number(year),
      Number(month) - 1,
      Number(day),
      Number(hour),
      Number(minute),
      Number(second)
    );
  }

  const date = new Date(source);
  return Number.isNaN(date.getTime()) ? null : date;
};

export const formatForDatetimeLocalInput = (value) => {
  const date = parseDateTimeValue(value);
  if (!date) return '';

  return [
    date.getFullYear(),
    pad(date.getMonth() + 1),
    pad(date.getDate()),
  ].join('-') + `T${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

export const formatDateValue = (value, locale = 'es-MX', options = { dateStyle: 'medium' }) => {
  const date = parseDateTimeValue(value);
  if (!date) return '';
  return new Intl.DateTimeFormat(locale, options).format(date);
};

export const formatTimeValue = (value, locale = 'es-MX', options = { hour: '2-digit', minute: '2-digit' }) => {
  const date = parseDateTimeValue(value);
  if (!date) return '';
  return new Intl.DateTimeFormat(locale, options).format(date);
};

export const formatDateTimeValue = (
  value,
  locale = 'es-MX',
  options = { dateStyle: 'medium', timeStyle: 'short' }
) => {
  const date = parseDateTimeValue(value);
  if (!date) return '';
  return new Intl.DateTimeFormat(locale, options).format(date);
};
