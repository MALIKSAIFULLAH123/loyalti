import { useGlobal } from '@metafox/framework';
import { ClickOutsideListener } from '@metafox/ui';
import { Collapse, Paper, styled, SxProps } from '@mui/material';
import React from 'react';
import { WidthInteractionLandscape } from '@metafox/story/constants';

const name = 'WapperItemInteraction';

const RootStyled = styled(Paper, {
  name,
  slot: 'root',
  shouldForwardProp: props => props !== 'isMinHeight'
})<{ isMinHeight?: boolean }>(({ theme, isMinHeight }) => ({
  width: '100%',
  maxWidth: '100%',
  height: '85%',
  position: 'absolute',
  bottom: 0,
  right: 0,
  borderTopLeftRadius: theme.shape.borderRadius,
  borderTopRightRadius: theme.shape.borderRadius,
  backgroundColor: theme.palette.background.paper,
  paddingTop: theme.spacing(2.5),
  display: 'flex',
  flexDirection: 'column',
  zIndex: 9999,
  ...(isMinHeight && {
    left: '50%',
    transform: 'translate(-50%, 0)',
    width: WidthInteractionLandscape
  })
}));

interface Props {
  children: React.ReactNode;
  setOpen: any;
  open: boolean;
  isMinHeight?: boolean;
  sxProps?: SxProps;
}

const WapperItemInteraction = ({
  children,
  setOpen,
  open,
  isMinHeight = false,
  sxProps = {}
}: Props) => {
  const { useIsMobile } = useGlobal();

  const isMobile = useIsMobile(true);

  const onClickAway = () => {
    if (!isMobile) return;

    setOpen(false);
  };

  if (!open) return null;

  return (
    <Collapse in={open} orientation="vertical">
      <ClickOutsideListener onClickAway={onClickAway}>
        <RootStyled
          data-testid="wrapper-interaction"
          isMinHeight={isMinHeight}
          sx={sxProps}
        >
          {children}
        </RootStyled>
      </ClickOutsideListener>
    </Collapse>
  );
};

export default WapperItemInteraction;
