/**
 * @type: ui
 * name: event.invitedButton
 */

import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Button, styled } from '@mui/material';
import React from 'react';

const ButtonInviteStyled = styled(Button, { name: 'ButtonInviteStyled' })(
  ({ theme }) => ({
    height: '100%',
    '& .MuiButton-startIcon': {
      marginLeft: 0
    }
  })
);

const ButtonInvite = props => {
  const { item, type } = props;
  const { i18n, dispatch, useDialog } = useGlobal();
  const { closeDialog } = useDialog();

  const handleClickInvited = () => {
    closeDialog();
    dispatch({
      type,
      payload: { identity: item._identity }
    });
  };

  return (
    <ButtonInviteStyled
      size="small"
      variant="outlined"
      component="h5"
      onClick={handleClickInvited}
      startIcon={
        <LineIcon sx={{ marginLeft: '0 !important' }} icon={'ico-envelope'} />
      }
    >
      <span>{i18n.formatMessage({ id: 'invite_friends' })}</span>
    </ButtonInviteStyled>
  );
};

export default ButtonInvite;
