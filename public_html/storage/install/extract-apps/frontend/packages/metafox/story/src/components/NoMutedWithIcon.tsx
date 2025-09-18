/**
 * @type: ui
 * name: store.block.no_muted_with_icon
 * title: No muted with description
 */

import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, Typography } from '@mui/material';
import * as React from 'react';
import { styled } from '@mui/material/styles';

const name = 'NoContentWithIcon';

const Root = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  justifyContent: 'center',
  padding: theme.spacing(2),
  alignItems: 'center'
}));

const LineIconStyled = styled(LineIcon, { name, slot: 'LineIcon' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(50),
    color: theme.palette.text.secondary,
    marginBottom: theme.spacing(2)
  })
);

const ContentStyled = styled(Typography, { name, slot: 'content' })(
  ({ theme }) => ({
    textAlign: 'center',
    [theme.breakpoints.down('xs')]: {
      fontSize: theme.mixins.pxToRem(15)
    }
  })
);

interface NoContentWithIconProps {
  icon: string;
  description?: string;
  variant?: string;
  color?: string;
}

export default function NoContentWithIcon({
  icon,
  description,
  variant = 'subtitle2',
  color = 'text.secondary'
}: NoContentWithIconProps) {
  const { i18n } = useGlobal();

  return (
    <Root data-testid="noMutedFound">
      <LineIconStyled icon={icon} />
      {description && (
        <ContentStyled variant={variant} color={color}>
          {i18n.formatMessage({ id: description })}
        </ContentStyled>
      )}
    </Root>
  );
}
