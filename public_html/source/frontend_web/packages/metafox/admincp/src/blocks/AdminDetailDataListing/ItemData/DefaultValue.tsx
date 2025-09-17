/**
 * @type: ui
 * name: acp.detail.ui.default
 */

export default function DefaultItem({ format, value }) {
  if (!value) {
    return null;
  }

  return value;
}
