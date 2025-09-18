import { ItemMedia, ItemText, ItemTitle, LineIcon } from '@metafox/ui';
import { Box, IconButton, Skeleton, styled } from '@mui/material';
import React from 'react';

const name = 'loading-skeleton';

const ItemStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  height: 72,
  padding: theme.spacing(0.5, 1)
}));

const BlockCollapseRoot = styled(Box, {
  name,
  slot: 'BlockCollapse',
  shouldForwardProp: props => props !== 'title'
})<{ title?: string }>(({ theme, title }) => ({
  ...(title && {
    borderTop: theme.mixins.border('secondary')
  })
}));
const BlockCollapseHeader = styled('div', {
  name,
  slot: 'BlockCollapseHeader'
})(({ theme }) => ({
  padding: theme.spacing(2.5, 2, 2.5, 3),
  display: 'flex',
  flexDirection: 'row',
  justifyContent: 'space-between',
  alignItems: 'center',
  cursor: 'pointer'
}));
const BlockTitle = styled('div', {
  name,
  slot: 'BlockTitle'
})(({ theme }) => ({
  ...theme.typography.body1,
  color: theme.palette.text.primary,
  fontWeight: '600'
}));

interface Props {
  title?: string;
}

export default function LoadingSkeleton({ title }: Props) {
  return (
    <BlockCollapseRoot title={title}>
      {title ? (
        <BlockCollapseHeader>
          <BlockTitle>{title}</BlockTitle>
          <IconButton size="small" color="default">
            <LineIcon icon="ico-angle-up" />
          </IconButton>
        </BlockCollapseHeader>
      ) : null}
      {Array(4)
        .fill(0)
        .map((_, index) => (
          <ItemStyled key={index}>
            <ItemMedia>
              <Skeleton
                variant="avatar"
                width={40}
                height={40}
                sx={{ mr: 1 }}
              />
            </ItemMedia>
            <ItemText>
              <ItemTitle>
                <Skeleton variant="text" width={'100%'} />
              </ItemTitle>
              <ItemTitle>
                <Skeleton width={120} />
              </ItemTitle>
            </ItemText>
          </ItemStyled>
        ))}
    </BlockCollapseRoot>
  );
}
