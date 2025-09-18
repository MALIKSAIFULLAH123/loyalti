import { Link, useGlobal } from '@metafox/framework';
import { useBlock } from '@metafox/layout';
import { QuizItemProps } from '@metafox/quiz/types';
import {
  FeaturedFlag,
  ItemAction,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  SponsorFlag,
  PendingFlag
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { styled, Typography } from '@mui/material';
import React from 'react';
import useStyles from './styles';

const FlagWrapper = styled('span', {
  name: 'FlagWrapper'
})(({ theme }) => ({
  display: 'flex',
  marginBottom: theme.spacing(1)
}));

export default function QuizItemMainCard({
  item,
  itemProps,
  user,
  handleAction,
  state,
  identity,
  wrapAs,
  wrapProps
}: QuizItemProps) {
  const { ItemActionMenu, useIsMobile, i18n } = useGlobal();
  const classes = useStyles();
  const isMobile = useIsMobile();

  const { itemLinkProps = {} } = useBlock();

  if (!item) return null;

  const { link: to } = item || {};

  const cover = getImageSrc(item.image, '240');

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      identity={identity}
    >
      <Link to={to} asModal={itemLinkProps.asModal} identityTracking={identity}>
        <ItemMedia src={cover} alt={item.title} backgroundImage />
      </Link>
      <ItemText>
        {isMobile ? (
          <FlagWrapper>
            <FeaturedFlag variant="itemView" value={item.is_featured} />
            <SponsorFlag
              variant="itemView"
              value={item.is_sponsor}
              item={item}
            />
            <PendingFlag variant="itemView" value={item.is_pending} />
          </FlagWrapper>
        ) : null}
        <ItemTitle>
          <Link
            to={to}
            color={'inherit'}
            children={item.title}
            identityTracking={identity}
          />
        </ItemTitle>
        {itemProps.showActionMenu ? (
          <ItemAction placement="top-end" spacing="normal">
            <ItemActionMenu
              identity={identity}
              icon={'ico-dottedmore-vertical-o'}
              state={state}
              handleAction={handleAction}
            />
          </ItemAction>
        ) : null}
        <Typography variant="body2" color="text.secondary" mt={1}>
          {i18n.formatMessage(
            { id: 'total_play' },
            { value: item.statistic.total_play }
          )}
        </Typography>
        {!isMobile ? (
          <div className={classes.itemFlag}>
            <FeaturedFlag variant="itemView" value={item.is_featured} />
            <SponsorFlag
              variant="itemView"
              value={item.is_sponsor}
              item={item}
            />
            <PendingFlag variant="itemView" value={item.is_pending} />
          </div>
        ) : null}
      </ItemText>
    </ItemView>
  );
}
