import { Link, useGlobal } from '@metafox/framework';
import React from 'react';
import {
  DotSeparator,
  FormatDateRelativeToday,
  ItemView,
  ItemTitle
} from '@metafox/ui';
import { styled, Box } from '@mui/material';
import LoadingSkeleton from './LoadingSkeleton';

const name = 'PostItemSmallCard';

const Wrapper = styled('div', { name, slot: 'wrapper' })(({ theme }) => ({
  width: '100%'
}));

export default function PostItemSmallCard({
  item,
  identity,
  itemProps,
  user,
  state,
  handleAction,
  wrapAs,
  wrapProps
}) {
  const { i18n, useGetItem, usePageParams } = useGlobal();
  const pageParams = usePageParams();
  const {
    title,
    forum: forumIdentity,
    last_post: latestPostIdentity,
    link: to
  } = item;
  const forum = useGetItem(forumIdentity);
  const latest_post = useGetItem(latestPostIdentity);
  const user_latest_post = useGetItem(latest_post?.user);
  const user_latest = user_latest_post || user;
  const latest_info = latest_post || item;
  const isHideForum =
    pageParams?.resourceName === 'forum' && pageParams?.id == forum?.id;

  return (
    <ItemView testid={item.resource_name} wrapAs={wrapAs} wrapProps={wrapProps}>
      <Wrapper>
        <ItemTitle mb={0.5}>
          <Link to={to}>{title}</Link>
        </ItemTitle>
        <DotSeparator
          sx={{
            '& >span': { mb: 0.5, display: 'inline-block' }
          }}
        >
          <Box
            component={'span'}
            sx={{
              display: 'inline-flex',
              alignItems: 'center',
              color: 'text.secondary'
            }}
          >
            {i18n.formatMessage({ id: 'latest' })}:
            <Link
              color={'text.primary'}
              to={user_latest?.link}
              children={user_latest?.full_name}
              hoverCard={`/user/${user_latest?.id}`}
              sx={{ ml: 0.5 }}
            />
          </Box>
          <Link to={latest_info?.link} color={'text.secondary'}>
            <FormatDateRelativeToday value={latest_info?.creation_date} />
          </Link>
        </DotSeparator>
        <Box>
          {!isHideForum ? (
            <Link to={forum?.link} color={'primary'}>
              {forum?.title}
            </Link>
          ) : null}
        </Box>
      </Wrapper>
    </ItemView>
  );
}
PostItemSmallCard.LoadingSkeleton = LoadingSkeleton;
PostItemSmallCard.displayName = 'ForumPostItem(PostItemSmallCard)';
