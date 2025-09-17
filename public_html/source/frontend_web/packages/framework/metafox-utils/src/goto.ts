/**
 * @type: service
 * name: redirectTo
 */
export default function redirectTo(href: string) {
  if (window?.location) {
    window.location.assign(href);
  }
}
