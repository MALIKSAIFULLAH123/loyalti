function captilizeWord(x: string): string {
  return x ? x.substr(0, 1).toUpperCase() + x.substr(1) : '';
}

export default function normalizeClsx(...name: string[]): string {
  return name.map((x, index) => (0 < index ? captilizeWord(x) : x)).join('');
}
