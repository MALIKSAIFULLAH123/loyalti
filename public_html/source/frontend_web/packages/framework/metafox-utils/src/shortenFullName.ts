export default function shortenFullName(name: string): string {
  if (!name || !name.replace(/\s/g, '')) return 'NA';

  const regex = /^([a-zA-Z]|[\d])/;
  const data = name.split(/\s+/g).filter(x => regex.test(x));
  const cs = (data?.length ? data : name.split(/\s+/g))
    .splice(0, 2)
    .map(x => String.fromCodePoint(x.codePointAt(0)).toUpperCase());

  return 1 < name.length
    ? cs.join('')
    : Array.from(cs)
        .splice(0, 2)
        .map(x => x.toUpperCase())
        .join('');
}
