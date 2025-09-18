/**
 * @type: itemView
 * name: advertise.itemView.itemTable
 * chunkName: advertise
 */

import React from 'react';
import { styled, Box, Typography } from '@mui/material';
import { FormatNumber, LineIcon } from '@metafox/ui';
import SkeletonLoading from './LoadingSkeleton';
import { useGlobal } from '@metafox/framework';

const name = 'Table';

const Root = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  height: '145px',
  borderRadius: theme.shape.borderRadius,
  background: theme.palette.action.selected,
  padding: theme.spacing(5),
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'flex-start'
}));

const LineIconStyled = styled(LineIcon, {
  name,
  slot: 'icon',
  shouldForwardProp: props => props !== 'size'
})<{ size?: any }>(({ theme, size }) => ({
  color: theme.palette.text.secondary,
  minWidth: '70px',
  ...(size && {
    fontSize: theme.mixins.pxToRem(size)
  })
}));

const ContentStyled = styled(Box, { name, slot: 'content' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column'
}));

const TextStyled = styled(Typography, { name, slot: 'label' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(18)
}));

interface Props {
  data: any;
  loading: boolean;
  error: any;
}

function ItemView({ data, loading, error }: Props) {
  const { i18n } = useGlobal();
  const { size = 48, icon, total } = data;

  if (loading) {
    return (
      <Root>
        <SkeletonLoading />
      </Root>
    );
  }

  if (!data || error) return null;

  return (
    <Root>
      <LineIconStyled icon={icon} size={size} />
      <ContentStyled>
        <Typography color="text.secondary" variant="subtitle1">
          {total ? <FormatNumber value={total} /> : 0}
        </Typography>
        <TextStyled color="text.secondary">
          {data?.phrase
            ? i18n.formatMessage(
                { id: data.phrase.name },
                {
                  type: data.phrase.params.type,
                  total: data.phrase.params.total,
                  value: data.phrase.params.value
                }
              )
            : i18n.formatMessage({ id: 'click' })}
        </TextStyled>
      </ContentStyled>
    </Root>
  );
}

export default ItemView;
