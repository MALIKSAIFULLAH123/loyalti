import { Link } from '@metafox/framework';
import { PagesItemProps } from '@metafox/pages/types';
import {
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  Statistic
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import * as React from 'react';
import useStyles from './styles';

export default function PageItemSmallCard({
  item,
  identity,
  wrapAs,
  wrapProps
}: PagesItemProps) {
  const classes = useStyles();

  if (!item) return null;

  const { id, title, statistic, summary } = item;
  const to = `/page/${id}`;

  const avatar = getImageSrc(item.avatar, '200');

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia link={to} src={avatar} />
      <ItemText>
        <ItemTitle>
          <Link hoverCard to={to} color={'inherit'}>
            {title}
          </Link>
        </ItemTitle>
        <ItemSummary>{summary}</ItemSummary>
        <ItemSummary>
          <Statistic
            values={statistic}
            display={'total_like'}
            className={classes.pageLike}
          />
        </ItemSummary>
      </ItemText>
    </ItemView>
  );
}
