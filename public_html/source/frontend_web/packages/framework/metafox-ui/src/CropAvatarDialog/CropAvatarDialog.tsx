/**
 * @type: dialog
 * name: CropAvatarDialog
 */
import { useGlobal, FETCH_PREVIEW_PHOTO } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { getImageSrc, isNoImage } from '@metafox/utils';
import {
  Button,
  CircularProgress,
  Dialog,
  DialogTitle,
  IconButton,
  Box
} from '@mui/material';
import Slider from '@mui/material/Slider';
import { useTheme } from '@mui/material/styles';
import useMediaQuery from '@mui/material/useMediaQuery';
import { isFunction } from 'lodash';
import React, { ChangeEvent, useEffect, useReducer } from 'react';
import { getCroppedImg } from './canvasUtils';
import Cropper from './Cropper';
import { reducer, State } from './reducer';
import useStyles from './styles';
import { Area } from './types';
import resizeBase64 from '@metafox/utils/resizeBase64';
import { detect } from 'detect-browser';

export const initState: State = {
  crop: { x: 0, y: 0 },
  zoom: 1,
  flipHorizontal: false,
  flipVertical: false,
  rotation: 0,
  isDirty: false,
  isLoading: false,
  croppedAreaPixels: null,
  isSuccess: false
};

const readFile = (file: File) => {
  return new Promise(resolve => {
    const reader = new FileReader();
    reader.addEventListener('load', () => resolve(reader.result), false);
    reader.readAsDataURL(file);
  });
};

function checkImageError(URL, callback) {
  const tester = new Image();
  tester.onload = () => callback(false);
  tester.onerror = () => callback(true);
  tester.src = URL;
}

