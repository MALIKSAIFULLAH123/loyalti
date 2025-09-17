/**
 * @type: service
 * name: useIntlMessages
 */
import { useSelector } from 'react-redux';
import { GlobalState } from '@metafox/framework';

export default function useIntlMessages() {
  return useSelector((state: GlobalState) => state.intl.messages);
}
