export default function toClassName(...str) {
  // converting all characters to lowercase
  const ans = str.join(' ').toLowerCase();

  return ans
    .split(' ')
    .reduce((s, c) => s + (c.charAt(0).toUpperCase() + c.slice(1)));
}
