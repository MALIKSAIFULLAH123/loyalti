export default function getSafariVersion() {
  const nav = navigator.userAgent.toLowerCase();

  if (nav.indexOf('safari') !== -1) {
    if (nav.indexOf('chrome') > -1) {
      // Chrome
      const raw = nav.match(/chrom(?:e|ium)\/([0-9]+)\./);

      return raw ? parseInt(raw[1], 10) : null;
    } else {
      // Safari
      const raw = nav.match(/version\/(\d+)\.(\d+)/);

      return raw ? parseFloat(raw[1] + '.' + raw[2]) : null;
    }
  }

  return null;
}
