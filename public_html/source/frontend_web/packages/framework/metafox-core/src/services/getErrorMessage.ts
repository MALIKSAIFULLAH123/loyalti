/**
 * @type: service
 * name: getErrorMessage
 */

import { get } from 'lodash';

export default function getErrorMessage(err: unknown) {
  const message =
    get(err, 'response.data.error') || get(err, 'response.data.message');

  return message;
}
