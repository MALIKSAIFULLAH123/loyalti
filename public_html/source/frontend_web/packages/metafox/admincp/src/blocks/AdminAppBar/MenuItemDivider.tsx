/**
 * @type: ui
 * name: appbar.item.divider
 * bundle: admincp
 */
import { Box, styled } from '@mui/material';
import React from 'react';

const name = 'AdminCpItemDivider';

const Divider = styled(Box, { name, slot: 'UserAvatarButton' })(
  ({ theme }) => ({
    position: 'relative',
    '&:before': {
      content: '""',
      position: 'absolute',
      left: '0',
      top: '10px',
      bottom: '10px',
      width: '1px',
      backgroundColor: '#eaeaea'
    }
  })
);

export default function AsDivider() {
  return <Divider />;
}
