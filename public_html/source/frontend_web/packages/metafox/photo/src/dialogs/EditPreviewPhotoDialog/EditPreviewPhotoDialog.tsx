import {
  BasicFileItem,
  useGlobal,
  useIsMobile,
  useSession
} from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { LineIcon } from '@metafox/ui';
import { Box, Button, TextField, styled } from '@mui/material';
import React from 'react';
import CropperImageSelection from '../../components/ImageCropperSelection';
import useCropImage from '../../hooks/useCropImage';
import StyledMenuItem from './StyledMenuItem';
import {
  getImageSrc,
  getCroppedImg,
  isPhotoType,
  isVideoType
} from '@metafox/utils';
import useTagOnImage from './useTagOnImage';
import TaggedBox from '@metafox/photo/components/TaggedBox';
import Suggestion from '@metafox/photo/components/Suggestion/Suggestion';
import PhotoTagPreview from '@metafox/photo/components/PhotoTagPreview';
import PhotoTag from '@metafox/photo/containers/PhotoTag';
import useStyles from './Tag.styles';
import VideoPlayer from '@metafox/ui/VideoPlayer';
import UploadThumbnail from './UploadThumbnail';

const DialogContentWrapper = styled(DialogContent, {
  name: 'DialogContentWrapper'
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column-reverse'
  }
}));

const LeftSide = styled('div', {
  name: 'LeftSide'
})(({ theme }) => ({
  width: 360,
  maxWidth: '100%',
  display: 'flex',
  flexDirection: 'column',
  paddingRight: theme.spacing(2),
  [theme.breakpoints.down('sm')]: {
    width: '100%',
    padding: 0,
    marginTop: theme.spacing(2)
  }
}));

const LeftSideActions = styled('div', {
  name: 'LeftSideActions'
})(({ theme }) => ({
  display: 'flex',
  flex: 1,
  flexDirection: 'column',
  marginBottom: theme.spacing(2),
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'row',
    marginBottom: theme.spacing(1),
    justifyContent: 'center',
    '& button': {
      minWidth: 'auto'
    }
  }
}));

const MainContent = styled('div', {
  name: 'MainContent'
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  backgroundColor: theme.palette.background.default,
  maxWidth: '100%',
  position: 'relative',
  textAlign: 'center',
  flex: 1,
  minWidth: 0,
  height: 'auto',
  aspectRatio: '1'
}));

const LeftSideBottom = styled('div', {
  name: 'LeftSideBottom'
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  '& > :first-of-type': {
    marginRight: theme.spacing(1)
  },
  [theme.breakpoints.down('sm')]: {
    justifyContent: 'end'
  }
}));

const Backdrop = styled('div', {
  name: 'Backdrop'
})(({ theme }) => ({
  backgroundColor: theme.mixins.backgroundColor('paper'),
  textAlign: 'center',
  overflow: 'hidden',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  position: 'absolute',
  left: 0,
  right: 0,
  top: 0,
  bottom: 0
}));

const BlurContainer = styled('div', {
  name: 'BlurContainer'
})(({ theme }) => ({
  width: '10%',
  height: '10%',
  transform: 'scale(14)',
  filter: 'blur(3px)',
  WebkitFilter: 'blur(3px)'
}));

const BlurImg = styled('img', {
  name: 'BlurImg'
})(({ theme }) => ({
  objectFit: 'cover',
  width: '100%',
  height: '100%',
  border: 0
}));

const Mask = styled('div', {
  name: 'Mask'
})(({ theme }) => ({
  position: 'absolute',
  left: 0,
  right: 0,
  top: 0,
  bottom: 0,
  backgroundColor: 'rgba(0,0,0,0.6)'
}));

const MediaContainer = styled('div', {
  name: 'MediaContainer'
})(({ theme }) => ({
  position: 'relative',
  maxWidth: '100%',
  height: '100%',
  overflow: 'hidden',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  flexDirection: 'column',
  padding: theme.spacing(2),
  flexGrow: 1
}));

