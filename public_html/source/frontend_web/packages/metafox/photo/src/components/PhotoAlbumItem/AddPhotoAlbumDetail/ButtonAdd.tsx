import { useGlobal } from '@metafox/framework';
import { usePageParams } from '@metafox/layout';
import { LineIcon } from '@metafox/ui';
import { Button, styled, Box } from '@mui/material';
import React from 'react';

const name = 'AddDetailPhotoAlbumButton';

const Root = styled('div', { name, slot: 'root' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  justifyContent: 'center',
  alignItems: 'center'
}));
const ButtonStyle = styled(Button, { name, slot: 'root' })(({ theme }) => ({
  fontWeight: theme.typography.fontWeightBold,
  fontSize: theme.typography.pxToRem(18)
}));
const ButtonWrap = styled(Box, {
  name,
  slot: 'buttonWrap',
  shouldForwardProp: prop => prop !== 'size' && prop !== 'isProfilePage',
  overridesResolver(props, styles) {
    return [styles.buttonWrap];
  }
})<{ size?: string; isProfilePage?: boolean }>(
  ({ theme, size, isProfilePage }) => ({
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    border: theme.mixins.border('secondary'),
    borderColor:
      theme.palette.mode === 'light'
        ? theme.palette.border.primary
        : 'rgba(255, 255, 255, 0.23)',
    height: theme.spacing(4.75),
    borderRadius: theme.shape.borderRadius,
    ...(size === 'small' && {
      padding: theme.spacing(0, 1)
    }),
    ...(size !== 'small' && {
      height: 125,
      width: '100%'
    }),
    ...(size !== 'small' && {
      height: isProfilePage ? 170 : 125,
      width: '100%'
    }),
    '&:hover': {
      cursor: 'pointer',
      backgroundColor: theme.palette.button.hover,
      borderColor:
        theme.palette.mode === 'light' ? theme.palette.border.primary : '#fff'
    },
    '& .MuiButton-root.MuiButton-text:hover': {
      backgroundColor: 'transparent'
    }
  })
);

export default function AddButton(props) {
  const { identity, size } = props;
  const { i18n, dispatch } = useGlobal();
  const pageParams = usePageParams();

  const handleClick = () => {
    dispatch({ type: 'photo/quickAddToAlbum', payload: { identity } });
  };

  return (
    <Root>
      <ButtonWrap
        isProfilePage={!!pageParams?.profile_page}
        onClick={handleClick}
        size={size}
      >
        <ButtonStyle
          size="medium"
          color="primary"
          startIcon={<LineIcon icon="ico-photos-plus-o" />}
        >
          {i18n.formatMessage({ id: 'add' })}
        </ButtonStyle>
      </ButtonWrap>
    </Root>
  );
}
