/**
 * @type: ui
 * name: photo.itemView.modalCard
 */
import { useGlobal, useLoggedIn } from '@metafox/framework';
import { PhotoItemShape } from '@metafox/photo/types';
import { ClickOutsideListener, LineIcon, Image } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, Button, IconButton, styled, Tooltip } from '@mui/material';
import * as React from 'react';
import PhotoTag from '../../containers/PhotoTag';
import useStyles from './PhotoItemModalView.styles';
import Suggestion from '../Suggestion/Suggestion';
import TaggedBox from '../TaggedBox';
import { isNumber } from 'lodash';

type PhotoItemModalViewProps = {
  item: PhotoItemShape;
  user;
  isModal: boolean;
  identity: string;
  imageHeightAuto?: boolean;
  hideActionMenu?: boolean;
  enablePhotoTags?: boolean;
  taggedFriends?: any[];
  onAddPhotoTag: (data: unknown) => void;
  onRemovePhotoTag: (id: unknown) => void;
  onMinimizePhoto: (minimize: boolean) => void;
};

type State = {
  tagging?: boolean;
  px: number;
  py: number;
};

const name = 'PhotoItemModalView';

const ImageBox = styled('div', {
  name,
  slot: 'ImageBox',
  shouldForwardProp: props => props !== 'tagging'
})<{ tagging?: boolean; size?: { width?: string; height?: string } }>(
  ({ theme, tagging, size }) => ({
    position: 'relative',
    maxWidth: '100%',
    maxHeight: '100%',
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    margin: '0 auto',
    img: {
      maxWidth: '100%',
      maxHeight: size.height
    },
    ...(tagging && {
      cursor: 'pointer'
    })
  })
);

const ActionBar = styled('div', { name, slot: 'actionBar' })(({ theme }) => ({
  position: 'absolute',
  right: 0,
  top: 0,
  padding: theme.spacing(1),
  display: 'inline-flex',
  justifyContent: 'flex-end',
  zIndex: 1,
  alignItems: 'center'
}));

const BoxFake = styled('div', { name, slot: 'BoxFake' })(({ theme }) => ({
  position: 'absolute',
  top: 0,
  right: 0,
  left: 0,
  objectFit: 'cover',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));

const ImageFake = styled('img', { name, slot: 'ImageFake' })(({ theme }) => ({
  width: '100%',
  height: 'auto'
}));

const TagFriend = styled(IconButton, { name, slot: 'TagFriend' })(
  ({ theme }) => ({
    color: '#fff !important',
    width: 32,
    height: 32,
    fontSize: theme.mixins.pxToRem(15)
  })
);

const ImageStyled = styled(Image, {
  name,
  slot: 'Image',
  shouldForwardProp: props =>
    props !== 'visibleImage' && props !== 'imageHeight'
})<{ visibleImage?: boolean; imageHeight?: boolean }>(
  ({ theme, visibleImage, imageHeight }) => ({
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    opacity: 0,
    '& img': {
      maxWidth: '100%',
      maxHeight: '100%',
      width: 'auto',
      height: 'auto'
    },
    [theme.breakpoints.down('sm')]: {
      maxHeight: '350px',
      '& img': {
        maxHeight: '350px'
      }
    },
    ...(visibleImage && {
      opacity: 1
    }),
    ...(imageHeight && {
      maxHeight: 'initial'
    })
  })
);

const ImageWrapper = styled('div', { name, slot: 'ImageWrapper' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    height: '100%',
    width: '100%'
  })
);

