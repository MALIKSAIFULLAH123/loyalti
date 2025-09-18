import { useGlobal } from '@metafox/framework';
import { LineIcon, TruncateText } from '@metafox/ui';
import { Box, styled, SxProps, Tooltip } from '@mui/material';
import { get } from 'lodash';
import React from 'react';

const name = 'TourGuideHeaderDock';

const HeaderContent = styled(Box, {
  name,
  slot: 'headercontent',
  shouldForwardProp: props => props !== 'colorItem' && props !== 'isNewDock'
})<{ colorItem?: string; isNewDock?: boolean }>(
  ({ theme, colorItem, isNewDock }) => ({
    padding: theme.spacing(1.5, 2),
    display: 'flex',
    color:
      colorItem && get(theme.palette, colorItem)
        ? get(theme.palette, colorItem)
        : colorItem ?? theme.palette.text?.primary,
    ...(isNewDock && {
      borderBottom: theme.mixins.border('secondary')
    })
  })
);

const IconCloseStyled = styled('div', {
  name,
  slot: 'removeBtn',
  shouldForwardProp: props => props !== 'isNewDock'
})<{ isNewDock?: boolean }>(({ theme, isNewDock }) => ({
  width: 24,
  transform: 'translate(8px, 0)',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  cursor: 'pointer',
  ':hover': {
    backgroundColor: theme.palette.action.hover,
    borderRadius: theme.shape.borderRadius * 1.5
  },
  '& .ico': {
    fontSize: theme.mixins.pxToRem(16),
    color: theme.palette.text.primary,
    ...(isNewDock && {
      color: 'inherit'
    })
  }
}));

interface Props {
  color?: string;
  title?: string;
  onClose?: (hasConfirm?: boolean) => void;
  hasConfirmClose?: boolean;
  sx?: SxProps;
  isNewDock?: boolean;
  showFull?: boolean;
  [key: string]: any;
}

function HeaderDock({
  color,
  title = 'tourguide_new_here',
  onClose = () => {},
  hasConfirmClose = true,
  isNewDock = false,
  showFull = false,
  ...rest
}: Props) {
  const { i18n } = useGlobal();

  return (
    <HeaderContent isNewDock={isNewDock} colorItem={color} {...rest}>
      {title ? (
        <TruncateText
          lines={1}
          showFull={showFull}
          variant="h4"
          sx={{ flex: 1, minWidth: 0 }}
        >
          {i18n.formatMessage({ id: title })}
        </TruncateText>
      ) : null}
      {isNewDock ? (
        <Tooltip title={i18n.formatMessage({ id: 'close' })}>
          <IconCloseStyled
            isNewDock={isNewDock}
            onClick={() => onClose(hasConfirmClose)}
          >
            <LineIcon icon="ico-close" />
          </IconCloseStyled>
        </Tooltip>
      ) : null}
    </HeaderContent>
  );
}

export default HeaderDock;
