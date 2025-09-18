/**
 * @type: ui
 * name: event.viewListHostButton
 */

import { useGlobal } from '@metafox/framework';
import { Button } from '@mui/material';
import React from 'react';

const ViewListGuestButton = () => {
  const { i18n, dispatch, usePageParams } = useGlobal();
  const { identity } = usePageParams();

  const handleClick = () => {
    dispatch({ type: 'event/viewAllHost', payload: { identity } });
  };

  return (
    <Button onClick={handleClick} variant="link">
      {i18n.formatMessage({ id: 'all_hosts' })}
    </Button>
  );
};

export default ViewListGuestButton;