export default function PhotoItemModalView({
  item,
  identity,
  imageHeightAuto = false,
  enablePhotoTags = true,
  taggedFriends = [],
  onAddPhotoTag,
  onRemovePhotoTag,
  onMinimizePhoto
}: PhotoItemModalViewProps) {
  const classes = useStyles();
  const { i18n, assetUrl } = useGlobal();
  const loggedIn = useLoggedIn();
  const anchorRef = React.useRef<any>();
  const [loadImage, setLoadImage] = React.useState<boolean>(false);
  const [offset, setOffset] = React.useState<State>({ px: 0, py: 0 });
  const [tagging, setTagging] = React.useState<boolean>(false);
  const [openTagBox, setOpenTagBox] = React.useState<boolean>(false);
  const [minimize, setMinimize] = React.useState<boolean>(true);

  const refWrapper = React.useRef();
  const [size, setSize] = React.useState({});

  React.useEffect(() => {
    if (loadImage && refWrapper?.current) {
      const rect = refWrapper.current.getBoundingClientRect();

      if (rect.height > 0) {
        setSize({ height: `${rect.height}px` });
      }
    }
  }, [loadImage]);

  if (!item) return null;

  const src = getImageSrc(item.image, 'origin', assetUrl('photo.no_image'));
  const srcSmall = getImageSrc(item.image, '240', assetUrl('photo.no_image'));

  const removeTagFriend = (e: React.MouseEvent<{}>, id: string) => {
    e.stopPropagation();
    onRemovePhotoTag(id);
  };

  const onLoad = e => {
    setLoadImage(true);
  };

  const toggleTagging = () => {
    setTagging(prev => !prev);

    if (!tagging) {
      setOpenTagBox(false);
    }
  };

  const onClickImageBox = (e: React.MouseEvent<HTMLDivElement>) => {
    const rect = e.currentTarget.getBoundingClientRect();
    const px: number =
      (Math.max(Math.min(e.clientX - rect.left, rect.width - 50), 50) /
        rect.width) *
      100;
    const py: number =
      (Math.max(Math.min(e.clientY - rect.top, rect.height - 50), 50) /
        rect.height) *
      100;
    setOffset({ px, py });

    if (tagging) {
      setOpenTagBox(true);
    }
  };

  const chooseFriendToTag = (content: unknown) => {
    const { px, py } = offset;
    const newTaggedFriend = { content, px, py };
    onAddPhotoTag(newTaggedFriend);
    setOpenTagBox(false);
  };

  const handleClickAway = () => {
    setOpenTagBox(false);
    setTagging(false);
  };

  const handleFullSize = () => {
    const minimizeItem = minimize;
    setMinimize(!minimizeItem);
    onMinimizePhoto && onMinimizePhoto(minimizeItem);
  };

  const isMaturePhoto = isNumber(item?.mature) && item?.mature > 0;

  return (
    <ClickOutsideListener onClickAway={handleClickAway}>
      <ImageWrapper ref={refWrapper}>
        <ImageBox tagging={tagging} onClick={onClickImageBox} size={size}>
          <ImageStyled
            onLoad={onLoad}
            visibleImage
            imageHeight={imageHeightAuto}
            alt={item.title}
            src={src}
            key={identity}
            imageFit="contain"
            aspectRatio={'auto'}
            identity={identity}
            matureProps={{
              color: 'white',
              sx: { width: '100%', height: '100%' }
            }}
          />
          {!isMaturePhoto ? (
            <>
              {!loadImage ? (
                <BoxFake>
                  <ImageFake alt={item.title} src={srcSmall} />
                </BoxFake>
              ) : null}
              <TaggedBox
                open={tagging && openTagBox}
                px={offset.px}
                py={offset.py}
                classes={classes}
                ref={anchorRef}
              />
              {tagging && openTagBox ? (
                <Suggestion
                  onItemClick={chooseFriendToTag}
                  classes={classes}
                  anchorRef={anchorRef}
                  identity={identity}
                  open
                />
              ) : null}
              {item.tagged_friends?.length
                ? item.tagged_friends.map(id => (
                    <PhotoTag
                      extra={item.extra}
                      tagging={tagging}
                      identity={id}
                      key={id.toString()}
                      onRemove={removeTagFriend}
                      classes={classes}
                    />
                  ))
                : null}
            </>
          ) : null}
        </ImageBox>
        <ActionBar>
          {/* <Box>
           {isModal && !isMobile && (
              <Tooltip title={i18n.formatMessage({ id: 'close' })}>
                <TagFriend onClick={handleClose}>
                  <LineIcon icon="ico-close" color="white" />
                </TagFriend>
              </Tooltip>
            )}
          </Box>  */}
          {loggedIn && enablePhotoTags && !tagging && !isMaturePhoto ? (
            <Box>
              {loadImage && item.extra?.can_tag_friend && onAddPhotoTag ? (
                <Tooltip title={i18n.formatMessage({ id: 'start_tagging' })}>
                  <TagFriend onClick={toggleTagging}>
                    <LineIcon icon="ico-price-tag" color="white" />
                  </TagFriend>
                </Tooltip>
              ) : null}
              {onMinimizePhoto && (
                <Tooltip
                  title={i18n.formatMessage({
                    id: minimize ? 'switch_to_full_screen' : 'exit_full_screen'
                  })}
                >
                  <TagFriend onClick={handleFullSize}>
                    <LineIcon
                      icon={
                        minimize ? 'ico-arrow-expand' : 'ico-arrow-collapse'
                      }
                      color="white"
                    />
                  </TagFriend>
                </Tooltip>
              )}
            </Box>
          ) : null}
          {enablePhotoTags && tagging && !isMaturePhoto ? (
            <Tooltip title={i18n.formatMessage({ id: 'done_tagging' })}>
              <Button
                variant="contained"
                color="primary"
                size="small"
                onClick={toggleTagging}
              >
                {i18n.formatMessage({ id: 'done' })}
              </Button>
            </Tooltip>
          ) : null}
        </ActionBar>
      </ImageWrapper>
    </ClickOutsideListener>
  );
}
