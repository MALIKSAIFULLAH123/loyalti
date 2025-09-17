/**
 *
 * @param {String} name - Normalize pageMetaName format.
 * @returns {String} without ended "."
 */
export default function uniqMetaName(name: string = ''): string {
  const arr = name.toLowerCase().split('.');
  let prefix = arr.shift();

  if (prefix === 'admin') {
    prefix = `admin.${arr.shift()}`;
  }

  const key = arr
    .map(str => {
      return str
        .toLowerCase()
        .replace(/([\W|-]+)/g, '_')
        .replace(/(^\W+|^_+|\W+$|_+$)/g, '');
    })
    .filter(Boolean)
    .join('.');

  return `${prefix}.${key}`;
}
