/**
 * @type: ui
 * name: shortcut.ui.toggleButton
 * chunkName: sidebarHome
 */
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, styled } from '@mui/material';
import React from 'react';

const IconWrapper = styled(Box, {
  name: 'IconWrapper'
})(({ theme }) => ({
  backgroundColor:
    theme.palette.mode === 'light' ? '#e0dddd' : theme.palette.grey[500],
  borderRadius: '50%',
  fontSize: theme.mixins.pxToRem(16),
  color:
    theme.palette.mode === 'light'
      ? theme.palette.grey[600]
      : theme.palette.grey[700]
}));

const Wrapper = styled(Box, {
  name: 'LayoutSlot',
  slot: 'onlineItem',
  overridesResolver(props, styles) {
    return [styles.onlineItem];
  }
})(({ theme }) => ({
  borderRadius: theme.shape.borderRadius,
  fontWeight: 500,
  fontSize: theme.mixins.pxToRem(15),
  '&:hover': {
    background: 'rgba(0,0,0,0.04)'
  }
}));

type Props = {
  setCollapsed: (x) => void;
  collapsed: boolean;
};

export default function AsLink({ setCollapsed, collapsed }: Props) {
  const { i18n } = useGlobal();

  return (
    <Wrapper
      onClick={() => setCollapsed(prev => !prev)}
      sx={{ display: 'flex', alignItems: 'center', cursor: 'pointer' }}
      p={1.5}
      mx={1}
    >
      <IconWrapper
        sx={{
          display: 'inline-flex',
          alignItems: 'center',
          justifyContent: 'center',
          width: '32px',
          height: '32px'
        }}
        mr={2}
      >
        <LineIcon icon={collapsed ? 'ico-angle-down' : 'ico-angle-up'} />
      </IconWrapper>
      {i18n.formatMessage({ id: collapsed ? 'more' : 'less' })}
    </Wrapper>
  );
}
