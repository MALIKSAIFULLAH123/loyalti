/**
 * @type: ui
 * name: acp.detail.ui.time
 */

import { useGlobal } from '@metafox/framework';

export default function DateItem({ format, value }) {
  const { moment } = useGlobal();

  if (!value) {
    return null;
  }

  const date = moment(value);

  return date.format(format);
}
