/**
 * @type: skeleton
 * name: advertise.block.advertisesBlockItem.skeleton
 */
import { ImageSkeleton, ItemSummary } from '@metafox/ui';
import { Box, Skeleton, styled } from '@mui/material';
import React from 'react';

const ContentWrapperStyled = styled(Box, {
  shouldForwardProp: props => props !== 'slotName'
})<{ slotName?: string }>(({ theme, slotName }) => ({
  display: 'flex',
  flexDirection: 'row',
  alignItems: 'center',
  marginTop: theme.spacing(2),
  padding: theme.spacing(1),
  ...(slotName === 'main' && {
    marginTop: 0,
    padding: theme.spacing(1, 0)
  }),
  ...(slotName === 'top' && {
    padding: theme.spacing(1, 0),
    marginTop: 0,
    maxWidth: theme.breakpoints.values.md,
    width: theme.breakpoints.values.md,
    marginLeft: 'auto',
    marginRight: 'auto'
  })
}));

const ImageStyled = styled(ImageSkeleton)(({ theme }) => ({
  width: '90px',
  height: '90px',
  borderRadius: theme.shape.borderRadius,
  padding: 0
}));

const ContentStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  flex: 1,
  minWidth: 0,
  paddingRight: theme.spacing(1)
}));

const InfoStyled = styled(Box)(({ theme }) => ({
  marginLeft: theme.spacing(1)
}));

export default function LoadingSkeleton(props) {
  const slotName = props?.itemProps?.slotName;

  return (
    <ContentWrapperStyled slotName={slotName}>
      <ContentStyled>
        <ImageStyled ratio="169" />
        <InfoStyled>
          <div>
            <Skeleton width={160} />
          </div>
          <ItemSummary>
            <Skeleton width={160} />
          </ItemSummary>
        </InfoStyled>
      </ContentStyled>
    </ContentWrapperStyled>
  );
}
