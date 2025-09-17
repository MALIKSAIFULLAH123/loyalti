import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { MediaViewModalProps } from '@metafox/photo/types';
import { styled, Box } from '@mui/material';
import * as React from 'react';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';

const name = 'ViewMediaModal';

const Root = styled(DialogContent, { name, slot: 'root' })(({ theme }) => ({
  height: '100%',
  padding: '0 !important',
  paddingTop: '0 !important',
  display: 'flex',
  overflowY: 'visible',
  [theme.breakpoints.down('md')]: {
    height: 'auto',
    flexFlow: 'column',
    flexDirection: 'column',
    '& > div': {
      overflow: 'inherit'
    }
  }
}));

export default function ViewMediaModal(props: MediaViewModalProps) {
  const { identity, photo_set, photo_album, photo_id, media_type, error } =
    props;
  const { jsxBackend, useDialog, i18n, useIsMobile, useGetItem } = useGlobal();
  const item = useGetItem(identity);
  const isMobile = useIsMobile(true);

  const PhotoItemViewMobile = jsxBackend.get('media.ui.viewBlockMobileModal');
  const MediaViewContainer = jsxBackend.get('media.ui.viewBlock');

  const { dialogProps } = useDialog();

  if (!item) return null;

  if (isMobile) {
    return (
      <Dialog
        {...dialogProps}
        fullScreen={!error}
        scroll="body"
        data-testid="popupViewPhoto"
      >
        <DialogTitle enableBack={!error && isMobile} disableClose={isMobile}>
          {i18n.formatMessage({ id: 'photo' })}
        </DialogTitle>
        <ErrorBoundary error={error}>
          <Box pb={2}>
            <PhotoItemViewMobile {...props} />
          </Box>
        </ErrorBoundary>
      </Dialog>
    );
  }

  return (
    <Dialog
      scroll={'body'}
      {...dialogProps}
      fullScreen={!error}
      data-testid="popupDetailPhoto"
    >
      {error ? (
        <DialogTitle backIcon="ico-close" enableBack={!error}>
          {i18n.formatMessage({ id: 'photo' })}
        </DialogTitle>
      ) : null}
      <ErrorBoundary error={error}>
        <Root dividers={false}>
          <MediaViewContainer
            photo_set={photo_set}
            photo_album={photo_album}
            identity={identity}
            photo_id={photo_id}
            media_type={media_type}
          />
        </Root>
      </ErrorBoundary>
    </Dialog>
  );
}
