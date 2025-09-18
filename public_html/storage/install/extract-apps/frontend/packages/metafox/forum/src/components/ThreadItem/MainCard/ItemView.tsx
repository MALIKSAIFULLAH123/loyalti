import { Link, useGlobal } from '@metafox/framework';
import React from 'react';
import {
  ItemAction,
  ItemText,
  ItemTitle,
  ItemView,
  SponsorFlag,
  LineIcon,
  PendingFlag,
  UserAvatar,
  FormatDateRelativeToday,
  TruncateText,
  UserName
} from '@metafox/ui';
import { styled, Box, Chip, useMediaQuery, useTheme } from '@mui/material';
import { slugify } from '@metafox/utils';
import { ThreadItemProps } from '@metafox/forum/types';
import { useBlock } from '@metafox/layout';

const name = 'ThreadItemMainCard';

const SubInfoStyled = styled('div', { name, slot: 'subInfoStyled' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    marginRight: theme.spacing(1),
    flexFlow: 'wrap',
    [theme.breakpoints.down('sm')]: {
      flexDirection: 'column',
      alignItems: 'flex-start'
    }
  })
);

const ProfileLink = styled(UserName, { name, slot: 'profileLink' })(
  ({ theme }) => ({
    color: theme.palette.text.primary,
    fontSize: theme.mixins.pxToRem(13),
    '&:hover': {
      textDecoration: 'underline'
    }
  })
);

const ForumLink = styled(TruncateText, { name, slot: 'forumLink' })(
  ({ theme }) => ({
    color: theme.palette.primary.main
  })
);

const InfoStyled = styled('div', { name, slot: 'infoStyled' })(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  position: 'relative'
}));

const TotalStatistic = styled(Box, { name, slot: 'totalComment' })(
  ({ theme }) => ({
    marginRight: theme.spacing(2),
    fontSize: theme.mixins.pxToRem(13),
    display: 'inline-flex'
  })
);

const IconTitle = styled(LineIcon, { name, slot: 'IconTitle' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1),
    color: theme.palette.success.main,
    display: 'inline-block',
    transform: 'translateY(2px)'
  })
);

const Profile = styled(Box, { name, slot: 'Profile' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  flexWrap: 'wrap',
  [theme.breakpoints.down('sm')]: {
    marginBottom: theme.spacing(0.5)
  }
}));

const FlagWrapper = styled('span', {
  slot: 'FlagWrapper',
  name
})(({ theme }) => ({
  display: 'inline-flex',
  '&>span': {
    marginRight: theme.spacing(0.5),
    marginBottom: theme.spacing(0)
  }
}));

