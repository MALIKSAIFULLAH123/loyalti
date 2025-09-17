/**
 * @type: ui
 * name: StatusComposerControlAttachedPhotos
 */

import {
  BasicFileItem,
  StatusComposerControlProps,
  useGlobal
} from '@metafox/framework';
import { Image, LineIcon, ItemShape } from '@metafox/ui';
import { getImageSrc, isVideoType, isIOS } from '@metafox/utils';
import { Button, IconButton, Box, styled } from '@mui/material';
import { get } from 'lodash';
import React from 'react';
import useAddPhotoToStatusComposerHandler from '@metafox/photo/hooks/useAddPhotoToStatusComposerHandler';
import produce from 'immer';
import useFeedMediaConfig from '@metafox/photo/hooks/useFeedMediaConfig';
import VideoItemView from './VideoItemView';

const name = 'PreviewPhotos';
const RemoveBtnStyled = styled(IconButton, {
  name,
  slot: 'remove button'
})(({ theme }) => ({
  position: 'absolute',
  top: theme.spacing(2),
  right: theme.spacing(2),
  zIndex: 5,
  opacity: 0.9
}));

const RemainBackdropStyled = styled('div', {
  name,
  slot: 'RemainBackdrop',
  shouldForwardProp: props => props !== 'allowEditAll'
})<{ allowEditAll?: boolean }>(({ theme, allowEditAll }) => ({
  position: 'absolute',
  left: 0,
  right: 0,
  top: 0,
  bottom: 0,
  backgroundColor: 'rgba(0,0,0,0.3)',
  borderRadius: theme.shape.borderRadius,
  '&:hover': {
    backgroundColor: 'rgba(0,0,0,0.1)'
  },
  ...(allowEditAll && { cursor: 'pointer' })
}));

const Root = styled('div', {
  name,
  slot: 'Root'
})(({ theme }) => ({
  display: 'block',
  position: 'relative',
  overflow: 'hidden',
  margin: theme.spacing(1.5)
}));

const ListContainer = styled('div', {
  name,
  slot: 'ListContainer',
  shouldForwardProp: prop => prop !== 'gridType'
})<{
  gridType?: string;
}>(({ theme, gridType }) => ({
  position: 'relative',
  display: 'flex',
  flexWrap: 'wrap'
}));

const ItemRoot = styled('div', {
  name,
  slot: 'ItemRoot',
  shouldForwardProp: prop => prop !== 'itemIndex' && prop !== 'gridType'
})<{
  itemIndex?: string;
  gridType?: string;
}>(({ theme, itemIndex, gridType }) => ({
  position: 'relative',
  display: 'flex',
  flexBasis: '50%',
  padding: theme.spacing(0.5),
  ...(gridType === 'preset1' &&
    itemIndex === 'item0' && {
      flexBasis: '100%'
    }),
  ...(gridType === 'preset3' &&
    itemIndex === 'item0' && {
      flexBasis: '100%'
    })
}));

const RemainText = styled('div', {
  name,
  slot: 'RemainText'
})(({ theme }) => ({
  color: 'white',
  position: 'absolute',
  left: '50%',
  top: '50%',
  fontSize: '2rem',
  transform: 'translate(-50%,-50%)'
}));

const ActionBar = styled('div', {
  name,
  slot: 'ActionBar'
})(({ theme }) => ({
  position: 'absolute',
  top: 0,
  right: 0,
  left: 0,
  display: 'flex',
  zIndex: 5,
  flexDirection: 'row',
  padding: theme.spacing(2),
  justifyContent: 'space-between'
}));

const ButtonGroup = styled('div', {
  name,
  slot: 'ButtonGroup'
})(({ theme }) => ({
  '& > *': {
    marginRight: `${theme.spacing(1)} !important`
  },
  [theme.breakpoints.down('sm')]: {
    display: 'flex',
    flexDirection: 'column',
    margin: theme.spacing(-1.5, -1, -1),
    '& button': {
      marginTop: theme.spacing(0.5)
    }
  }
}));

