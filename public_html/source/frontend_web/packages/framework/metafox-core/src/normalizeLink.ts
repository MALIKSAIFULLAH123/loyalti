/**
 * @type: service
 * name: selfLink
 */
export default function selfLink(url: string): string {
  return url.replace('https://dev-foxsocial.phpfox.us/backend/', '/');
}
