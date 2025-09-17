/**
 * @type: ui
 * name: core.ui.taggedPlace
 */
import { Typography, styled } from '@mui/material';
import * as React from 'react';

const RootStyled = styled(Typography, { name: 'TaggedPlace', slot: 'root' })(
  ({ theme }) => ({
    fontWeight: theme.typography.fontWeightBold
  })
);

export default function TaggedPlace({ place }) {
  return (
    <RootStyled component="span">
      <a
        rel="noopener noreferrer"
        href={`https://www.google.com/maps?daddr=${place.lat},${place.lng}`}
        target="_blank"
      >
        {place.address}
      </a>
    </RootStyled>
  );
}
