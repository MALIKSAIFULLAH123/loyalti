/**
 * @type: service
 * name: copyToClipboard
 */

import copy from 'copy-to-clipboard';

export default function copyToClipboard(text: string): void {
  copy(text);
}
