import { getImageSrc } from '@metafox/utils';
import React from 'react';
import { BgStatusItemShape } from '../../types';
import { Box, Grid, styled } from '@mui/material';

const name = 'BgStatusPicker';

const ItemRoot = styled(Box, { name, slot: 'ItemRoot' })(({ theme }) => ({
  cursor: 'pointer',
  paddingBottom: '60%',
  position: 'relative'
}));

const ItemBg = styled(Box, {
  name,
  slot: 'ItemBg',
  shouldForwardProp: prop => prop !== 'selected'
})<{ selected: boolean }>(({ theme, selected }) => ({
  position: 'absolute',
  left: 0,
  top: 0,
  bottom: 0,
  right: 0,
  display: 'block',
  backgroundSize: 'cover',
  '&:before': {
    content: "''",
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    pointerEvents: 'none',
    border: '4px solid transparent'
  },
  ...(selected && {
    '&:before': {
      borderColor: theme.palette.primary.main
    }
  })
}));

export type BgStatusItemProps = {
  item: BgStatusItemShape;
  onClick: () => void;
  isHide?: boolean;
  onClickLoadMore?: () => void;
  labelLoadmore?: string;
  selected?: boolean;
};

export default function BgStatusItem({
  item,
  isHide,
  onClick,
  labelLoadmore,
  onClickLoadMore,
  selected
}: BgStatusItemProps) {
  if (isHide) return null;

  const imgSrc = getImageSrc(item.image, '200');

  return (
    <Grid item md={2.4} sm={4} xs={6}>
      <ItemRoot
        data-testid="itemBackgroundStatus"
        onClick={!onClickLoadMore ? onClick : undefined}
      >
        <ItemBg
          selected={selected}
          style={{
            backgroundImage: `url("${imgSrc}")`
          }}
        />
        {onClickLoadMore ? (
          <Box
            onClick={onClickLoadMore}
            sx={{
              position: 'absolute',
              left: 0,
              right: 0,
              top: 0,
              bottom: 0,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              background: 'rgba(0, 0, 0, 0.4)',
              color: '#fff',
              fontSize: '24px'
            }}
          >
            {labelLoadmore}
          </Box>
        ) : null}
      </ItemRoot>
    </Grid>
  );
}
