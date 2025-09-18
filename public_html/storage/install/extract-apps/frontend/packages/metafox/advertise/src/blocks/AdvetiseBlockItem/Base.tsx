import React from 'react';
import { useGlobal } from '@metafox/framework';
import { CreationTypeAdvertise } from '@metafox/advertise/types';
import { Box, styled } from '@mui/material';

const RootStyled = styled(Box, {
  shouldForwardProp: props => props !== 'slotName'
})<{ slotName?: any }>(({ theme, slotName }) => ({
  width: '100%',
  flex: 1,
  minHeight: 0,
  marginTop: theme.spacing(2),
  '&:first-of-type': { marginTop: 0 },
  '&:last-child': { marginBottom: 0 }
}));

export interface Props {
  [key: string]: any;
}

function Base(props: Props) {
  const { itemProps, identity } = props;
  const { slotName = 'main' } = itemProps;
  const { jsxBackend, useGetItem } = useGlobal();

  const item = useGetItem(identity);

  const BannerAdvertise = jsxBackend.get('advertise.block.banner');
  const HtmlAdvertise = jsxBackend.get('advertise.block.html');

  if (!item) return null;

  return (
    <RootStyled slotName={slotName}>
      {item.creation_type === CreationTypeAdvertise.IMAGE ? (
        <BannerAdvertise identity={identity} slotName={slotName} />
      ) : (
        <HtmlAdvertise identity={identity} slotName={slotName} />
      )}
    </RootStyled>
  );
}

export default Base;
