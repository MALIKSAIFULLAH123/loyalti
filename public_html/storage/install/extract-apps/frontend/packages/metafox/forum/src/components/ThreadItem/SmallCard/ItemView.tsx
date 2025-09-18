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
  DotSeparator,
  TruncateText,
  UserName
} from '@metafox/ui';
import { styled, Box, Chip } from '@mui/material';
import { slugify } from '@metafox/utils';
import { ThreadItemProps } from '@metafox/forum/types';
import { useBlock } from '@metafox/layout';

const name = 'ThreadItemMainCard';

const ProfileLink = styled(UserName, { name, slot: 'profileLink' })(
  ({ theme }) => ({
    color: theme.palette.text.primary,
    fontSize: theme.mixins.pxToRem(13)
  })
);

const ForumLink = styled(Link, { name, slot: 'forumLink' })(({ theme }) => ({
  color: theme.palette.primary.main,
  fontSize: theme.mixins.pxToRem(13),
  '&:hover': {
    textDecoration: 'underline'
  }
}));

const InfoStyled = styled('div', { name, slot: 'infoStyled' })(({ theme }) => ({
  display: 'flex'
}));

const TotalComment = styled(Link, { name, slot: 'totalComment' })(
  ({ theme }) => ({
    marginRight: theme.spacing(2),
    fontSize: theme.mixins.pxToRem(13),
    display: 'inline-flex'
  })
);

const IconTitle = styled(LineIcon, { name, slot: 'IconTitle' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1.5),
    color: theme.palette.success.main,
    fontSize: theme.spacing(2.5)
  })
);

const Profile = styled(Box, { name, slot: 'Profile' })(({ theme }) => ({
  display: 'inline-flex',
  alignItems: 'center',
  flexWrap: 'wrap'
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

export default function ThreadItemMainCard({
  item,
  identity,
  itemProps,
  user,
  state,
  handleAction,
  wrapAs,
  wrapProps
}: ThreadItemProps) {
  const { ItemActionMenu, useGetItem, i18n, usePageParams } = useGlobal();
  const pageParams = usePageParams();
  const { noPin } = useBlock();

  const {
    statistic,
    title,
    creation_date,
    forum: forumEntity,
    is_closed,
    is_sticked,
    is_wiki,
    last_post: latestPostIdentity,
    link: to
  } = item || {};
  const latest_post = useGetItem(latestPostIdentity);
  const user_latest_post = useGetItem(latest_post?.user);
  let iconMedia = '';

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
                sx={{ fontSize: '13px' }}
              />
            </Box>
            <Box sx={{ flex: 1, minWidth: 0 }}>
              <Box mb={0.25} sx={{ display: 'flex', alignItems: 'center' }}>
                {iconMedia && <IconTitle icon={iconMedia} />}
                <FlagWrapper>
                  <SponsorFlag
                    variant="itemView"
                    value={item.is_sponsor}
                    item={item}
                  />
                  <PendingFlag
                    variant="itemView"
                    value={item.is_pending}
                    showTitleMobile
                  />
                </FlagWrapper>
                <ItemTitle>
                  <Link to={to} identityTracking={identity}>
                    {title}
                  </Link>
                </ItemTitle>
              </Box>
              <Box sx={{ color: 'text.secondary' }}>
                <DotSeparator
                  sx={{
                    '& >span': { mb: 0.5, display: 'inline-block' }
                  }}
                >
                  {is_closed && (
                    <Chip
                      size="small"
                      label={i18n.formatMessage({ id: 'closed' })}
                      variant="filled"
                      sx={{
                        mr: 1,
                        mb: { sm: 0, xs: 1 },
                        color: 'default.contrastText',
                        backgroundColor: 'text.secondary',
                        fontSize: '13px'
                      }}
                    />
                  )}
                  <Profile>
                    <ProfileLink to={user.link} user={user} />
                  </Profile>
                  <FormatDateRelativeToday value={creation_date} />
                  {!isHideForum && forum?.title ? (
                    <TruncateText
                      lines={1}
                      sx={{ maxWidth: '150px', display: 'inline-flex' }}
                    >
                      <ForumLink
                        sx={{ mt: { sm: 0, xs: 0.5 } }}
                        to={toForum}
                        children={forum?.title}
                      />
                    </TruncateText>
                  ) : null}

                  {!item.is_pending && (
                    <Box sx={{ display: 'flex', alignItems: 'center' }}>
                      <TotalComment to={to} identityTracking={identity}>
                        <LineIcon icon="ico-thumbup-o" />
                        <Box ml={0.5} sx={{ lineHeight: 1.1 }}>
                          {statistic?.total_like ?? 0}
                        </Box>
                      </TotalComment>
                      <TotalComment to={to} identityTracking={identity}>
                        <LineIcon icon="ico-comment-square-empty-o" />
                        <Box ml={0.5} sx={{ lineHeight: 1.1 }}>
                          {statistic?.total_comment ?? 0}
                        </Box>
                      </TotalComment>
                    </Box>
                  )}
                </DotSeparator>
              </Box>
              {latest_post ? (
                <Box
                  sx={{
                    display: 'flex',
                    flexDirection: 'column'
                  }}
                >
                  <Box sx={{ display: 'flex', color: 'text.secondary' }}>
                    <DotSeparator>
                      <Box component={'span'}>
                        {i18n.formatMessage({ id: 'latest' })}:
                        <Link
                          color={'text.secondary'}
                          to={user_latest_post?.link}
                          children={user_latest_post?.full_name}
                          hoverCard={`/user/${user_latest_post?.id}`}
                          sx={{ ml: 0.5 }}
                        />
                      </Box>
                      <Link
                        to={latest_post?.link}
                        color={'text.secondary'}
                        identityTracking={identity}
                      >
                        <FormatDateRelativeToday
                          value={latest_post?.creation_date}
                        />
                      </Link>
                    </DotSeparator>
                  </Box>
                </Box>
              ) : null}
            </Box>
          </Box>
          {itemProps.showActionMenu ? (
            <ItemAction ml={1} mr={-1}>
              <ItemActionMenu
                identity={identity}
                icon={'ico-dottedmore-vertical-o'}
                state={state}
                handleAction={handleAction}
              />
            </ItemAction>
          ) : null}
        </InfoStyled>
      </ItemText>
    </ItemView>
  );
}
