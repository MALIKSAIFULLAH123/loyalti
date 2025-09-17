const counter: Record<string, number> = {};

export default function nextId(prefix: string = 'i'): string {
  if (!counter[prefix]) counter[prefix] = 0;

  const value = ++counter[prefix];

  return `${prefix}${value}`;
}
