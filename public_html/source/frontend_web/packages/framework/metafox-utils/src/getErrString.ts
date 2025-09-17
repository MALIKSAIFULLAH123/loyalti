import { get } from 'lodash';

export default function getErrString(
  error: any,
  fallback: string = 'Unknown Error'
) {
  const indicates = [
    'response.data.error',
    'response.data.message',
    'data.error.message',
    'data.error',
    'message'
  ];

  for (let i = 0; i < indicates.length; ++i) {
    const message = get(error, indicates[i]);

    if (message) {
      return message;
    }
  }

  return fallback;
}