const VideoWrapper = styled('div', {
  name,
  slot: 'VideoWrapper'
})(({ theme }) => ({
  width: '100%',
  display: 'block',
  position: 'relative',
  borderRadius: theme.shape.borderRadius,
  overflow: 'hidden',
  '&:before': {
    paddingBottom: '56.25%',
    content: '""',
    display: 'block'
  }
}));

const VideoInner = styled(Box, {
  name,
  slot: 'VideoInner'
})(({ theme }) => ({
  top: 0,
  left: 0,
  right: 0,
  bottom: 0,
  position: 'absolute'
}));

const MediaStyled = styled(Box)(({ theme }) => ({
  backgroundColor: 'rgba(0,0,0,0.4)',
  aspectRatio: '16 / 9',
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  fontSize: theme.mixins.pxToRem(16),
  padding: theme.spacing(1),
  borderRadius: theme.shape.borderRadius
}));

const parseMessageAddMedia = ({ acl }) => {
  const canPhoto = get(acl, 'photo.photo.create');

  const canVideo = get(acl, 'video.video.create');

  if (canPhoto && canVideo) {
    return 'add_photos_video';
  }

  if (canVideo) {
    return 'add_videos';
  }

  return 'add_photos';
};

export default function PreviewPhotos({
  composerRef,
  isEdit,
  parentUser,
  submitting
}: StatusComposerControlProps) {
  const { i18n, dialogBackend, getAcl, ParserPreviewPhoto } = useGlobal();
  const inputRef = React.useRef<HTMLInputElement>();
  const { acceptTypes } = useFeedMediaConfig({
    parentUser
  });
  const [handleChange, addPhotos] = useAddPhotoToStatusComposerHandler(
    composerRef,
    inputRef,
    { parentUser, acceptTypes }
  );

  const items: BasicFileItem[] | ItemShape[] = get(
    composerRef.current.state,
    'attachments.photo.value'
  );

  const extraFeed: any = get(composerRef.current.state, 'extra');
  const postAsParent: boolean = get(
    composerRef.current.state,
    'post_as_parent'
  );

  const acl = getAcl();

  const editPhoto = React.useCallback(
    (item: BasicFileItem, data) => {
      const items: BasicFileItem[] = get(
        composerRef.current.state,
        'attachments.photo.value'
      );
      const newValue = produce(items, draft => {
        const itemEdit = draft.find(x => {
          return (
            (item?.uid && x?.uid === item.uid) ||
            (item?.id && x?.id === item?.id)
          );
        });

        if (!itemEdit) return;

        Object.assign(itemEdit, data);
      });

      composerRef.current.setAttachments('photo', 'photo', {
        as: 'StatusComposerControlAttachedPhotos',
        value: newValue
      });
    },
    [composerRef]
  );

  const removeAllItem = React.useCallback(() => {
    composerRef.current.removeAttachmentName('photo');
  }, [composerRef]);

  const removeItem = React.useCallback(
    (item: BasicFileItem) => {
      const items: BasicFileItem[] = get(
        composerRef.current.state,
        'attachments.photo.value'
      );
      const newValue = produce(items, draft =>
        draft.filter(x => x.uid !== item.uid || x.id !== item.id)
      );

      composerRef.current.setAttachments('photo', 'photo', {
        as: 'StatusComposerControlAttachedPhotos',
        value: newValue
      });
    },
    [composerRef]
  );

  const editAll = React.useCallback(() => {
    dialogBackend.present({
      component: 'photo.dialog.EditPreviewPhotosDialog',
      props: {
        composerRef,
        isEdit,
        parentUser,
        postAsParent
      }
    });
  }, [composerRef, dialogBackend, isEdit, postAsParent, parentUser]);

  const editItem = React.useCallback(
    item => {
      dialogBackend
        .present({
          component: 'photo.dialog.EditPreviewPhotoDialog',
          props: {
            item,
            postAsParent,
            parentUser
          }
        })
        .then(value => {
          if (!value) return;

          editPhoto(item, value);
        });
    },
    [dialogBackend, editPhoto, postAsParent, parentUser]
  );

  const messageAddMedia = parseMessageAddMedia({ acl });

  const total = items?.length || 0;

  if (!total) return null;

  const gridType = Math.min(total, 4) % 5;
  const remain = total - gridType;

  const allowEditAll = Boolean(
    1 < total || !isEdit || extraFeed?.can_edit_feed_item
  );

  const handleClickRemain = () => {
    if (!allowEditAll) return;

    editAll();
  };

  return (
    <Root data-testid="previewAttachPhoto">
      <ListContainer>
        {total
          ? items.slice(0, gridType).map((item, index) => (
              <ItemRoot
                gridType={`preset${gridType}`}
                itemIndex={`item${index}`}
                key={item?.uid || item?.id}
              >
                <Box sx={{ position: 'relative', width: '100%' }}>
                  {item?.is_processing ? (
                    <MediaStyled>
                      <LineIcon
                        icon="ico-loading-icon"
                        sx={{ fontSize: '24px' }}
                      />
                      <Box mt={1}>
                        {i18n.formatMessage({
                          id: 'video_is_being_processed'
                        })}
                      </Box>
                    </MediaStyled>
                  ) : isVideoType(item.file?.type) ||
                    item?.resource_name === 'video' ||
                    item?.type === 'video' ? (
                    <VideoWrapper>
                      <VideoInner>
                        <VideoItemView
                          item={item}
                          showImage={isIOS}
                        ></VideoItemView>
                      </VideoInner>
                    </VideoWrapper>
                  ) : (
                    <ParserPreviewPhoto
                      item={item}
                      onParse={parseFile => editPhoto(item, parseFile)}
                      onError={() => removeItem(item)}
                      isAbort={submitting}
                      sx={{
                        borderRadius: theme => `${theme.shape.borderRadius}px`,
                        overflow: 'hidden'
                      }}
                    >
                      <Image
                        draggable={false}
                        src={
                          item.base64 ||
                          item.source ||
                          item?.destination ||
                          getImageSrc(item?.image)
                        }
                        aspectRatio={'169'}
                        shape={'radius'}
                      />
                    </ParserPreviewPhoto>
                  )}
                  {0 < remain && gridType === index + 1 ? (
                    <RemainBackdropStyled
                      onClick={handleClickRemain}
                      allowEditAll={allowEditAll}
                    >
                      <RemainText>{`+ ${remain}`}</RemainText>
                    </RemainBackdropStyled>
                  ) : null}
                </Box>
              </ItemRoot>
            ))
          : null}
      </ListContainer>
      <ActionBar>
        {!isEdit || extraFeed?.can_edit_feed_item ? (
          <ButtonGroup>
            {1 === total ? (
              <Button
                variant="contained"
                size="smaller"
                color="default"
                onClick={() => editItem(items[0])}
                startIcon={<LineIcon icon="ico-pencil" />}
              >
                {i18n.formatMessage({ id: 'edit' })}
              </Button>
            ) : (
              <Button
                variant="contained"
                size="smaller"
                color="default"
                onClick={editAll}
                startIcon={<LineIcon icon="ico-pencil" />}
              >
                {i18n.formatMessage({ id: 'edit_all' })}
              </Button>
            )}
            <Button
              variant="contained"
              size="smaller"
              color="default"
              onClick={addPhotos}
              startIcon={<LineIcon icon="ico-plus" />}
            >
              {i18n.formatMessage({ id: messageAddMedia })}
            </Button>
          </ButtonGroup>
        ) : null}
        <input
          type="file"
          className="srOnly"
          ref={inputRef}
          onChange={handleChange}
          multiple
          accept={acceptTypes}
        />
      </ActionBar>
      {isEdit ? null : (
        <RemoveBtnStyled
          size="smallest"
          onClick={removeAllItem}
          variant="blacked"
          title={i18n.formatMessage({ id: 'remove_all' })}
        >
          <LineIcon icon="ico-close" />
        </RemoveBtnStyled>
      )}
    </Root>
  );
}
