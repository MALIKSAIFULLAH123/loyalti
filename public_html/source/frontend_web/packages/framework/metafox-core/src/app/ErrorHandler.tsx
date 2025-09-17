import { getErrString } from '@metafox/utils';
import { get } from 'lodash';
import { useGlobal } from '../hooks';

export default function ErrorHandler() {
  const { dialogBackend, use } = useGlobal();

  const handleActionError = error => {
    const alert = get(error, 'response.data.meta.alert');

    if (alert) {
      dialogBackend.alert(alert);
    } else {
      const message = getErrString(error);
      dialogBackend.alert({ title: 'Oops!', message });
    }
  };

  use({ handleActionError });

  return null;
}