const MediaWrapper = styled('div', {
  name: 'MediaWrapper'
})(({ theme }) => ({
  maxHeight: '100%',
  position: 'relative',
  justifyContent: 'center',
  alignItems: 'center',
  display: 'flex'
}));

const ImageError = styled('div', {
  name: 'ImageError'
})(({ theme }) => ({
  color: 'white',
  fontSize: theme.mixins.pxToRem(20)
}));

const LargeImage = styled('img', {
  name: 'LargeImage'
})(({ theme }) => ({
  maxWidth: '100%',
  maxHeight: '100%',
  objectFit: 'contain',
  position: 'relative',
  pointerEvents: 'none'
}));

type ThumbnailType = {
  id?: string;
  image?: any;
};

type PhotoFileItem = BasicFileItem & {
  tagged_friends?: Array<Record<string, any>>;
  thumbnail?: ThumbnailType;
  base64?: string;
  extra?: Record<string, any>;
};

export type EditPreviewPhotoDialogProps = {
  classes: Record<string, string>;
  dialogTitle: string;
  item: PhotoFileItem;
  hideTextField?: boolean;
  postAsParent?: boolean;
  parentUser?: any;
};

export type TransformState = {
  transform: 'crop' | 'ratate' | 'none';
  value?: any;
};

export type EditingActionState = 'tag' | 'crop' | 'rotate' | 'flip';

export type ImageLoadedState = 'loading' | 'error' | 'success';

export type FlipState = { x: number; y: number };

const defaultCropStyle = { top: 0, left: 0, width: '100%', height: '100%' };

const parseCropToArea = crop => {
  return {
    x: crop?.left,
    y: crop?.top,
    width: crop?.width,
    height: crop?.height
  };
};

