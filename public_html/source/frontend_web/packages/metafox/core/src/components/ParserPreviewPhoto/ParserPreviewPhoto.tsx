/**
 * @type: service
 * name: ParserPreviewPhoto
 */

import { useGlobal, FETCH_PREVIEW_PHOTO } from '@metafox/framework';
import {
  Box,
  styled,
  Skeleton,
  CircularProgress,
  Typography
} from '@mui/material';
import React from 'react';
import { LineIcon } from '@metafox/ui';
import { keyframes } from '@emotion/react';
import { reducer } from './reducer';

const hiddenAnimation = keyframes`
    0% {opacity: 1;}
    100% {opacity: 0;}
`;

const name = 'PreviewPhotos';
const IconWrapper = styled('div', {
  name,
  slot: 'icon',
  shouldForwardProp: prop => prop !== 'hide'
})<{ hide?: boolean }>(({ theme, hide }) => ({
  ...(hide && { animation: `${hiddenAnimation} 1s linear forwards` })
}));
const Root = styled(Box, {
  name,
  slot: 'remove button'
})(({ theme }) => ({
  position: 'relative'
}));

const Overlay = styled(Box)(({ theme }) => ({
  backgroundColor: theme.palette.grey[200],
  position: 'absolute',
  left: 0,
  right: 0,
  bottom: 0,
  top: 0,
  zIndex: 2,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));

function testImage(URL, callback) {
  const tester = new Image();
  tester.onload = () => callback(false);
  tester.onerror = () => callback(true);
  tester.src = URL;
}

export default function ParserPreviewPhoto({
  children,
  item,
  onParse,
  onError,
  canParse: canParseProp,
  sx,
  onStartParseFile,
  isAbort
}) {
  const { dispatch, getSetting, i18n } = useGlobal();
  const [state, fire] = React.useReducer(reducer, {
    loading: false,
    imageError: false,
    progress: 0
  });
  const src = item?.source;
  const shouldParseFile = getSetting('photo.converted_unsupported_files');
  const limitSizeParse = getSetting('photo.convertable_photo_size_limit');
  const acceptSize = limitSizeParse && item?.file?.size < limitSizeParse;
  const canParse = canParseProp || (shouldParseFile && acceptSize);
  const abortController = React.useMemo(() => new AbortController(), []);

  React.useEffect(() => {
    if (isAbort && state.loading) {
      abortController.abort();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isAbort, state.loading]);

  const setProgress = data => {
    fire({ type: 'setProgress', payload: data });
  };

  const setImageError = data => {
    fire({ type: 'setImageError', payload: data });
  };

  const onUploadProgress = event => {
    const progress = Math.round((event.loaded * 100) / event.total);

    if (progress > 99) return;

    setProgress(progress);
    // eslint-disable-next-line no-console
  };

  React.useEffect(() => {
    if (!src) return;

    testImage(src, setImageError);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [src]);
  React.useEffect(() => {
    if (state.loading) return;

    if (state.imageError && canParse) {
      onStartParseFile && onStartParseFile();
      fire({ type: 'setLoading', payload: true });
      dispatch({
        type: FETCH_PREVIEW_PHOTO,
        payload: { item },
        meta: {
          onUploadProgress,
          onParseFile,
          onError: handleError,
          abortController
        }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [state.imageError]);
  const handleError = () => {
    fire({ type: 'setLoading', payload: false });
    onError && onError();
  };

  const onParseFile = parseFile => {
    fire({ type: 'setLoaded' });
    onParse(parseFile);
  };

  if (src && state.imageError) {
    return (
      <Root sx={sx}>
        <Overlay>
          {state.loading ? (
            <>
              <Skeleton
                sx={{
                  transform: 'none',
                  position: 'absolute',
                  left: 0,
                  right: 0,
                  top: 0,
                  bottom: 0,
                  borderRadius: 0
                }}
                width="100%"
                height="100%"
              />
              <Box
                sx={{
                  position: 'relative',
                  display: 'inline-flex',
                  width: '56px',
                  height: '56px'
                }}
              >
                <IconWrapper hide={state.progress === 100}>
                  <CircularProgress
                    sx={{ width: '56px !important', height: '56px !important' }}
                    variant="determinate"
                    value={state.progress}
                  />
                </IconWrapper>
                <Box
                  sx={{
                    top: 0,
                    left: 0,
                    bottom: 0,
                    right: 0,
                    position: 'absolute',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center'
                  }}
                >
                  <LineIcon sx={{ fontSize: 18 }} icon={'ico-photo-up'} />
                </Box>
              </Box>
            </>
          ) : (
            <Box
              sx={{
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center'
              }}
            >
              <LineIcon sx={{ fontSize: 32 }} icon={'ico-file-empty-o'} />
              <Typography paragraph variant="body1" mt={2}>
                {i18n.formatMessage({ id: 'file_no_preview' })}
              </Typography>
            </Box>
          )}
        </Overlay>
        {children}
      </Root>
    );
  }

  return children;
}
