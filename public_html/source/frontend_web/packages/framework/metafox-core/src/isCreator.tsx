export default function isCreator(x: any): boolean {
  return !!(x && x.prototype && /function/i.test(typeof x.prototype.bootstrap));
}
