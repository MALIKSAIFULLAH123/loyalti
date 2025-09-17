import { useGlobal, useLocation } from '@metafox/framework';
import {
  MediaViewDetailProps,
  ACTION_ADD_TAG,
  ACTION_REMOVE_TAG
} from '@metafox/photo';
import { styled, Box } from '@mui/material';
import * as React from 'react';

const name = 'PhotoViewContainer';

const Root = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  width: '100%',
  height: '100%',
  padding: '0 !important',
  paddingTop: '0 !important',
  display: 'flex',
  overflowY: 'visible',
  background: theme.palette.background.paper,
  outline: 'none !important',
  [theme.breakpoints.down('md')]: {
    height: 'auto',
    flexFlow: 'column',
    flexDirection: 'column',
    '& > div': {
      overflow: 'inherit'
    }
  }
}));

const DialogStatistic = styled('div', {
  name,
  slot: 'dialogStatistic',
  shouldForwardProp: prop => prop !== 'isExpand'
})<{
  isExpand: boolean;
}>(({ theme, isExpand }) => ({
  display: isExpand ? 'none' : 'block',
  height: '100%',
  width: '480px',
  [theme.breakpoints.down('md')]: {
    width: '35%'
  },
  [theme.breakpoints.down('sm')]: {
    width: '45%'
  },
  [theme.breakpoints.down('xs')]: {
    width: '100%',
    height: '420px'
  }
}));

export default function PhotoViewContainer({
  item,
  identity,
  nextUrl,
  prevUrl,
  mediaType,
  loading,
  sx,
  shouldPreload,
  fromResource,
  fromResourceId
}: MediaViewDetailProps) {
  const { jsxBackend, dispatch, navigate } = useGlobal();
  const location = useLocation();

  const [isExpand, setExpand] = React.useState<boolean>(false);

  const PhotoSideBlock = jsxBackend.get('photo.view.sideBlock');
  const MediaViewSet = jsxBackend.get('media.ui.viewSet');

  React.useEffect(() => {
    if (!shouldPreload) return;

    dispatch({
      type: `photo/${fromResource}/LOAD`,
      payload: {
        identity,
        direction: shouldPreload,
        [fromResource]: fromResourceId
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [shouldPreload]);

  const onAddPhotoTag = (data: unknown) => {
    dispatch({ type: ACTION_ADD_TAG, payload: { identity, data } });
  };

  const onRemovePhotoTag = (id: unknown) => {
    dispatch({ type: ACTION_REMOVE_TAG, payload: { identity, id } });
  };

  const onMinimizePhoto = (minimize: boolean) => {
    setExpand(minimize);
  };

  const onArrowClick = React.useCallback(
    url => {
      navigate(
        { pathname: url },
        { replace: true, state: { asModal: location?.state?.asModal } }
      );
    },
    [navigate, location?.state?.asModal]
  );

  React.useEffect(() => {
    const handleKeyDown = (e: any) => {
      if (e.keyCode === 39 && nextUrl) {
        onArrowClick(nextUrl);
      } else if (e.keyCode === 37 && prevUrl) {
        onArrowClick(prevUrl);
      }
    };

    window.addEventListener('keydown', handleKeyDown);

    return () => {
      window.removeEventListener('keydown', handleKeyDown);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [nextUrl, prevUrl]);

  if (!item) return null;

  return (
    <Root sx={sx}>
      <MediaViewSet
        mediaType={mediaType}
        identity={identity}
        onAddPhotoTag={onAddPhotoTag}
        onRemovePhotoTag={onRemovePhotoTag}
        onMinimizePhoto={onMinimizePhoto}
        onArrowClick={onArrowClick}
        nextUrl={nextUrl}
        prevUrl={prevUrl}
        sx={{ flex: 1, minWidth: 0 }}
      />
      <DialogStatistic isExpand={isExpand}>
        {PhotoSideBlock ? (
          <PhotoSideBlock loading={loading} identity={identity} />
        ) : null}
      </DialogStatistic>
    </Root>
  );
}