export default function EditPreviewPhotoDialog({
  classes,
  dialogTitle = 'edit_photos',
  item: data,
  hideTextField = false,
  tagging: initialTagging,
  postAsParent,
  parentUser
}: EditPreviewPhotoDialogProps) {
  const { useDialog, i18n } = useGlobal();
  const { dialogProps, closeDialog, setDialogValue } = useDialog();
  const [item] = React.useState<PhotoFileItem>(data);
  const [imageLoaded, setImageLoaded] =
    React.useState<ImageLoadedState>('loading');
  const [rotate, setRotate] = React.useState<number>(0);
  const [flip, setFlip] = React.useState<{ x: number; y: number }>({
    x: 0,
    y: 0
  });
  const edittingActionDefault = initialTagging ? 'tag' : undefined;
  const [editingAction, setEditingAction] = React.useState<EditingActionState>(
    edittingActionDefault
  );
  const imageContainerRef = React.useRef<HTMLDivElement>();
  const imageWrapperRef = React.useRef<HTMLDivElement>();
  const imageRef = React.useRef<HTMLImageElement>();
  const cropSelectionRef = React.useRef<HTMLDivElement>();
  const srcImage = getImageSrc(item?.image);
  const [imageSource, setImageSource] = React.useState<string>(
    item.base64 || item.source || srcImage
  );
  const anchorTagRef = React.useRef<any>();
  const taggedFriendRef = React.useRef<any>(item?.tagged_friends || []);
  const [taggedFriend, setTaggedFriend] = React.useState(
    item?.tagged_friends || []
  );
  const classesTag = useStyles();
  const { user } = useSession();
  const isMobile = useIsMobile();

  const onAddPhotoTag = data => {
    setTaggedFriend(prev => [...prev, data]);
  };

  const handleRemoveTagPreview = (
    e: React.MouseEvent<{}>,
    idRemove: string
  ) => {
    e.stopPropagation();
    setTaggedFriend(prev => prev.filter(x => x.content?.id !== idRemove));
  };

  const handleRemoveTag = (e: React.MouseEvent<{}>, idRemove: string) => {
    e.stopPropagation();
    setTaggedFriend(prev => prev.filter(x => x.id !== idRemove));
  };

  const {
    onClickImageBox,
    setTagging,
    tagging,
    offsetTag,
    openTagBox,
    chooseFriendToTag
  } = useTagOnImage({
    imageRef,
    onAddPhotoTag,
    ready: imageLoaded === 'success'
  });
  const defaultPhotoCaption = React.useMemo(
    () => item?.text ?? '',
    [item?.text]
  );
  const defaultThumbnailVideo = React.useMemo(
    () => item?.thumbnail ?? undefined,
    [item?.thumbnail]
  );
  const [photoCaption, setPhotoCaption] = React.useState(defaultPhotoCaption);
  const [thumbnailVideo, setThumbnailVideo] = React.useState(
    defaultThumbnailVideo
  );
  const [crop, setCrop] = React.useState<string>();

  const onImageLoadedSuccess = React.useCallback(() => {
    setImageLoaded('success');
  }, []);
  const onImageLoadedError = React.useCallback(() => {
    setImageLoaded('error');
  }, []);

  const onChange = React.useCallback((a, b) => {}, []);

  const {
    getCropStyle,
    onFulfillSelection,
    cropSelectionStyle,
    onComponentMouseTouchDown,
    onCropMouseTouchDown
  } = useCropImage({
    imageContainerRef,
    imageWrapperRef,
    imageRef,
    cropSelectionRef,
    onChange
  });

  const startCroppingImage = React.useCallback(() => {
    if ('crop' !== editingAction) setEditingAction('crop');
  }, [editingAction]);

  const flipVertical = React.useCallback(() => {
    setFlip(prev => ({ ...prev, x: prev.x ? 0 : 1 }));

    if ('flip' !== editingAction) setEditingAction('flip');
  }, [editingAction]);

  const flipHorizontal = React.useCallback(() => {
    setFlip(prev => ({ ...prev, y: prev.y ? 0 : 1 }));

    if ('flip' !== editingAction) setEditingAction('flip');
  }, [editingAction]);

  const rotateImage = React.useCallback(() => {
    if ('rotate' !== editingAction) setEditingAction('rotate');

    setRotate(prev => (prev + 90) % 360);
  }, [editingAction]);

  const startTaggingImage = React.useCallback(() => {
    if ('tag' !== editingAction) {
      setEditingAction('tag');
    }
  }, [editingAction]);

  React.useEffect(() => {
    setTagging('tag' === editingAction);
  }, [editingAction, setTagging]);

  const discardChanges = React.useCallback(() => {
    if (editingAction === 'tag') {
      setTaggedFriend(taggedFriendRef.current);
    }

    setImageSource(imageSource || item.source || srcImage);
    setEditingAction(undefined);
    setFlip({ x: 0, y: 0 });
    setRotate(0);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    item.source,
    srcImage,
    imageSource,
    taggedFriendRef.current,
    editingAction
  ]);

  const handleCroppedImage = async () => {
    const isCropAction = editingAction === 'crop';
    const dataCrop = isCropAction ? getCropStyle() : defaultCropStyle;
    const dataArea = parseCropToArea(dataCrop);
    const image_crop = (await getCroppedImg(
      crop || imageSource,
      dataArea,
      'base64',
      rotate,
      !!flip?.x,
      !!flip?.y,
      '%'
    )) as string;
    setFlip({ x: 0, y: 0 });
    setRotate(0);

    if (image_crop) {
      setCrop(image_crop);
    }

    return true;
  };

  const saveChanges = React.useCallback(async () => {
    if (editingAction === 'tag') {
      taggedFriendRef.current = taggedFriend;
      setEditingAction(null);
    } else {
      const processCrop = await handleCroppedImage();

      if (processCrop) {
        setEditingAction(null);
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [photoCaption, cropSelectionStyle, imageSource, rotate, flip]);

  const handleSubmit = React.useCallback(() => {
    setDialogValue({
      thumbnail: thumbnailVideo,
      text: photoCaption,
      base64: crop,
      tagged_friends: taggedFriend.map(x => ({
        ...x,
        user_id: x.content?.id || x.user?.id
      }))
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [photoCaption, crop, taggedFriend, thumbnailVideo]);

  const isMenuDisabled = React.useCallback(
    (check: string) => {
      return (
        'success' !== imageLoaded || (editingAction && check !== editingAction)
      );
    },
    [imageLoaded, editingAction]
  );

  const cssRotate = rotate ? `rotate(${rotate}deg)` : '';
  const cssScale =
    flip.x || flip.y ? `scale(${flip.x ? -1 : 1}, ${flip.y ? -1 : 1})` : '';

  const photoStyle: React.CSSProperties = {
    transform: `${cssRotate} ${cssScale}`
  };

  const handleChangeCaption = event => {
    const data = event.target.value;
    setPhotoCaption(data);
  };

  const canEditImage =
    (item?.file && isPhotoType(item?.file?.type)) ||
    (item?.module_name && item?.module_name !== 'video') ||
    item.type === 'photo';
  const canTag = item?.uid
    ? user?.extra?.can_tag_friend_on_photo
    : item?.extra?.can_tag_friend;

  const isVideo =
    isVideoType(item.file?.type) ||
    item?.type === 'video' ||
    item?.module_name === 'video';

  return (
    <Dialog {...dialogProps} disableBackdropClick scroll="body" maxWidth="md">
      <DialogTitle>{i18n.formatMessage({ id: dialogTitle })}</DialogTitle>
      <DialogContentWrapper>
        <LeftSide>
          {isVideo ? (
            <UploadThumbnail
              value={thumbnailVideo}
              setValue={setThumbnailVideo}
              config={{ preview_url: thumbnailVideo }}
            />
          ) : null}
          {!hideTextField ? (
            <Box mb={2}>
              <TextField
                variant="outlined"
                placeholder={i18n.formatMessage({ id: 'caption' })}
                fullWidth
                multiline
                rows={4}
                InputLabelProps={{
                  shrink: true
                }}
                value={photoCaption}
                onChange={handleChangeCaption}
              />
            </Box>
          ) : null}
          {'success' === imageLoaded ? (
            <LeftSideActions>
              {canTag ? (
                <StyledMenuItem
                  disabled={isMenuDisabled('tag')}
                  startIcon={<LineIcon icon="ico-price-tag" />}
                  children={isMobile ? '' : i18n.formatMessage({ id: 'tags' })}
                  onClick={startTaggingImage}
                />
              ) : null}
              <StyledMenuItem
                disabled={isMenuDisabled('crop')}
                startIcon={<LineIcon icon="ico-arrow-collapse" />}
                children={isMobile ? '' : i18n.formatMessage({ id: 'crop' })}
                onClick={startCroppingImage}
              />
              <StyledMenuItem
                disabled={isMenuDisabled('rotate')}
                startIcon={<LineIcon icon="ico-rotate-right-alt" />}
                children={isMobile ? '' : i18n.formatMessage({ id: 'rotate' })}
                onClick={rotateImage}
              />
              <StyledMenuItem
                disabled={isMenuDisabled('flip')}
                startIcon={<LineIcon icon="ico-flip-v" />}
                children={
                  isMobile ? '' : i18n.formatMessage({ id: 'flip_vertical' })
                }
                onClick={flipVertical}
              />
              <StyledMenuItem
                disabled={isMenuDisabled('flip')}
                startIcon={<LineIcon icon="ico-flip-h" />}
                children={
                  isMobile ? '' : i18n.formatMessage({ id: 'flip_horizontal' })
                }
                onClick={flipHorizontal}
              />
            </LeftSideActions>
          ) : null}
          <LeftSideBottom>
            {editingAction ? (
              <Button
                disabled={'success' !== imageLoaded}
                variant="contained"
                color="primary"
                onClick={saveChanges}
              >
                {i18n.formatMessage({ id: 'save_changes' })}
              </Button>
            ) : null}
            {editingAction ? (
              <Button
                disabled={'success' !== imageLoaded}
                variant="outlined"
                color="primary"
                onClick={discardChanges}
              >
                {i18n.formatMessage({ id: 'discard' })}
              </Button>
            ) : null}
            {!editingAction ? (
              <Button
                disabled={!imageLoaded}
                variant="contained"
                color="primary"
                onClick={handleSubmit}
              >
                {i18n.formatMessage({ id: 'save' })}
              </Button>
            ) : null}
            {!editingAction ? (
              <Button
                disabled={!imageLoaded}
                variant="outlined"
                color="primary"
                onClick={closeDialog}
              >
                {i18n.formatMessage({ id: 'cancel' })}
              </Button>
            ) : null}
          </LeftSideBottom>
        </LeftSide>
        <MainContent>
          <Backdrop>
            {isVideo ? (
              <VideoPlayer
                ratioPercent={'100%'}
                autoPlay
                src={item?.destination || imageSource}
              />
            ) : (
              <BlurContainer>
                <BlurImg
                  draggable={false}
                  src={item.source || srcImage}
                  alt={item.uid}
                />
              </BlurContainer>
            )}
          </Backdrop>
          {!isVideo ? (
            <>
              <Mask />
              <MediaContainer
                onMouseDown={onComponentMouseTouchDown}
                onTouchStart={onComponentMouseTouchDown}
                ref={imageContainerRef}
              >
                <MediaWrapper ref={imageWrapperRef} onClick={onClickImageBox}>
                  {'error' !== imageLoaded && canEditImage ? (
                    <>
                      <LargeImage
                        draggable={false}
                        src={crop || imageSource}
                        ref={imageRef}
                        onLoad={onImageLoadedSuccess}
                        style={photoStyle}
                        onError={onImageLoadedError}
                        alt={item.uid}
                      />
                      <TaggedBox
                        open={tagging && openTagBox}
                        px={offsetTag.px}
                        py={offsetTag.py}
                        classes={classesTag}
                        ref={anchorTagRef}
                      />
                      {tagging && openTagBox ? (
                        <Suggestion
                          onItemClick={chooseFriendToTag}
                          classes={classesTag}
                          anchorRef={anchorTagRef}
                          identity={
                            item?.id
                              ? `photo.entities.photo.${item.id}`
                              : undefined
                          }
                          open
                          excludeIds={taggedFriend.map(
                            x => x?.content?.id || x?.user?.id
                          )}
                          isFullFriend
                          postAsParent={postAsParent}
                          parentUser={parentUser}
                        />
                      ) : null}
                      {taggedFriend && taggedFriend?.length
                        ? taggedFriend.map(x =>
                            x?.resource_name === 'photo_tag' ? (
                              <PhotoTag
                                tagging={tagging}
                                identity={`${x.module_name}.entities.${x.resource_name}.${x.id}`}
                                onRemove={handleRemoveTag}
                                classes={classesTag}
                                isTypePreview
                                forceShow
                              />
                            ) : (
                              <PhotoTagPreview
                                {...x}
                                tagging={tagging}
                                onRemove={handleRemoveTagPreview}
                                forceShow
                              />
                            )
                          )
                        : null}
                    </>
                  ) : (
                    <ImageError>
                      {i18n.formatMessage({ id: 'could_not_load_photo' })}
                    </ImageError>
                  )}
                  {'crop' === editingAction && 'success' === imageLoaded ? (
                    <CropperImageSelection
                      onMounted={onFulfillSelection}
                      style={cropSelectionStyle}
                      ref={cropSelectionRef}
                      onMouseDown={onCropMouseTouchDown}
                      onTouchStart={onCropMouseTouchDown}
                    />
                  ) : null}
                </MediaWrapper>
              </MediaContainer>
            </>
          ) : null}
        </MainContent>
      </DialogContentWrapper>
    </Dialog>
  );
}
