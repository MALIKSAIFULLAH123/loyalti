import { useGlobal } from '@metafox/framework';
import { AlbumItemProps } from '@metafox/photo/types';
import { Image, ItemView, TruncateText } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import React from 'react';
import useStyles from './styles';

const AlbumChooseCoverItem = ({
  item,
  user,
  identity,
  itemProps,
  wrapAs,
  wrapProps
}: AlbumItemProps) => {
  const { i18n, assetUrl } = useGlobal();
  const classes = useStyles();

  if (!item) return null;

  const { id, name, statistic } = item;

  const handleChoose = () => {
    itemProps.setId(id);
    itemProps.setStep(1);
    itemProps.setName(name);
  };

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
      <div className={classes.root} onClick={handleChoose}>
        <div className={classes.outer}>
          <div className={classes.mediaContent}>
            <Image
              className={classes.media}
              src={cover}
              aspectRatio={'32'}
              alt={item.title}
            />
          </div>
          <div className={classes.inner}>
            <TruncateText variant="h4" lines={2} fontWeight={600} fixHeight>
              {name}
            </TruncateText>
            {user?.full_name && (
              <div className={classes.owner}>
                {0 < statistic.total_photo && (
                  <div className={classes.totalPhoto}>
                    {i18n.formatMessage(
                      { id: 'total_photo' },
                      { value: statistic.total_photo }
                    )}
                  </div>
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    </ItemView>
  );
};

export default AlbumChooseCoverItem;
