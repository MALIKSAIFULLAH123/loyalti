import { useGetItem, useGlobal } from '@metafox/framework';
import { Container, LineIcon } from '@metafox/ui';
import { ProfileHeaderCoverProps } from '@metafox/user/types';
import { filterShowWhen } from '@metafox/utils';
import { Button, CircularProgress, Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import clsx from 'clsx';
import React, { useCallback, useEffect, useReducer, useRef } from 'react';
import { reducer } from './reducer';
import useStyles from './styles';
import loadable from '@loadable/component';

// cut off 60kb from bundle.
const Draggable = loadable(
  () => import(/* webpackChunkName: "reactDraggable" */ 'react-draggable')
);

const CoverStatic = styled(Box, {
  name: 'CoverStatic',
  shouldForwardProp: props => props !== 'position'
})<{ position: string | number }>(({ theme, position }) => ({
  '& img': {
    border: 'none',
    height: '100%',
    objectPosition: 'top',
    ...(position && {
      transform: `translateY(${position})`,
      height: 'auto !important',
      minHeight: '100%'
    })
  }
}));

const EditCoverButton = styled(Button, {
  name: 'EditCoverButton'
})(({ theme }) => ({
  textTransform: 'capitalize',
  position: 'absolute',
  top: theme.spacing(2),
  right: theme.spacing(2),
  zIndex: 1,
  '&:hover': {
    backgroundColor: theme.palette.divider
  }
}));

const ImagePositionBox = styled('div', {
  name: 'ProfileHeaderCoverPhoto',
  slot: 'ImagePositionBox',
  overridesResolver(props, styles) {
    return [styles.ImagePositionBox];
  }
})(({ theme }) => ({
  overflow: 'hidden',
  position: 'relative',
  borderBottomWidth: 'thin',
  borderBottomStyle: 'solid',
  borderBottomColor: theme.palette.border.secondary,
  zIndex: 0
}));

export const getTop = (top, imgHeight) => {
  return `${top}`.includes('%')
    ? top
    : `${((top / imgHeight) * 100).toFixed(2)}%`;
};

export default function ProfileBanner({
  image: defaultImage,
  identity,
  left = 0,
  top,
  alt,
  isUpdateAvatar = false
}: ProfileHeaderCoverProps) {
  const classes = useStyles();
  const {
    i18n,
    getSetting,
    ItemActionMenu,
    dispatch,
    useIsMobile,
    useCheckFileUpload,
    ParserPreviewPhoto
  } = useGlobal();
  const [isLoading, setLoading] = React.useState<boolean>(false);
  const inputRef = useRef<HTMLInputElement>();
  const isMobile = useIsMobile();
  const acceptList = getSetting<string[]>(
    'core.file_mime_type_accepts.image'
  ) || ['image/*'];
  const accept = acceptList.join();
  const [checkFiles] = useCheckFileUpload({ accept });
  const item = useGetItem(identity);
  const { can_edit } = Object.assign({}, item?.extra);
  const outerRef = React.useRef<HTMLDivElement>();

  const [state, fire] = useReducer(reducer, {
    defaultImage,
    left,
    top: top || 0,
    imgHeight: 0,
    image: defaultImage,
    menuOpen: false,
    enable: true,
    dragging: false,
    position: { x: left, y: top },
    file: undefined,
    bounds: {
      top: 0,
      left: 0,
      right: 0,
      bottom: 0
    }
  });

  const hasBanner = Boolean(item?.image);

  const onLoad = useCallback(
    (evt: any) => {
      if (outerRef.current) {
        const bounding = outerRef.current.getBoundingClientRect();
        const imgHeight = evt.target.height;

        const topParse = `${state?.top}`.includes('%')
          ? (imgHeight * parseFloat(`${state?.top}`)) / 100
          : state?.top;

        fire({
          type: 'setImgHeight',
          payload: {
            wrapHeight: bounding?.height || 0,
            imgHeight,
            top: topParse
          }
        });
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [state?.top]
  );

  const onControlledDrag = useCallback((_, { x, y }) => {
    fire({ type: 'dragging', payload: { x, y } });
  }, []);

  const handleResetValue = (
    event: React.MouseEvent<HTMLInputElement, MouseEvent>
  ) => {
    event.currentTarget.value = null;
  };

  const handleCancelClick = useCallback(() => {
    fire({ type: 'cancel' });
    setLoading(false);
  }, []);

  useEffect(() => {
    if (state.dragging) return;

    fire({
      type: 'defaultImage',
      payload: { defaultImage, position: { x: left, y: top } }
    });
  }, [defaultImage, left, top]);

  const handleErrorParseFile = useCallback(() => {
    handleCancelClick();
    fire({ type: 'loadingParseFile', payload: false });
  }, [handleCancelClick]);

  const handleSaveClick = useCallback(() => {
    setLoading(true);
    fire({ type: 'saving' });
    const top = `${((state.position.y / state.imgHeight) * 100).toFixed(2)}%`;

    dispatch({
      type: 'event/updateProfileBanner',
      payload: {
        identity,
        position: { y: top },
        file: state.file,
        tempFile: state.tempFile
      },
      meta: {
        onSuccess: () => {
          fire({
            type: 'success',
            payload: { image: state.image, top: state.position.y.toString() }
          });
          setLoading(false);
        },
        onFailure: () => {
          setLoading(false);
          fire({ type: 'failed' });
        }
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [state]);

  const handleFileInputChanged = useCallback(() => {
    if (!inputRef.current.files.length) return;

    const files = inputRef.current.files;
    const filesResult = checkFiles(files);

    if (filesResult?.length) {
      const file = filesResult[0];

      fire({ type: 'setFile', payload: file });
    }
  }, [checkFiles]);

  const handleUploadPhotoClick = useCallback(() => {
    setImmediate(() => {
      inputRef.current.click();
    });
  }, []);

  const handleRemovePhoto = useCallback(() => {
    dispatch({
      type: 'event/removeBanner',
      payload: {
        identity
      },
      meta: {
        onSuccess: () => {
          fire({ type: 'resetPosition' });
        },
        onFailure: () => fire({ type: 'failed' })
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const setImage = useCallback(data => {
    const { source, preUploadFile } = data;
    fire({ type: 'setTempFile', payload: { source, tempFile: preUploadFile } });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);
  const onStartParseFile = useCallback(() => {
    fire({ type: 'loadingParseFile', payload: true });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);
  const items = filterShowWhen(
    [
      {
        icon: 'ico-arrows-move',
        label: i18n.formatMessage({ id: 'reposition' }),
        value: 'reposition',
        testid: 'reposition',
        showWhen: [
          'and',
          ['truthy', 'hasBanner'],
          ['truthy', 'can_edit'],
          ['falsy', 'isMobile']
        ]
      },
      {
        icon: 'ico-photo-up-o',
        label: i18n.formatMessage({ id: 'upload_photo' }),
        value: 'upload_photo',
        testid: 'upload_photo',
        showWhen: ['truthy', 'can_edit']
      },

      {
        as: 'divider',
        testid: 'divider',
        showWhen: [
          'and',
          ['truthy', 'can_edit'],
          ['falsy', 'isMobile'],
          ['truthy', 'hasBanner']
        ]
      },
      {
        icon: 'ico-trash-o',
        label: i18n.formatMessage({ id: 'remove' }),
        value: 'remove_photo',
        testid: 'remove_photo',
        showWhen: ['and', ['truthy', 'can_edit'], ['truthy', 'hasBanner']]
      }
    ],
    { can_edit, isMobile, hasBanner }
  );

  const handleAction = (type: string, data: any, meta: any) => {
    switch (type) {
      case 'reposition':
        fire({ type: 'reposition' });
        break;
      case 'upload_photo':
        handleUploadPhotoClick();
        break;
      case 'remove_photo':
        handleRemovePhoto();
        break;
    }
  };

  const itemFile = {
    source: state.image,
    file: state.file,
    type: 'photo',
    item_type: item?.module_name,
    file_type: 'photo',
    thumbnail_sizes: item?.cover_thumbnail_sizes
  };

  return (
    <div className={classes.root}>
      <input
        type="file"
        aria-hidden
        className="srOnly"
        ref={inputRef}
        accept={accept}
        onChange={handleFileInputChanged}
        onClick={handleResetValue}
      />
      <Container maxWidth={'md'} gutter>
        <Box sx={{ position: 'relative' }}>
          {can_edit && !state.dragging ? (
            <ItemActionMenu
              label={i18n.formatMessage({ id: 'edit_cover_photo' })}
              id="editCoverPhoto"
              items={items}
              disablePortal
              handleAction={handleAction}
              control={
                <EditCoverButton
                  aria-label="Edit cover photo"
                  disableRipple
                  color="default"
                  size="small"
                >
                  <LineIcon
                    icon={'ico-camera'}
                    className={classes.iconEditCover}
                  />
                  <span className={classes.textEditCover}>
                    {i18n.formatMessage({ id: 'edit_cover_photo' })}
                  </span>
                </EditCoverButton>
              }
            />
          ) : null}
        </Box>
        {state.dragging ? (
          <>
            {!isLoading && !isMobile && !state.loadingParseFile && (
              <div className={classes.repositionMessage} draggable="false">
                {i18n.formatMessage({ id: 'drag_to_reposition_your_cover' })}
              </div>
            )}
            <Box sx={{ position: 'relative' }}>
              <div className={classes.controlGroup}>
                <Button
                  variant="contained"
                  color="default"
                  className={clsx(classes.btnControl, classes.btnCancel)}
                  onClick={handleCancelClick}
                  disabled={isLoading || state.loadingParseFile}
                  size="small"
                >
                  {i18n.formatMessage({ id: 'cancel' })}
                </Button>
                <Button
                  variant="contained"
                  color="primary"
                  className={classes.btnControl}
                  onClick={handleSaveClick}
                  size="small"
                  disabled={isLoading || state.loadingParseFile}
                >
                  {isLoading ? (
                    <CircularProgress color="inherit" size="1.5rem" />
                  ) : (
                    i18n.formatMessage({ id: 'save' })
                  )}
                </Button>
              </div>
            </Box>
          </>
        ) : null}
      </Container>
      <ImagePositionBox>
        <div
          className={classes.bgBlur}
          style={{
            backgroundImage: `url(${state.image})`
          }}
        />
        <Container
          maxWidth={'md'}
          gutter={!isUpdateAvatar}
          sx={{ height: '100%' }}
        >
          <Box
            sx={{
              position: 'relative',
              '&:before': {
                content: '""',
                display: 'block',
                paddingBottom: '31.25%'
              }
            }}
          >
            <Box
              ref={outerRef}
              sx={{
                position: 'absolute',
                left: 0,
                top: 0,
                right: 0,
                bottom: 0
              }}
            >
              {state.dragging ? (
                <Draggable
                  disabled={
                    !state.dragging ||
                    isLoading ||
                    isMobile ||
                    state.loadingParseFile
                  }
                  position={state.position}
                  axis="y"
                  bounds={state.bounds}
                  onDrag={onControlledDrag}
                >
                  <div
                    className={clsx(
                      classes.imageDrag,
                      state.dragging && !isMobile && classes.isReposition
                    )}
                  >
                    <ParserPreviewPhoto
                      canParse
                      item={itemFile}
                      onParse={setImage}
                      onStartParseFile={onStartParseFile}
                      onError={handleErrorParseFile}
                      sx={{ height: '100%', paddingBottom: '31.25%' }}
                    >
                      <img
                        src={state.image}
                        alt={alt}
                        className={clsx(
                          classes.imageCover,
                          state.dragging && !isMobile && classes.isReposition
                        )}
                        onLoad={onLoad}
                      />
                    </ParserPreviewPhoto>
                    <div className={classes.overBg}></div>
                  </div>
                </Draggable>
              ) : (
                <CoverStatic position={top}>
                  <img
                    src={state.image}
                    alt={alt}
                    className={clsx(
                      classes.imageCover,
                      state.dragging && !isMobile && classes.isReposition
                    )}
                    onLoad={onLoad}
                  />
                </CoverStatic>
              )}
            </Box>
          </Box>
        </Container>
      </ImagePositionBox>
    </div>
  );
}
