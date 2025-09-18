import { Link, useGlobal } from '@metafox/framework';
import { useBlock } from '@metafox/layout';
import { PollItemShape } from '@metafox/poll/types';
import {
  FeaturedFlag,
  ItemAction,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  ItemViewProps,
  PendingFlag,
  SponsorFlag,
  Statistic
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled } from '@mui/material';
import React from 'react';
import useStyles from './styles';

const FlagWrapper = styled('span', {
  name: 'FlagWrapper'
})(({ theme }) => ({
  display: 'flex',
  marginBottom: theme.spacing(1)
}));

const ClosedStyled = styled(Box, {
  name: 'ClosedStyled'
})(({ theme }) => ({
  '&:before': {
    color: theme.palette.text.secondary,
    content: '"·"',
    paddingLeft: '0.25em',
    paddingRight: '0.25em'
  }
}));

export default function PollItemMainCard({
  identity,
  item,
  itemProps,
  user,
  handleAction,
  state,
  wrapAs,
  wrapProps
}: ItemViewProps<PollItemShape>) {
  const classes = useStyles();
  const { ItemActionMenu, useIsMobile, i18n } = useGlobal();
  const isMobile = useIsMobile();
  const { itemLinkProps } = useBlock();
  const asModal = itemLinkProps?.asModal;

  if (!item) return null;

  const { link: to } = item;
  const cover = getImageSrc(item.image, '240');

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      identity={identity}
    >
      <Link to={to} asModal={asModal} identityTracking={identity}>
        <ItemMedia
          src={cover}
          alt={item.question}
          backgroundImage
        />
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
            children={item.question}
            color={'inherit'}
            identityTracking={identity}
          />
        </ItemTitle>
        {itemProps.showActionMenu ? (
          <ItemAction
            placement={isMobile ? 'bottom-end' : 'top-end'}
            spacing="normal"
          >
            <ItemActionMenu
              identity={identity}
              icon={'ico-dottedmore-vertical-o'}
              state={state}
              handleAction={handleAction}
            />
          </ItemAction>
        ) : null}
        <div className={classes.itemStatistic}>
          <Statistic
            values={item.statistic}
            display={'total_vote'}
            skipZero={false}
          />
          {item.is_closed ? (
            <ClosedStyled color={'text.secondary'}>
              {i18n.formatMessage({ id: 'voting_closed' })}
            </ClosedStyled>
          ) : null}
        </div>
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
