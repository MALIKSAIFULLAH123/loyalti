/**
 * @type: siteDock
 * name: NotificationWebPush
 * bundle: web
 */

import { useGlobal } from '@metafox/framework';
import React from 'react';

export default function RequestNotificationBanner() {
  const { dispatch, jsxBackend } = useGlobal();
  const [show, setShow] = React.useState(false);
  const cb = React.useCallback(() => {
    setShow(true);
  }, []);

  React.useEffect(() => {
    dispatch({ type: 'core/fcm/requestBanner', meta: { cb } });
  }, []);

  if (!show) return null;

  return jsxBackend.render({
    component: 'core.dialog.NotificationWebPush'
  });
}