export default function ThreadItemMainCard(props: ThreadItemProps) {
  const {
    item,
    identity,
    itemProps,
    user,
    state,
    handleAction,
    wrapAs,
    wrapProps
  } = props;
  const { ItemActionMenu, useGetItem, i18n, usePageParams, jsxBackend } =
    useGlobal();
  const pageParams = usePageParams();
  const { noPin } = useBlock();
  // Todo: need improve for all site (tablet structure for json file)
  const isTablet = useMediaQuery('(max-width:1199px)');
  const SmallCard = jsxBackend.get('forum_thread.itemView.smallCard');

  const {
    statistic,
    title,
    creation_date,
    forum: forumEntity,
    is_closed,
    is_sticked,
    is_wiki,
    last_post: latestPostIdentity,
    link: to,
    is_pending
  } = item || {};
  const latest_post = useGetItem(latestPostIdentity);
  const user_latest_post = useGetItem(latest_post?.user);
  let iconMedia = '';
  const theme = useTheme();

  if (is_sticked && !noPin) {
    iconMedia = 'ico-thumb-tack';
  }

  if (is_wiki && pageParams?.tab !== 'wiki_thread') {
    iconMedia = 'ico-file-word';
  }

  const forum: Record<string, any> = useGetItem(forumEntity);

  const toForum = forum
    ? `/forum/${forum?.id}/${slugify(forum?.title || '')}`
    : '';
  const isHideForum =
    pageParams?.resourceName === 'forum' && pageParams?.id == forum?.id;

  if (!user || !item) return null;

  if (isTablet) {
    return <SmallCard {...props} />;
  }

  return (
    <ItemView
      testid={item.resource_name}
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      identity={identity}
    >
      <ItemText>
        <InfoStyled>
          <Box sx={{ display: 'flex', flex: 1, minWidth: 0 }}>
            <Box mr={1.5}>
              <UserAvatar
                hoverCard={`/user/${user?.id}`}
                size={40}
                user={user}
                variant="circular"
                sx={{ fontSize: '15px' }}
                srcSizePrefers={'50x50'}
              />
            </Box>

            <Box sx={{ flex: 1, minWidth: 0 }}>
              <Box>
                <ItemTitle>
                  {iconMedia && <IconTitle icon={iconMedia} />}
                  {item.is_sponsor || item.is_pending ? (
                    <FlagWrapper>
                      <SponsorFlag
                        variant="itemView"
                        value={item.is_sponsor}
                        item={item}
                      />
                      <PendingFlag variant="itemView" value={item.is_pending} />
                    </FlagWrapper>
                  ) : null}
                  <Link to={to} identityTracking={identity}>
                    {title}
                  </Link>
                </ItemTitle>
              </Box>
              <SubInfoStyled sx={{ color: 'text.secondary' }}>
                {is_closed && (
                  <Chip
                    size="small"
                    label={i18n.formatMessage({ id: 'closed' })}
                    variant="filled"
                    sx={{
                      mr: 1,
                      mb: { sm: 0, xs: 1 },
                      backgroundColor: theme.palette.mode === 'dark'
                      ? theme.palette.grey[600]
                      : theme.palette.text.secondary,
                      color: 'default.contrastText',
                      fontSize: '13px'
                    }}
                  />
                )}
                <Profile>
                  <TruncateText lines={1} sx={{ maxWidth: '160px' }}>
                    <ProfileLink to={user.link} user={user} />
                  </TruncateText>
                </Profile>
                <Box
                  sx={{ display: { sm: 'block', xs: 'none' } }}
                  mr={1}
                  ml={1}
                >
                  {'·'}
                </Box>
                <Link to={to} identityTracking={identity}>
                  <FormatDateRelativeToday
                    data-testid={'forumTimeCreate'}
                    value={creation_date}
                  />
                </Link>
                <Box
                  sx={{ display: { sm: 'block', xs: 'none' } }}
                  mr={1}
                  ml={1}
                >
                  {'·'}
                </Box>
                {!isHideForum && forum?.title ? (
                  <>
                    <ForumLink
                      lines={1}
                      sx={{ maxWidth: '160px' }}
                      variant={'body2'}
                    >
                      <Link to={toForum}>{forum?.title}</Link>
                    </ForumLink>
                    {!is_pending && (
                      <Box
                        sx={{ display: { sm: 'block', xs: 'none' } }}
                        mr={1}
                        ml={1}
                      >
                        {'·'}
                      </Box>
                    )}
                  </>
                ) : null}
                {!is_pending && (
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <Link
                      to={to}
                      sx={{ display: 'inline-flex' }}
                      identityTracking={identity}
                    >
                      <TotalStatistic>
                        <LineIcon icon="ico-thumbup-o" />
                        <Box ml={0.5} sx={{ lineHeight: 1.1 }}>
                          {statistic?.total_like ?? 0}
                        </Box>
                      </TotalStatistic>
                    </Link>
                    <Link
                      to={to}
                      sx={{ display: 'inline-flex' }}
                      identityTracking={identity}
                    >
                      <TotalStatistic>
                        <LineIcon icon="ico-comment-square-empty-o" />
                        <Box ml={0.5} sx={{ lineHeight: 1.1 }}>
                          {statistic?.total_comment ?? 0}
                        </Box>
                      </TotalStatistic>
                    </Link>
                  </Box>
                )}
              </SubInfoStyled>
            </Box>
          </Box>
          <Box ml={2} sx={{ display: 'flex', alignItems: 'center' }}>
            {latest_post ? (
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <Box
                  sx={{
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'flex-end'
                  }}
                >
                  <Link
                    to={latest_post?.link}
                    color={'text.primary'}
                    variant={'body2'}
                  >
                    <FormatDateRelativeToday
                      data-testid={'forumLatestTimeCreate'}
                      value={latest_post?.creation_date}
                    />
                  </Link>
                  <Box
                    sx={{
                      display: 'flex',
                      alignItems: 'center',
                      color: 'text.secondary'
                    }}
                  >
                    <TruncateText
                      lines={1}
                      sx={{ maxWidth: '160px' }}
                      color={'text.secondary'}
                      variant={'body2'}
                    >
                      <Link
                        color={'text.secondary'}
                        to={user_latest_post?.link}
                        children={user_latest_post?.full_name}
                        hoverCard={`/user/${user_latest_post?.id}`}
                      />
                    </TruncateText>
                  </Box>
                </Box>
                <UserAvatar
                  hoverCard={`/user/${user_latest_post?.id}`}
                  size={24}
                  user={user_latest_post}
                  variant="circular"
                  sx={{ fontSize: '9px', ml: 1.5 }}
                  srcSizePrefers={'50x50'}
                />
              </Box>
            ) : null}
            {itemProps.showActionMenu ? (
              <ItemAction ml={0.5} mr={-1}>
                <ItemActionMenu
                  identity={identity}
                  icon={'ico-dottedmore-vertical-o'}
                  state={state}
                  handleAction={handleAction}
                />
              </ItemAction>
            ) : null}
          </Box>
        </InfoStyled>
      </ItemText>
    </ItemView>
  );
}
