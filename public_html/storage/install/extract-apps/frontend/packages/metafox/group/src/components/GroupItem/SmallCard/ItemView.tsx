import { Link, useGlobal } from '@metafox/framework';
import { GroupItemProps } from '@metafox/group/types';
import { Image, ItemView, Statistic, TruncateText } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import * as React from 'react';
import useStyles from './styles';

export default function GroupSmallCardItem({
  item,
  identity,
  wrapAs,
  wrapProps
}: GroupItemProps) {
  const { assetUrl } = useGlobal();
  const classes = useStyles();
  const { title, summary, statistic, id, link = '' } = item;
  const to = link || `/group/${id}`;
  const cover = getImageSrc(item.cover, '500', assetUrl('group.no_image'));
  const avatar = getImageSrc(item.avatar, '50x50');

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <div className={classes.root}>
        <div className={classes.outer}>
          <div
            className={classes.pageCover}
            style={{ backgroundImage: `url("${cover}")` }}
          >
            <div className={classes.pageShadow}>
              <Image
                className={classes.media}
                link={to}
                src={avatar}
                aspectRatio={'11'}
              />
              <Statistic
                values={statistic}
                display={'total_like'}
                className={classes.pageLike}
              />
            </div>
          </div>
          <div className={classes.inner}>
            <TruncateText fontSize={14} lines={2} fixHeight>
              <Link
                to={to}
                className={classes.title}
                color={'inherit'}
                children={title}
                hoverCard
              />
            </TruncateText>
            <div className={classes.categoryName}>{summary}</div>
          </div>
        </div>
      </div>
    </ItemView>
  );
}
