/**
 * @type: dialog
 * name: photo.dialog.simplePhoto
 */
import { useGetItem, useGlobal } from '@metafox/framework';
import { Dialog, DialogContent } from '@metafox/dialog';
import { LineIcon } from '@metafox/ui';
import { CircularProgress, Tooltip, styled } from '@mui/material';
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
const IconDownload = styled('div')(({ theme }) => ({
  position: 'fixed',
  top: theme.spacing(4),
  right: theme.spacing(10),
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
    right: theme.spacing(5.5)
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

const Progress = styled(CircularProgress)(({ theme }) => ({
  color: 'white'
}));

export type SimplePhotoDialogProps = {
  src: string;
  alt: string;
  id: number | string;
  identity: string;
};

export default function SimplePhotoDialog(props: SimplePhotoDialogProps) {
  const { useDialog, dispatch, useIsMobile, i18n } = useGlobal();
  const { src, identity } = props;
  const item = useGetItem(identity);
  
  const { dialogProps, closeDialog } = useDialog();
  const [downloading, setDownloading] = React.useState(false);

  const isMobile = useIsMobile();

  const handleDownloading = () => setDownloading(false);

  const downloadItem = () => {
    if (!identity || downloading) return;

    setDownloading(true);
    dispatch({
      type: 'core/downloadItem',
      payload: { identity },
      meta: { onSuccess: handleDownloading, onFailure: handleDownloading }
    });
  };

  return (
    <DialogStyled
      {...dialogProps}
      data-testid="popupViewPhoto"
      sx={{ backgroundColor: 'rgba(0, 0, 0, 0.9)' }}
    >
      {item?.extra?.can_download && (
        <Tooltip title={i18n.formatMessage({ id: 'download' })}>
          <IconDownload onClick={downloadItem}>
            {downloading ? (
              <Progress size={18} />
            ) : (
              <LineIcon icon={'ico-download'} />
            )}
          </IconDownload>
        </Tooltip>
      )}
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
