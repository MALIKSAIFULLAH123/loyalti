import { Link, useGlobal } from '@metafox/framework';
import { QuizItemProps } from '@metafox/quiz/types';
import {
  Image,
  ItemView,
  Statistic,
  TruncateText,
  UserName
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import React from 'react';
import useStyles from './styles';

export default function QuizItemSmallCard({
  item,
  handleAction,
  state,
  identity,
  user,
  wrapAs,
  wrapProps
}: QuizItemProps) {
  const { ItemActionMenu, assetUrl } = useGlobal();
  const classes = useStyles();

  if (!item) return null;

  const to = `/quiz/${item.id}`;
  const cover = getImageSrc(item.image, '240', assetUrl('quiz.cover_no_image'));

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <div className={`${classes.root} ${classes.smallView}`}>
        <div className={classes.outer}>
          {cover ? (
            <div className={classes.media}>
              <Image link={to} src={cover} aspectRatio={'11'} backgroundImage />
            </div>
          ) : null}
          <div className={classes.inner}>
            <div className={classes.header}>
              <Link to={to} className={classes.title}>
                <TruncateText variant={'body1'} lines={2}>
                  {item.title}
                </TruncateText>
              </Link>
              <ItemActionMenu
                identity={identity}
                icon={'ico-dottedmore-vertical-o'}
                state={state}
                handleAction={handleAction}
              />
            </div>
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
              display={'total_play'}
            />
          </div>
        </div>
      </div>
    </ItemView>
  );
}
