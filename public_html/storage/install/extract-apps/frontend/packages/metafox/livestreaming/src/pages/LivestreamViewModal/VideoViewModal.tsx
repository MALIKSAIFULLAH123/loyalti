/**
 * @type: dialog
 * name: livestreaming.dialog.videoView
 * chunkName: dialog.livestreaming
 */

import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { connectItemView, useGlobal } from '@metafox/framework';
import * as React from 'react';
import { LivestreamDetailViewProps } from '../../types';
import actionCreators from '@metafox/livestreaming/actions/livestreamItemActions';

function LiveVideoViewDialog({
  item,
  identity,
  error,
  actions,
  user,
  searchParams
}: LivestreamDetailViewProps) {
  const { useDialog, useIsMobile, i18n, jsxBackend, useSession } = useGlobal();
  const { dialogProps } = useDialog();
  const isMobile = useIsMobile(true);
  const refViewed = React.useRef(false);
  const { user: authUser, loggedIn } = useSession();
  const isOwner = authUser?.id === user?.id;

  React.useEffect(() => {
    if (refViewed.current || !item?.is_streaming || isOwner || !loggedIn)
      return;

    // update viewer
    refViewed.current = true;
    actions.updateViewer();

    return () => {
      actions.removeViewer();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [item?.is_streaming]);

  if (!item) return null;

  return (
    <Dialog
      scroll={'body'}
      {...dialogProps}
      fullScreen={!error}
      data-testid="popupDetailLiveVideo"
    >
      {isMobile || error ? (
        <DialogTitle
          backIcon="ico-close"
          enableBack={!error}
          disableClose={isMobile}
        >
          {i18n.formatMessage({ id: 'video' })}
        </DialogTitle>
      ) : null}
      <DialogContent
        sx={{
          padding: '0 !important',
          height: isMobile ? 'auto' : '100% !important'
        }}
      >
        {jsxBackend.render({
          component: 'livevideo.ui.viewBlock',
          props: {
            identity,
            error,
            searchParams
          }
        })}
      </DialogContent>
    </Dialog>
  );
}

export default connectItemView(LiveVideoViewDialog, actionCreators);
