import { useSelector } from 'react-redux';
import { GlobalState, useGlobal } from '@metafox/framework';
import React from 'react';
import { get, isEmpty } from 'lodash';

export default function UserChangeStatusAware() {
  const { dispatch } = useGlobal();
  const users = useSelector((state: GlobalState) =>
    get(state, 'user.entities.user', {})
  );

  React.useEffect(() => {
    if (isEmpty(users)) return;

    dispatch({
      type: 'chatplus/updateUserStatus',
      payload: { users }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [Object.keys(users)?.length]);

  return null;
}
