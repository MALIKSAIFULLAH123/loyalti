/**
 * @type: itemView
 * name: blog.itemView.smallCard
 * chunkName: blog
 */

import { BlogItemProps as ItemProps } from '@metafox/blog';
import actionCreators from '@metafox/blog/actions/blogItemActions';
import { connectItemView, Link } from '@metafox/framework';
import {
  DotSeparator,
  FormatDate,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  UserName
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import React from 'react';

export function BlogItemSmallCard({
  item,
  user,
  wrapAs,
  wrapProps
}: ItemProps) {
  if (!item) return null;

  const { creation_date, link: to } = item;

  return (
    <ItemView testid={item.resource_name} wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia
        link={to}
        src={getImageSrc(item?.image)}
        alt={item.title}
        backgroundImage
      />
      <ItemText>
        <ItemTitle>
          <Link to={item.link}>{item.title}</Link>
        </ItemTitle>
        <DotSeparator sx={{ color: 'text.secondary', mt: 1 }}>
          <UserName to={user.link} user={user} />
          <FormatDate
            data-testid="creationDate"
            value={creation_date}
            format="ll"
          />
        </DotSeparator>
      </ItemText>
    </ItemView>
  );
}
export default connectItemView(BlogItemSmallCard, actionCreators);
