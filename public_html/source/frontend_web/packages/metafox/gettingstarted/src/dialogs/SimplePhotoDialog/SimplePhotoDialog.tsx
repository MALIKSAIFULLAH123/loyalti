/**
 * @type: dialog
 * name: gettingStarted.dialog.simplePhoto
 */
import { useGlobal } from '@metafox/framework';
import { Dialog, DialogContent } from '@metafox/dialog';
import { LineIcon } from '@metafox/ui';
import { Tooltip, styled } from '@mui/material';
import React from 'react';

const DialogStyled = styled(Dialog)(({ theme }) => ({
  '& .MuiDialog-paper': {
    background: 'transparent'
  }
}));

const IconClose = styled('div')(({ theme }) => ({
  position: 'fixed',
  top: theme.spacing(4),
  right: theme.spacing(4),
  cursor: 'pointer',
  width: theme.spacing(5),
  height: theme.spacing(5),
  fontSize: theme.mixins.pxToRem(18),
  color:
    theme.palette.mode === 'light' ? theme.palette.background.paper : '#fff',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  zIndex: '2',
  [theme.breakpoints.down('md')]: {
    top: theme.spacing(0.5),
    right: theme.spacing(0.5)
  }
}));

const RootDialogContent = styled(DialogContent)(({ theme }) => ({
  padding: '0 !important',
  paddingTop: '0 !important',
  display: 'flex',
  overflowY: 'visible',
  zIndex: '1',
  flexFlow: 'column',
  justifyContent: 'center'
}));

const DialogImage = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  position: 'relative'
}));

const ImgStyled = styled('img', {
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  maxHeight: ' calc(100vh - 88px)',
  margin: 'auto',
  borderRadius: theme.shape.borderRadius,
  [theme.breakpoints.down('sm')]: {
    borderRadius: 0
  },
  ...(isMobile && {
    width: '100%'
  })
}));

export type SimplePhotoDialogProps = {
  src: string;
  alt: string;
  id: number | string;
};

export default function SimplePhotoDialog(props: SimplePhotoDialogProps) {
  const { useDialog, useIsMobile, i18n } = useGlobal();
  const { src } = props;
  const { dialogProps, closeDialog } = useDialog();

  const isMobile = useIsMobile();

  return (
    <DialogStyled
      {...dialogProps}
      data-testid="popupViewPhoto"
      sx={{ backgroundColor: 'rgba(0, 0, 0, 0.9)' }}
    >
      <Tooltip title={i18n.formatMessage({ id: 'press_esc_to_close' })}>
        <IconClose onClick={closeDialog}>
          <LineIcon icon="ico-close" />
        </IconClose>
      </Tooltip>
      <RootDialogContent dividers={false}>
        <DialogImage>
          <ImgStyled src={src} alt="" isMobile={isMobile} />
        </DialogImage>
      </RootDialogContent>
    </DialogStyled>
  );
}
