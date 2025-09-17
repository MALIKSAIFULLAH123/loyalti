/**
 * @type: service
 * name: SiteFixedDockContainer
 */
import { useGlobal } from '@metafox/framework';
import { Box, styled } from '@mui/material';
import React from 'react';

const name = 'SiteFixedDockContainer';
const FixedPlace = styled(Box, { name })(({ theme }) => ({
  position: 'fixed',
  bottom: 0,
  left: 0,
  right: 0,
  zIndex: theme.zIndex.modal,
  display: 'flex',
  flexDirection: 'column'
}));

export default function SiteFixedDockContainer() {
  const { jsxBackend, layoutBackend } = useGlobal();
  const items = layoutBackend.getSiteFixedDockComponents();

  if (!items?.length) return null;

  return (
    <FixedPlace>
      {jsxBackend.render(layoutBackend.getSiteFixedDockComponents())}
    </FixedPlace>
  );
}
