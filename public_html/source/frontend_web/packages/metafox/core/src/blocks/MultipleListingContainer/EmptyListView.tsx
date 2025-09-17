import React from 'react';
import { styled, Box } from '@mui/material';

const BlockEmpty = styled(Box, {
  name: 'listview-blockEmpty',
  slot: 'Root'
})(({ theme }) => ({
  display: 'none',
  '&:first-of-type:last-of-type': {
    display: 'block'
  }
}));

export default function EmptyListView({ children }) {
  // only show empty block when all elements in listingContainer is null
  return <BlockEmpty className="listview-blockEmpty">{children}</BlockEmpty>;
}
