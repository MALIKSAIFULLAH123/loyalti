/**
 * @type: ui
 * name: livestreaming.ui.liveVideoPlayer
 */
import { useGlobal, ENTITY_REFRESH } from '@metafox/framework';
import * as React from 'react';
import { Box, styled, Button } from '@mui/material';
import { getImageSrc } from '@metafox/utils';
import { LivestreamItemShape } from '@metafox/livestreaming/types';
import VideoPlayer from '@metafox/ui/VideoPlayer';
import { LineIcon } from '@metafox/ui';
import {
  useFirestoreDocIdListener,
  useFirebaseFireStore
} from '@metafox/framework/firebase';
import { isEmpty } from 'lodash';

type Props = {
  item: LivestreamItemShape;
  dialog?: boolean;
  actions?: Record<string, any>;
  dashboard?: boolean;
};

const EndPlayer = styled(Box, {
  name: 'EndPlayer',
  shouldForwardProp: (prop: string) => prop !== 'dialog'
})<{ dialog: boolean }>(({ theme, dialog }) => ({
  position: 'relative',
  backgroundColor: '#333',
  color: '#fff',
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  fontSize: '24px',
  ...(dialog && {
    width: '100%',
    height: '100%'
  }),
  '&:before': {
    content: '""',
    display: 'block',
    paddingBottom: '56.25%'
  }
}));

const EndPlayerContent = styled(Box, {
  name: 'EndPlayer',
  shouldForwardProp: (prop: string) => prop !== 'dialog'
})<{ dialog: boolean }>(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  position: 'absolute',
  top: 0,
  left: 0,
  right: 0,
  bottom: 0
}));

const WrapperButtons = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  '& > *': {
    marginRight: `${theme.spacing(1)} !important`
  }
}));
type LiveProps = {
  time_limit_warning?: boolean;
  status: string;
  stream_key: string;
  end_date?: string;
  is_approved?: boolean;
};

function LiveVideoPlayer({ item, dialog, actions, dashboard }: Props) {
  const { i18n, dialogBackend, moment, dispatch, jsxBackend } = useGlobal();
  const {
    video_url,
    thumbnail_url,
    is_owner,
    is_streaming,
    extra,
    is_pending,
    stream_key,
    webcamConfig = {}
  } = item || {};
  const db = useFirebaseFireStore();
  const dataLive = useFirestoreDocIdListener<LiveProps>(db, {
    collection: 'live_video',
    docID: stream_key
  });
  const WebcamPlayer = jsxBackend.get('livevideo.ui.webcamPlayer');

  const handleDeleteLiveVideo = React.useCallback(() => {
    actions.deleteItem();
  }, []);
  const offline =
    ['idle', 'deleted'].includes(dataLive?.status) || !is_streaming;
  const isApprovePending = dataLive?.is_approved;
  const showWarningLimitTime =
    is_streaming && is_owner && dashboard && dataLive?.time_limit_warning;
  const minuteRemain = dataLive?.end_date
    ? moment(dataLive?.end_date).diff(new Date(), 'minutes')
    : '';

  const onSuccess = () => {
    if (is_owner && dashboard) {
      dialogBackend.alert({
        message: i18n.formatMessage({
          id: 'live_video_approved_successfully_notification'
        })
      });
    }
  };

  React.useEffect(() => {
    if (item._identity && isApprovePending && is_pending) {
      dispatch({
        type: ENTITY_REFRESH,
        payload: { identity: item._identity },
        meta: { onSuccess }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isApprovePending, is_pending, item._identity]);

  React.useEffect(() => {
    if (offline && actions) {
      actions.updateStatusOffline();
    }

    return () => {
      if (offline && actions) {
        actions.removeLiveWatching();
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [offline, actions]);

  React.useEffect(() => {
    if (showWarningLimitTime && minuteRemain > 0) {
      dialogBackend.alert({
        title: i18n.formatMessage({ id: 'timeout' }),
        message: i18n.formatMessage(
          { id: 'the_live_video_will_end_in_n_minutes' },
          {
            value: minuteRemain
          }
        )
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [showWarningLimitTime, minuteRemain]);

  if (!item) return null;

  const cover = getImageSrc(thumbnail_url, '500', '');

  if (offline) {
    return (
      <EndPlayer dialog={dialog}>
        <EndPlayerContent>
          <LineIcon icon="ico-video" sx={{ fontSize: 40, mb: 2 }} />
          {i18n.formatMessage({
            id: is_owner ? 'your_live_video_has_ended' : 'live_video_had_ended'
          })}
          {is_owner ? (
            <WrapperButtons mt={2}>
              {extra?.can_delete ? (
                <Button
                  data-testid="buttonDeleteLiveVideo"
                  role="button"
                  tabIndex={1}
                  autoFocus
                  variant="contained"
                  disableRipple
                  size="medium"
                  color="error"
                  onClick={handleDeleteLiveVideo}
                  sx={{ minWidth: 100 }}
                >
                  {i18n.formatMessage({ id: 'delete' })}
                </Button>
              ) : null}
              <Button
                data-testid="buttonViewLiveVideo"
                role="button"
                tabIndex={2}
                variant="contained"
                disableRipple
                size="medium"
                color="primary"
                href={item.link || `/live-video/${item?.id}`}
                sx={{ minWidth: 100 }}
              >
                {i18n.formatMessage({ id: 'view' })}
              </Button>
            </WrapperButtons>
          ) : null}
        </EndPlayerContent>
      </EndPlayer>
    );
  }

  if (dashboard && is_owner && !isEmpty(webcamConfig))
    return (
      <WebcamPlayer
        sxDeviceWrapper={{ p: 2 }}
        streamKey={stream_key}
        deviceDefault={webcamConfig}
        id={item.id}
      />
    );

  return <VideoPlayer src={video_url} thumb_url={cover} autoPlay />;
}

export default LiveVideoPlayer;
