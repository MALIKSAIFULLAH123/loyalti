import { useGlobal } from '@metafox/framework';
import { AlbumItemProps } from '@metafox/music/types';
import { Image, ItemView, LineIcon } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import React from 'react';
import useStyles from './styles';

const PhotoItemSmallCard = ({
  item,
  identity,
  wrapAs,
  wrapProps
}: AlbumItemProps) => {
  const classes = useStyles();
  const { assetUrl } = useGlobal();

  if (!item) return null;

  const { statistic } = item;
  const to = `/photo/album/${item.id}`;

  const cover = getImageSrc(
    item.image,
    '500',
    assetUrl('photo.album_no_image')
  );

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <div className={classes.smallRoot}>
        <div className={classes.outer}>
          <div className={classes.mediaContent}>
            <Image
              className={classes.smallMedia}
              link={to}
              src={cover}
              aspectRatio={'43'}
            />
            {0 < statistic.total_photo && (
              <div className={classes.smallTotalPhoto}>
                <LineIcon icon={'ico-photos-alt-o'} />
                {statistic.total_photo}
              </div>
            )}
          </div>
        </div>
      </div>
    </ItemView>
  );
};

export default PhotoItemSmallCard;
