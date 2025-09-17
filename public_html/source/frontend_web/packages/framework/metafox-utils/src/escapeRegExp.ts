export default function escapeRegExp(x: string = ''): string {
  // $& means the whole matched string
  return x.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
