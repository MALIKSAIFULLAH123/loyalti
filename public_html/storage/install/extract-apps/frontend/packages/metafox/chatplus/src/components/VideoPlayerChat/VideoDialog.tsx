/**
 * @type: dialog
 * name: chatplus.dialog.VideoPlayer
 */

import { triggerClick } from '@metafox/chatplus/utils';
import { Dialog, DialogContent } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { styled, Tooltip } from '@mui/material';
import * as React from 'react';
import { isEmpty } from 'lodash';
import VideoPlayerChat from './index';

const name = 'dialogItemView';

const IconClose = styled('div', {
  name,
  slot: 'ico-close',
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
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
  zIndex: 1000,
  '&:hover': {
    background: theme.palette.grey['800'],
    borderRadius: '50%'
  },
  ...(isMobile && {
    top: theme.spacing(2.5),
    right: theme.spacing(2.5)
  })
}));

const IconDownLoad = styled('div', {
  name,
  slot: 'ico-download',
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
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
  zIndex: 1000,
  '&:hover': {
    background: theme.palette.grey['800'],
    borderRadius: '50%'
  },
  ...(isMobile && {
    top: theme.spacing(2.5),
    right: theme.spacing(8.125)
  })
}));
const RootDialogContent = styled(DialogContent, {
  name,
  slot: 'content',
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  padding: '0 !important',
  paddingTop: '0 !important',
  display: 'flex',
  overflowY: 'visible',
  [theme.breakpoints.down('xs')]: {
    flexFlow: 'column'
  },
  ...(isMobile && {
    width: '100%',
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: theme.palette.grey['A400']
  })
}));

const DialogVideo = styled('div', {
  name,
  slot: 'DialogVideo',
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  position: 'relative',
  '& video': {
    maxHeight: `calc(100vh - ${isMobile ? '150px' : '80px'})`
  }
}));

type IProps = {
  src?: string;
  currentTime?: string;
  thumbUrl?: string;
};

export default function VideoDialog({ src, currentTime, thumbUrl }: IProps) {
  const { useDialog, useIsMobile, i18n } = useGlobal();
  const isMobile = useIsMobile();
  const { dialogProps, closeDialog } = useDialog();

  const downloadImage = (src: string) => {
    if (!src) return;

    triggerClick(src, false, true);
  };

  if (isEmpty(src)) return;

  return (
    <Dialog
      {...dialogProps}
      data-testid="popupDetailVideo"
      maxWidth={isMobile ? 'xl' : 'md'}
      fullScreen={isMobile ? true : false}
      fullWidth={false}
      scroll="body"
      sx={{ backgroundColor: 'rgba(0, 0, 0, 0.9)' }}
      PaperProps={{
        style: {
          background: 'transparent'
        }
      }}
    >
      <Tooltip
        arrow={false}
        placeholder="top"
        title={i18n.formatMessage({ id: 'download' })}
      >
        <IconDownLoad onClick={() => downloadImage(src)} isMobile={isMobile}>
          <LineIcon icon="ico-download" />
        </IconDownLoad>
      </Tooltip>
      <Tooltip
        arrow={false}
        placeholder="top"
        title={i18n.formatMessage({ id: 'press_esc_to_close' })}
      >
        <IconClose onClick={closeDialog} isMobile={isMobile}>
          <LineIcon icon="ico-close" />
        </IconClose>
      </Tooltip>
      <RootDialogContent dividers={false} isMobile={isMobile}>
        <DialogVideo isMobile={isMobile}>
          <VideoPlayerChat
            width={'100%'}
            height={'100%'}
            src={src}
            currentTime={currentTime || 0}
            autoPlay
            isMinimize={false}
            thumbUrl={thumbUrl}
            isDialog
          />
        </DialogVideo>
      </RootDialogContent>
    </Dialog>
  );
}
