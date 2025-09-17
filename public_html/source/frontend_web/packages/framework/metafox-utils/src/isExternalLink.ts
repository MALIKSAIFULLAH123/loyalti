/**
 *
 * @param {String} url - Url
 * @returns
 */
export default function isExternalLink(url: string): boolean {
  try {
    const base = new URL(
      `${window.location.protocol}//${window.location.host}`
    );

    return new URL(url, base).hostname !== base.hostname;
    // eslint-disable-next-line no-empty
  } catch (error) {}

  return false;
}
