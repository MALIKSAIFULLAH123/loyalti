import { Link, useGlobal } from '@metafox/framework';
import { VideoItemProps } from '@metafox/video/types';
import {
  FeaturedFlag,
  Image,
  ItemView,
  LineIcon,
  SponsorFlag,
  Statistic,
  TruncateText,
  UserName
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import * as React from 'react';
import useStyles from './styles';

const VideoItemSmallCard = ({
  item,
  user,
  identity,
  itemProps,
  handleAction,
  state,
  wrapAs,
  wrapProps
}: VideoItemProps) => {
  const { ItemActionMenu, assetUrl } = useGlobal();
  const classes = useStyles();

  if (!item) return null;

  const to = `/video/play/${item.id}/${item.title}`;
  const cover = getImageSrc(item.image, '500', assetUrl('video.no_image'));

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <div className={classes.root}>
        <div className={classes.outer}>
          <div className={classes.itemFlag}>
            <FeaturedFlag variant="itemView" value={item.is_featured} />
            <SponsorFlag
              variant="itemView"
              value={item.is_sponsor}
              item={item}
            />
          </div>
          {cover ? (
            <Link to={to} className={classes.media}>
              <LineIcon className={classes.iconPlay} icon="ico-play-circle-o" />
              <Image
                link={to}
                src={cover}
                aspectRatio={'169'}
                backgroundImage
              />
            </Link>
          ) : null}
          <div className={classes.inner}>
            <Link to={to} className={classes.title}>
              <TruncateText variant={'body1'} lines={2} fixHeight>
                {item.title}
              </TruncateText>
            </Link>
            <div className={classes.itemMinor}>
              <UserName
                to={`/user/${user?.id}`}
                user={user}
                hoverCard={false}
              />
            </div>
            <Statistic
              className={classes.itemMinor}
              values={item.statistic}
              display={'total_view'}
            />
            {!itemProps.showActionMenu ? (
              <ItemActionMenu
                identity={identity}
                icon={'ico-dottedmore-vertical-o'}
                state={state}
                handleAction={handleAction}
                className={classes.actionMenu}
              />
            ) : null}
          </div>
        </div>
      </div>
    </ItemView>
  );
};

export default VideoItemSmallCard;
