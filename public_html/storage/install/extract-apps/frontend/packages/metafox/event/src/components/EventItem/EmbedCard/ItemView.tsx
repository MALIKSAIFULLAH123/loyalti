import { EventDetailViewProps } from '@metafox/event/types';
import { isEventEnd } from '@metafox/event/utils';
import { Link, useGlobal } from '@metafox/framework';
import {
  DotSeparator,
  FeedEmbedCard,
  FormatDate,
  TruncateText,
  SponsorFlag,
  FeaturedFlag
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled, Typography } from '@mui/material';
import { isEqual } from 'lodash';
import React from 'react';
import InterestedButton from '../../InterestedButton';

const name = 'EventEmbedItemView';

const BgCover = styled('div', {
  name,
  slot: 'bgCover'
})(({ theme }) => ({
  backgroundRepeat: 'no-repeat',
  backgroundPosition: 'center',
  backgroundSize: 'cover',
  height: 200,
  [theme.breakpoints.down('sm')]: {
    height: 160
  }
}));

const TypographyStyled = styled(Typography, {
  name,
  slot: 'TypographyStyled'
})(({ theme }) => ({
  fontSize: theme.spacing(1.625),
  fontWeight: '700',
  lineHeight: theme.spacing(2.5)
}));

const FlagWrapper = styled('span', {
  name,
  slot: 'flagWrapper'
})(({ theme }) => ({
  marginLeft: 'auto',
  '& > .MuiFlag-root': {
    marginLeft: theme.spacing(2.5),
    [theme.breakpoints.down('sm')]: {
      marginLeft: theme.spacing(0.5)
    }
  }
}));

const Title = styled(Box, {
  name,
  slot: 'title'
})(({ theme }) => ({
  '& a': {
    color: theme.palette.text.primary
  },
  marginBottom: theme.spacing(1.25),
  fontWeight: 600
}));

const Description = styled(Box, {
  name,
  slot: 'description'
})(({ theme }) => ({
  color: theme.palette.text.hint,
  '& p': {
    margin: 0
  }
}));

const ItemInner = styled('div', {
  name,
  slot: 'itemInner'
})(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  padding: theme.spacing(2),
  display: 'flex',
  flexDirection: 'column'
}));

const WrapperInfoFlag = styled(Box, {
  name,
  slot: 'wrapperInfoFlag'
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  marginTop: theme.spacing(2)
}));

const Actions = styled('div', {
  name,
  slot: 'actions'
})(({ theme }) => ({
  marginRight: theme.spacing(1.5),
  display: 'flex'
}));

export default function EventEmbedItemView({
  item,
  feed,
  handleAction,
  actions,
  identity,
  isShared
}: EventDetailViewProps) {

  const { i18n, useSession } = useGlobal();
  const { loggedIn } = useSession();

  if (!item) return null;

  const {
    title,
    location,
    image,
    link,
    statistic,
    start_time,
    end_time,
    rsvp,
    is_online,
    is_featured,
    is_sponsor
  } = item;

  const isEnd = isEventEnd(end_time);

  return (
    <FeedEmbedCard variant="grid" item={item} feed={feed} isShared={isShared}>
      {image ? (
        <Link
          to={link}
          identity={feed?._identity}
          identityTracking={feed?._identity}
        >
          <BgCover
            style={{
              backgroundImage: `url(${getImageSrc(image, '1024')})`
            }}
          />
        </Link>
      ) : null}
      <ItemInner>
        <Title>
          <TruncateText variant="h4" lines={1}>
            <Link
              to={link}
              identity={feed?._identity}
              identityTracking={feed?._identity}
            >
              {title}
            </Link>
          </TruncateText>
        </Title>
        <Box mb={1.25}>
          <Typography
            component="div"
            variant="body1"
            textTransform="uppercase"
            color="primary"
          >
            <DotSeparator>
              <FormatDate
                data-testid="startedDate"
                value={start_time}
                format="LL"
              />
              <FormatDate
                data-testid="startedDate"
                value={start_time}
                format="LT"
              />
            </DotSeparator>
          </Typography>
        </Box>
        {is_online ? (
          <Description>
            <TruncateText variant={'subtitle2'} lines={1}>
              {i18n.formatMessage({ id: 'online' })}
            </TruncateText>
          </Description>
        ) : (
          <Description>
            <TruncateText variant={'body1'} lines={1}>
              {location?.address}
            </TruncateText>
          </Description>
        )}

        <WrapperInfoFlag>
          <Box display="flex" alignItems="center">
            {loggedIn ? (
              <Actions>
                <InterestedButton
                  disabled={isEnd || isEqual(item.extra?.can_rsvp, false)}
                  actions={actions}
                  handleAction={handleAction}
                  identity={identity}
                  rsvp={rsvp}
                />
              </Actions>
            ) : null}
            <TypographyStyled variant="body2" color="text.hint">
              {statistic?.total_member ? (
                <>
                  {i18n.formatMessage(
                    { id: 'people_going' },
                    { value: statistic.total_member }
                  )}
                </>
              ) : null}
            </TypographyStyled>
          </Box>
          <FlagWrapper>
            <FeaturedFlag
              variant="text"
              value={is_featured}
              color="primary"
              showTitleMobile={false}
            />
            <SponsorFlag
              color="yellow"
              variant="text"
              value={is_sponsor}
              showTitleMobile={false}
              item={item}
            />
          </FlagWrapper>
        </WrapperInfoFlag>
      </ItemInner>
    </FeedEmbedCard>
  );
}

EventEmbedItemView.LoadingSkeleton = () => null;
EventEmbedItemView.displayName = 'EventItem_EmbedCard';