const CropAvatarDialog = ({ onUpdate, item, avatar }) => {
  const classes = useStyles();
  const theme = useTheme();
  const inputFileRef = React.useRef(null);
  const [newAvatar, setNewAvatar] = React.useState<File>(null);
  const [editMode, setEditMode] = React.useState(false);
  const {
    i18n,
    dispatch,
    dialogBackend,
    useDialog,
    useCheckFileUpload,
    getSetting,
    setNavigationConfirm
  } = useGlobal();
  const { dialogProps, setUserConfirm, closeDialog } = useDialog();
  const fullScreen = useMediaQuery(theme.breakpoints.down('sm'));
  let originalAvatarPhoto = getImageSrc(item?.avatar);
  const { name: browserName } = detect() || {};
  const { can_set_profile_avatar = true } = Object.assign({}, item?.extra);

  if (avatar) {
    originalAvatarPhoto = getImageSrc(avatar);
  }

  initState.imageSrc = originalAvatarPhoto;
  const [state, fire] = useReducer(reducer, {
    ...initState
  });

  const acceptList = getSetting<string[]>(
    'core.file_mime_type_accepts.image'
  ) || ['image/*'];
  const accept = acceptList.join();
  const [checkFiles] = useCheckFileUpload({ accept });

  const {
    crop,
    zoom,
    rotation,
    flipHorizontal,
    flipVertical,
    imageSrc,
    croppedAreaPixels,
    isLoading,
    isDirty,
    imageId,
    isSuccess,
    isLoadingParseFile,
    progressParseFile
  } = state;

  useEffect(() => {
    setNavigationConfirm && setNavigationConfirm(isDirty);
    setUserConfirm(isDirty);

    return () => {
      if (isDirty) {
        setNavigationConfirm && setNavigationConfirm(false);
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [setUserConfirm, isDirty]);

  useEffect(() => {
    if (!isSuccess) return;

    closeDialog();
  }, [isSuccess, closeDialog]);

  const handleCropComplete = (_: Area, croppedAreaPixels: Area) => {
    fire({ type: 'setCroppedAreaPixels', payload: croppedAreaPixels });
  };

  const handleRotation = () => {
    const rotateNumber = 360 <= state.rotation + 90 ? 0 : state.rotation + 90;

    fire({ type: 'setRotation', payload: rotateNumber });
  };

  const handleCroppedImage = async () => {
    fire({ type: 'pendingSave' });

    try {
      const avatar_crop = (await getCroppedImg(
        imageSrc,
        croppedAreaPixels,
        'base64',
        rotation,
        flipVertical,
        flipHorizontal
      )) as string;

      if (avatar_crop) {
        dispatch({
          type: 'updateProfileUserAvatar',
          payload: {
            avatar: newAvatar,
            avatar_crop,
            userId: item?.id,
            identity: item?._identity,
            image_id: imageId
          },
          meta: {
            onSuccess: () => {
              fire({ type: 'fullfilSave' });
            },
            onFailure: () => {
              fire({ type: 'rejectSave' });
            }
          }
        });
      }

      isFunction(onUpdate) && onUpdate(avatar);
    } catch (e) {
      dialogBackend.alert({
        title: 'Error',
        message: 'Ops! Picture format is not correct.'
      });
      fire({ type: 'rejectSave' });
    }
  };

  const onUploadProgress = event => {
    const progress = Math.round((event.loaded * 100) / event.total);

    if (progress > 99) return;

    fire({ type: 'setProgressParseFile', payload: progress });
    // eslint-disable-next-line no-console
  };

  const handleFileChange = async (e: ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && 0 < e.target.files.length) {
      const files = e.target.files;
      const filesResult = checkFiles(files);

      if (filesResult?.length) {
        const file = filesResult[0];

        let imageDataUrl = await readFile(file);

        if (['ios', 'crios'].includes(browserName)) {
          imageDataUrl = await resizeBase64({ url: imageDataUrl });
        }

        checkImageError(imageDataUrl, err => {
          if (err) {
            fire({ type: 'startParseFile' });
            dispatch({
              type: FETCH_PREVIEW_PHOTO,
              payload: {
                item: {
                  source: imageDataUrl,
                  file,
                  type: 'photo',
                  item_type: item?.module_name,
                  file_type: 'photo',
                  thumbnail_sizes: item?.avatar_thumbnail_sizes
                }
              },
              meta: {
                onUploadProgress,
                onParseFile: parseFile => {
                  const { preUploadFile, source } = parseFile;
                  fire({ type: 'successParseFile' });
                  setNewAvatar(preUploadFile);
                  fire({ type: 'updateFile', payload: source as string });
                  handleShowEditPhoto();
                },
                onError: () => {
                  fire({ type: 'errorParseFile' });

                  if (inputFileRef?.current) {
                    inputFileRef.current.value = null;
                  }
                }
              }
            });
          } else {
            setNewAvatar(file);
            fire({ type: 'updateFile', payload: imageDataUrl as string });
            handleShowEditPhoto();
          }
        });
      }
    }
  };

  const handleChangeFileButtonClick = () => {
    inputFileRef.current.click();
  };

  const handleChoosePhoto = () => {
    dispatch({
      type: 'chooseAvatarPhotoDialog',
      payload: {
        identity: item?._identity
      },
      meta: {
        onSuccess: item => {
          fire({ type: 'setFromPhoto', payload: item });
          handleShowEditPhoto();
        },
        onFailure: () => fire({ type: 'failed' })
      }
    });
  };

  const handleShowEditPhoto = () => {
    setEditMode(true);
  };

  return (
    <Dialog
      {...dialogProps}
      className={classes.cropDialog}
      aria-labelledby="dialog-title"
      maxWidth={'sm'}
      fullWidth
      scroll={'body'}
      fullScreen={fullScreen}
    >
      <DialogTitle id="dialog-title">
        {i18n.formatMessage({ id: 'update_your_profile_picture' })}
        <IconButton
          size="small"
          className={classes.btnClose}
          disabled={isLoading}
          onClick={closeDialog}
        >
          <LineIcon icon={'ico-close'} />
        </IconButton>
      </DialogTitle>

      {imageSrc && editMode ? (
        <div className={classes.cropContainer}>
          <div className={classes.cropContainer}>
            <div className={classes.cropContent}>
              <div className={classes.cropBackdrop}>
                <div className={classes.cropBlurContent}>
                  <img
                    crossOrigin="anonymous"
                    src={imageSrc}
                    alt=""
                    className={classes.blurImage}
                  />
                </div>
              </div>
              <Cropper
                key={imageSrc}
                image={imageSrc}
                crop={crop}
                zoom={zoom}
                aspect={1}
                flipHorizontal={flipHorizontal}
                flipVertical={flipVertical}
                onCropChange={location =>
                  fire({ type: 'setCrop', payload: location })
                }
                onCropComplete={handleCropComplete}
                onZoomChange={zoom =>
                  fire({
                    type: 'setZoom',
                    payload: { zoom }
                  })
                }
                rotation={rotation}
                showGrid={false}
                cropShape={'round'}
              />
            </div>
            {!isNoImage(imageSrc) && (
              <>
                <div className={classes.controls}>
                  <div className={classes.sliderContainer}>
                    <Button
                      variant="text"
                      className={classes.btnControl}
                      onClick={() =>
                        fire({ type: 'setZoom', payload: { mode: 'minus' } })
                      }
                    >
                      <LineIcon icon={'ico-minus'} />
                    </Button>
                    <Slider
                      className={classes.sliderZoom}
                      value={zoom}
                      min={1}
                      max={3}
                      step={0.1}
                      aria-labelledby="Zoom"
                      onChange={(e, zoom) =>
                        fire({
                          type: 'setZoom',
                          payload: { zoom: zoom as number }
                        })
                      }
                    />
                    <Button
                      variant="text"
                      className={classes.btnControl}
                      onClick={() =>
                        fire({ type: 'setZoom', payload: { mode: 'plus' } })
                      }
                    >
                      <LineIcon icon={'ico-plus'} />
                    </Button>
                  </div>
                </div>
                <div className={classes.btnContainer}>
                  <Button
                    startIcon={<LineIcon icon={'ico-rotate-right-alt'} />}
                    onClick={handleRotation}
                  >
                    {i18n.formatMessage({ id: 'rotate' })}
                  </Button>
                  <Button
                    startIcon={<LineIcon icon={'ico-flip-v'} />}
                    onClick={() => fire({ type: 'setFlipVertical' })}
                  >
                    {i18n.formatMessage({ id: 'flip_vertical' })}
                  </Button>
                  <Button
                    startIcon={<LineIcon icon={'ico-flip-h'} />}
                    onClick={() => fire({ type: 'setFlipHorizontal' })}
                  >
                    {i18n.formatMessage({ id: 'flip_horizontal' })}
                  </Button>
                </div>
              </>
            )}
            <div className={classes.btnActions}>
              <div className={classes.btnChangePhoto}>
                <Button
                  variant="outlined"
                  disabled={isLoading}
                  className={classes.btnAction}
                  size="small"
                  onClick={() => setEditMode(false)}
                >
                  {i18n.formatMessage({ id: 'back' })}
                </Button>
              </div>
              <Button
                variant="contained"
                className={classes.btnAction}
                disabled={!isDirty}
                onClick={handleCroppedImage}
                size="small"
              >
                {isLoading ? (
                  <CircularProgress color="inherit" size="1rem" />
                ) : (
                  i18n.formatMessage({ id: 'save' })
                )}
              </Button>
            </div>
          </div>
        </div>
      ) : (
        <Box sx={{ p: 2 }}>
          {isLoadingParseFile ? (
            <Box
              sx={{
                position: 'absolute',
                top: 0,
                left: 0,
                bottom: 0,
                right: 0,
                background:
                  theme.palette.mode === 'dark'
                    ? 'rgba(0,0,0,0.5)'
                    : 'rgba(255,255,255,0.5)',
                zIndex: 5,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center'
              }}
            >
              <CircularProgress
                sx={{ width: '56px !important', height: '56px !important' }}
                variant="determinate"
                value={progressParseFile}
              />
            </Box>
          ) : null}
          <input
            type="file"
            onChange={handleFileChange}
            accept={accept}
            className={classes.inputFile}
            ref={inputFileRef}
            style={{ display: 'none' }}
          />
          <Box
            sx={{
              display: 'flex',
              flexDirection: 'column',
              '& button': {
                margin: '0 0 8px 0 !important',
                '&:last-child': { marginBottom: '0 !important' }
              }
            }}
          >
            <Button
              variant="contained"
              disabled={isLoading}
              className={classes.btnAction}
              size="small"
              onClick={handleChangeFileButtonClick}
              startIcon={<LineIcon icon="ico-upload" />}
            >
              {i18n.formatMessage({ id: 'upload_photo' })}
            </Button>
            {can_set_profile_avatar ? (
              <Button
                variant="outlined"
                disabled={isLoading}
                className={classes.btnAction}
                size="small"
                onClick={handleChoosePhoto}
                startIcon={<LineIcon icon="ico-photo" />}
              >
                {i18n.formatMessage({ id: 'choose_from_photos' })}
              </Button>
            ) : null}
            {imageSrc ? (
              <Button
                variant="outlined"
                disabled={isLoading}
                className={classes.btnAction}
                size="small"
                onClick={handleShowEditPhoto}
                startIcon={<LineIcon icon="ico-pencil" />}
              >
                {i18n.formatMessage({ id: 'edit_avatar' })}
              </Button>
            ) : null}
          </Box>
        </Box>
      )}
    </Dialog>
  );
};

export default CropAvatarDialog;
