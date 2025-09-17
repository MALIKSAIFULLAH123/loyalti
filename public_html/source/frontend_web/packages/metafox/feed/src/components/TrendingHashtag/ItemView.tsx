import { Link } from '@metafox/framework';
import { ItemSummary, ItemTitle, ItemView, Statistic } from '@metafox/ui';
import React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import useStyles from './styles';

export default function TrendingHashtagItemView({
  item,
  identity,
  wrapAs,
  wrapProps
}) {
  const classes = useStyles();

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemTitle className={classes.title}>
        <Link to={item?.link}>{item?.text}</Link>
      </ItemTitle>
      <ItemSummary>
        <Statistic
          values={{
            ...item?.statistic,
            total_post: item?.statistic?.total_item
          }}
          display={'total_post'}
          fontStyle={'minor'}
        />
      </ItemSummary>
    </ItemView>
  );
}

TrendingHashtagItemView.LoadingSkeleton = LoadingSkeleton;
