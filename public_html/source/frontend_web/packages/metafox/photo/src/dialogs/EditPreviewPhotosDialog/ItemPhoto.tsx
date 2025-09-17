import { useGlobal } from '@metafox/framework';
import { StyledIconButton, LineIcon } from '@metafox/ui';
import { getImageSrc, isVideoType, isIOS } from '@metafox/utils';
import { TextField, Box, styled } from '@mui/material';
import clsx from 'clsx';
import { XYCoord } from 'dnd-core';
import React from 'react';
import { DropTargetMonitor, useDrag, useDrop } from 'react-dnd';
import { ItemTypes } from './ItemTypes';
import { handleConvertBase64 } from '@metafox/photo/utils/helper';

const name = 'PreviewPhotosDialogItemPhoto';
const CustomPlayButton = styled(LineIcon, {
  name,
  slot: 'CustomPlayButton'
})(({ theme }) => ({
  position: 'absolute',
  top: 0,
  bottom: 0,
  left: 0,
  right: 0,
  margin: 'auto',
  fontSize: '64px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  background: 'rgba(0, 0, 0, 0.3)',
  color: '#fff',
  borderTopLeftRadius: theme.spacing(1),
  borderTopRightRadius: theme.spacing(1)
}));

export default function ItemPhoto({
  id,
  index,
  item,
  movePhoto,
  editItem,
  editPhoto,
  removeItem,
  tagItem,
  editTextPhoto,
  classes,
  hideRemove,
  isEdit
}) {
  const { i18n, useSession, ParserPreviewPhoto } = useGlobal();
  const itemRef = React.useRef<HTMLDivElement>(null);
  const { user } = useSession();
  const [photoCaption, setPhotoCaption] = React.useState(item?.text ?? '');
  const [isParsingFile, setIsParsingFile] = React.useState(false);

  const defaultThumbnailVideo = React.useMemo(() => {
    const thumbnail = item?.thumbnail;

    if (thumbnail?.image) return getImageSrc(thumbnail?.image, '500');

    return undefined;
  }, [item?.thumbnail]);

  const [previewUrl, setPreviewUrl] = React.useState(defaultThumbnailVideo);

  React.useEffect(() => {
    const thumbnail = item?.thumbnail;

    if (!thumbnail?.file) {
      setPreviewUrl(
        thumbnail?.image ? getImageSrc(thumbnail?.image, '500') : undefined
      );

      return;
    }

    handleConvertBase64(thumbnail?.file, result => {
      setPreviewUrl(result);
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [item?.thumbnail?.file, item?.thumbnail?.image]);

  const [, drop] = useDrop({
    accept: ItemTypes.PHOTO,
    hover(item, monitor: DropTargetMonitor) {
      if (!itemRef.current) {
        return;
      }

      const dragIndex = item.index;
      const hoverIndex = index;

      if (dragIndex === hoverIndex) {
        return;
      }

      const hoverBoundingRect = itemRef.current?.getBoundingClientRect();
      const hoverMiddleY =
        (hoverBoundingRect.bottom - hoverBoundingRect.top) / 2;
      const clientOffset = monitor.getClientOffset();
      const hoverClientY = (clientOffset as XYCoord).y - hoverBoundingRect.top;

      // Dragging downwards
      if (dragIndex < hoverIndex && hoverClientY < hoverMiddleY) {
        return;
      }

      // Dragging upwards
      if (dragIndex > hoverIndex && hoverClientY > hoverMiddleY) {
        return;
      }

      movePhoto(dragIndex, hoverIndex);
      item.index = hoverIndex;
    }
  });

  const handleCaptionChange = React.useCallback(event => {
    const data = event.target.value;
    setPhotoCaption(data);
  }, []);

  const handleCaptionBlur = React.useCallback(
    event => {
      const data = event.target.value;
      editTextPhoto(item, data);
    },
    [editTextPhoto, item]
  );

  const [isDragging, drag] = useDrag({
    type: ItemTypes.PHOTO,
    item: { type: ItemTypes.PHOTO, id, index },
    collect: (monitor: any) => ({
      isDragging: monitor.isDragging()
    })
  });

  const opacity = isDragging.isDragging ? 0.5 : 0.9999;
  const customStyle = isDragging.isDragging ? 'isDragging' : '';
  drag(drop(itemRef));

  const styleDnd: React.CSSProperties = {
    cursor: isEdit ? 'default' : 'move'
  };

  const onStartParseFile = React.useCallback(() => {
    setIsParsingFile(true);
  }, []);

  const onParseFile = React.useCallback(
    parseFile => {
      editPhoto(item, parseFile);
      setIsParsingFile(false);
    },
    [item, editPhoto]
  );

  const callbackEditDetail = React.useCallback(data => {
    setPhotoCaption(data?.text);
  }, []);

  if (!item) return;

  const canTag = item?.uid
    ? user?.extra?.can_tag_friend_on_photo
    : item?.extra?.can_tag_friend;

  const isVideo =
    isVideoType(item.file?.type) ||
    item?.type === 'video' ||
    item?.resource_name === 'video';
  const isVideoTag = !isIOS && isVideo && !previewUrl;

  return (
    <div
      data-uid={item.uid}
      style={{ ...styleDnd, opacity }}
      ref={isEdit ? null : itemRef}
      className={clsx(classes.itemRoot, customStyle ? classes.isDragging : '')}
    >
      <div className={classes.itemMediaContainer}>
        <div className={classes.itemMediaBackdrop}>
          {isVideo ? (
            isVideoTag ? (
              <>
                <div className={classes.itemBlur}>
                  <video
                    src={item.source || item.destination}
                    draggable={false}
                    controls={false}
                    autoPlay={false}
                    muted
                    className={classes.itemBlurImg}
                  />
                </div>
                <video
                  src={item.source || item.destination}
                  draggable={false}
                  controls={false}
                  autoPlay={false}
                  muted
                  className={classes.itemMedia}
                />
                <CustomPlayButton icon="ico-play-circle-o" />
              </>
            ) : (
              <>
                <div className={classes.itemBlur}>
                  <img
                    draggable={false}
                    src={previewUrl || item.source || item.destination}
                    className={classes.itemBlurImg}
                    alt={item.uid}
                  />
                </div>
                <Box
                  sx={{
                    position: 'absolute',
                    top: 0,
                    left: 0,
                    right: 0,
                    bottom: 0,
                    borderTopLeftRadius: theme => theme.shape.borderRadius,
                    borderTopRightRadius: theme => theme.shape.borderRadius,
                    overflow: 'hidden'
                  }}
                >
                  <img
                    draggable={false}
                    src={previewUrl || item.source || item.destination}
                    className={classes.itemMedia}
                    alt={item.uid}
                  />
                  <CustomPlayButton icon="ico-play-circle-o" />
                </Box>
              </>
            )
          ) : (
            <>
              <div className={classes.itemBlur}>
                <img
                  draggable={false}
                  src={item.base64 || item.source || getImageSrc(item?.image)}
                  className={classes.itemBlurImg}
                  alt={item.uid}
                />
              </div>
              <ParserPreviewPhoto
                item={item}
                onParse={onParseFile}
                onError={() => removeItem(item)}
                onStartParseFile={onStartParseFile}
                sx={{
                  position: 'absolute',
                  top: 0,
                  left: 0,
                  right: 0,
                  bottom: 0,
                  borderTopLeftRadius: theme => theme.shape.borderRadius,
                  borderTopRightRadius: theme => theme.shape.borderRadius,
                  overflow: 'hidden'
                }}
              >
                <img
                  draggable={false}
                  src={item.base64 || item.source || getImageSrc(item?.image)}
                  className={classes.itemMedia}
                  alt={item.uid}
                />
              </ParserPreviewPhoto>
            </>
          )}
          {!isParsingFile ? (
            <>
              <Box className={clsx(classes.editButton, classes.autoHideButton)}>
                <StyledIconButton
                  size="small"
                  color="primary"
                  icon="ico-pencil"
                  onClick={() => editItem(item, callbackEditDetail)}
                  title={i18n.formatMessage({ id: 'edit' })}
                />
              </Box>
              {canTag && !isVideo ? (
                <Box className={clsx(classes.tagButton)}>
                  <StyledIconButton
                    onClick={() => tagItem(item)}
                    size="small"
                    color="primary"
                    icon="ico-price-tag"
                    title={i18n.formatMessage({ id: 'tag_friends' })}
                  />
                </Box>
              ) : null}
            </>
          ) : null}
          {hideRemove ? null : (
            <Box className={clsx(classes.closeButton, classes.autoHideButton)}>
              <StyledIconButton
                size="small"
                onClick={() => removeItem(item)}
                color="primary"
                icon="ico-close"
                title={i18n.formatMessage({ id: 'remove' })}
              />
            </Box>
          )}
        </div>
      </div>
      <div className={classes.itemComposerContainer}>
        <TextField
          value={photoCaption}
          variant="outlined"
          placeholder={i18n.formatMessage({ id: 'caption' })}
          fullWidth
          disabled={isParsingFile}
          InputLabelProps={{
            shrink: true
          }}
          className={classes.itemComposer}
          onChange={handleCaptionChange}
          onBlur={handleCaptionBlur}
        />
      </div>
    </div>
  );
}
