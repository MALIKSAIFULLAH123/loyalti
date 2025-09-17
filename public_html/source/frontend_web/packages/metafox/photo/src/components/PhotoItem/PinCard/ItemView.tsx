import { Link, useGlobal } from '@metafox/framework';
import { PhotoItemProps } from '@metafox/photo/types';
import {
  FeaturedFlag,
  LineIcon,
  SponsorFlag,
  PendingFlag,
  Image
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import React from 'react';
import useStyles from './styles';
import { styled } from '@mui/material/styles';

const PinItem = styled('div', {
  name: 'PinCard',
  slot: 'pinItem',
  shouldForwardProp: prop => prop !== 'size' && prop !== 'isProfilePage',
  overridesResolver(props, styles) {
    return [styles.pinItem];
  }
})(({ theme }) => ({
  position: 'relative',
  flexBasis: 0,
  display: 'flex',
  flexDirection: 'column',
  flexGrow: 1,
  overflow: 'hidden',
  borderRadius: theme.shape.borderRadius
}));

export interface PhotoProps {
  identity: string;
  itemHeight?: number;
  width: number;
  height: number;
  margin: number;
  classes?: any;
  order: number;
  onDeleteImage?: (id: string | number) => void;
}

const PhotoItem_PinCard = ({
  identity,
  item,
  user,
  itemHeight,
  margin,
  onDeleteImage,
  handleAction,
  state,
  wrapAs: WrapAs,
  wrapProps,
  order
}: PhotoItemProps & PhotoProps) => {
  const { ItemActionMenu, assetUrl, getAcl } = useGlobal();
  const canViewReactionItem = getAcl('like.like.view');
  const imageHeight = itemHeight ? '100%' : 'auto';
  const classes = useStyles();
  const no_image =
    item?.resource_name === 'video'
      ? assetUrl('video.no_image')
      : assetUrl('photo.no_image');
  const src = getImageSrc(item?.image, '240', no_image);

  if (!item || item?.error) return null;

  const { id, is_featured, is_sponsor, title, statistic, resource_name, slug } = item;
  let to = `/photo/${id}`;

  if (item?.album_id) {
    to =
      resource_name === 'photo'
        ? `/media/album/${item?.album_id}/photo/${id}${slug ? `/${slug}` : ''}`
        : `/media/album/${item?.album_id}/video/${id}${slug ? `/${slug}` : ''}`;
  }

  return (
    <WrapAs {...wrapProps} className={classes.pinGird}>
      <PinItem style={{ height: itemHeight, margin }}>
        <div className={classes.features}>
          <FeaturedFlag variant="itemView" value={item.is_featured} />
          <SponsorFlag value={item.is_sponsor} variant="itemView" item={item} />
          <PendingFlag variant="itemView" value={item.is_pending} />
        </div>
        <Link to={to} asModal className={classes.mediaLink}>
          <Image
            src={src}
            identity={identity}
            alt={title}
            className={classes.image}
            style={{ height: imageHeight }}
          />
          {resource_name === 'photo' ? (
            <div className={classes.photoInfo}>
              <div>
                <div className={classes.photoTitle}>{user.full_name}</div>
                {canViewReactionItem && statistic.total_like > 0 ? (
                  <div className={classes.photoLike}>
                    <span className="ico ico-thumbup-o"></span>
                    <span className={classes.total_like}>
                      {statistic.total_like}
                    </span>
                  </div>
                ) : null}
              </div>
            </div>
          ) : null}
          {resource_name === 'video' ? (
            <div className={classes.playVideoIcon}>
              <LineIcon icon="ico-play-circle-o" />
            </div>
          ) : null}

          {!item?.album_id ? (
            <div className={classes.features}>
              {is_featured ? (
                <FeaturedFlag variant="itemView" value={is_featured} />
              ) : null}
              <SponsorFlag value={is_sponsor} variant="itemView" item={item} />
            </div>
          ) : null}
        </Link>
        <div className={classes.photoActions}>
          <ItemActionMenu
            identity={identity}
            state={state}
            handleAction={handleAction}
            className={classes.photoActionsDropdown}
          >
            <LineIcon
              icon={'ico-dottedmore-vertical-o'}
              className={classes.iconButton}
            />
          </ItemActionMenu>
        </div>
      </PinItem>
    </WrapAs>
  );
};
export default PhotoItem_PinCard;
