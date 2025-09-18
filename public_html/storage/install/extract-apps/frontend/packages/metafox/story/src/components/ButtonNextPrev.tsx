import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import React from 'react';
import { styled, SxProps } from '@mui/material/styles';
import { camelCase } from 'lodash';

const name = 'ButtonNextPrev';

const ButtonWrapper = styled('div', {
  name,
  slot: 'ButtonWrapper',
  shouldForwardProp: props =>
    props !== 'displayPosition' && props !== 'isMobile'
})<{ displayPosition: 'left' | 'right'; isMobile?: boolean }>(
  ({ theme, displayPosition, isMobile }) => ({
    display: 'flex',
    alignItems: 'center',
    flex: 1,
    justifyContent: 'flex-start',
    marginLeft: theme.spacing(2),
    ...(displayPosition === 'left' && {
      justifyContent: 'flex-end',
      marginRight: theme.spacing(2),
      marginLeft: 0
    }),
    ...(isMobile && {
      position: 'absolute',
      top: '50%',
      transform: 'translate(0, -50%)',
      zIndex: 3,
      right: 12,
      left: 'unset',
      ...(displayPosition === 'left' && {
        top: '50%',
        transform: 'translate(0, -50%)',
        left: 12,
        right: 'unset'
      })
    })
  })
);

const ControlPhotoIcon = styled('div', {
  name,
  slot: 'nextPhoto',
  shouldForwardProp: props => props !== 'isMobile' && props !== 'isShow'
})<{ isMobile?: boolean; isShow?: boolean }>(({ theme, isMobile, isShow }) => ({
  display: 'flex',
  alignItems: 'center',
  backgroundColor: theme.palette.grey[600],
  justifyContent: 'center',
  zIndex: 1,
  padding: '10px',
  borderRadius: '50%',
  width: '42px',
  height: '42px',
  cursor: 'pointer',
  color: '#000',
  '& span': {
    fontSize: theme.mixins.pxToRem(20),
    fontWeight: theme.typography.fontWeightBold,
    ...(isMobile && {
      color: '#fff'
    })
  },
  ...(!isShow && {
    display: 'none'
  })
}));

interface Props {
  show: boolean;
  onClick: () => void;
  position: 'right' | 'left';
  sxProps?: SxProps;
}

function ButtonNextPrev({ position = 'right', show, onClick, sxProps }: Props) {
  const { useIsMobile } = useGlobal();

  const isMobile = useIsMobile();

  return (
    <ButtonWrapper
      data-testid={camelCase(`buttonWrapper ${position}`)}
      isMobile={isMobile}
      displayPosition={position}
      sx={sxProps}
    >
      <ControlPhotoIcon isShow={show} isMobile={isMobile} onClick={onClick}>
        <LineIcon icon={`ico-angle-${position}`} />
      </ControlPhotoIcon>
    </ButtonWrapper>
  );
}

export default ButtonNextPrev;
