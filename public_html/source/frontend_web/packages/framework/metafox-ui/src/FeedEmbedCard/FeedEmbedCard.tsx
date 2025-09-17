import {
  FeedEmbedCardProps,
  FromNow,
  PrivacyIcon,
  UserAvatar,
  UserName
} from '@metafox/ui';
import * as React from 'react';
import useStyles from './styles';
import { useGlobal, GlobalState, Link } from '@metafox/framework';
import { styled, Box } from '@mui/material';
import HeadlineSpan from '@metafox/feed/components/HeadlineSpan';
import HeadlineInfo from '@metafox/feed/components/HeadlineInfo';
import FeedStatus from '@metafox/feed/components/FeedStatus';
import { useSelector } from 'react-redux';
import { getTaggedFriendsPhotoSelector } from '@metafox/core/selectors/status';
import { isEmpty } from 'lodash';

const name = 'MuiFeedEmbedCard';

const RootStyled = styled(Box, {
  name,
  shouldForwardProp: props =>
    props !== 'link' &&
    props !== 'variant' &&
    props !== 'bottomSpacing' &&
    props !== 'isShared'
})<{
  link?: string;
  variant: string;
  bottomSpacing: string;
  isShared?: boolean;
}>(({ theme, link, variant, bottomSpacing, isShared }) => ({
  display: 'block',
  ...(isShared && {
    borderRadius: theme.shape.borderRadius,
    border: theme.mixins.border('secondary'),
    padding: theme.spacing(2),
    overflow: 'hidden'
  }),
  ...(link && {
    cursor: 'pointer'
  }),
  ...(bottomSpacing === 'normal' && {
    paddingBottom: theme.spacing(2)
  }),
  ...(bottomSpacing === 'dense' && {
    paddingBottom: theme.spacing(0)
  })
}));

const AvatarWrapper = styled('div', { name, slot: 'AvatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1.5)
  })
);

const HeaderStyled = styled('div', { name, slot: 'HeaderStyled' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'row',
    paddingTop: theme.spacing(1.5)
  })
);

const HeaderInfoStyled = styled('div', { name, slot: 'HeaderInfoStyled' })(
  ({ theme }) => ({
    flex: 1
  })
);

const HeaderHeadlineStyled = styled('div', {
  name,
  slot: 'HeaderHeadlineStyled'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  '& a:not(.simpleLink)': {
    fontWeight: theme.typography.fontWeightBold,
    color: theme.palette.text.primary
  },
  '& a:hover': {
    textDecoration: 'underline'
  }
}));

const PrivacyBlockStyled = styled('div', { name, slot: 'PrivacyBlockStyled' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'row',
    alignItems: 'center',
    color: theme.palette.text.secondary,
    fontSize: '0.8125rem',
    paddingTop: '0.25em'
  })
);

const SeparateSpanStyled = styled('div', { name, slot: 'SeparateSpanStyled' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    '& span + span:before': {
      content: '"·"',
      display: 'inline-block',
      padding: `${theme.spacing(0, 0.5)}`
    }
  })
);

const ItemOuter = styled(Box, {
  name: 'FeedItem',
  slot: 'feedItemOuter',
  shouldForwardProp: prop => prop !== 'className' && prop !== 'isShared',
  overridesResolver(props, styles) {
    return [!props.isShared && styles.feedItemOuter];
  }
})<{ className: string; isShared: boolean }>(
  ({ theme, className, isShared }) => ({
    display: 'flex',
    borderRadius: theme.shape.borderRadius,
    border: theme.mixins.border('secondary'),
    overflow: 'hidden',
    ...(className === 'default' && {
      display: 'block'
    }),
    ...(className === 'grid' && {
      flexDirection: 'column'
    }),
    ...(className === 'list' && {
      flexDirection: 'row',
      [theme.breakpoints.down('sm')]: {
        flexDirection: 'column',
        '& .media': {
          width: '100% !important'
        }
      }
    })
  })
);

export default function FeedEmbedCard({
  variant = 'list',
  children,
  item,
  feed,
  // isShare in composer feed
  isShared: isSharedProp,
  sx,
  sxOuter
}: FeedEmbedCardProps) {
  const classes = useStyles();
  const { useSession, useGetItem } = useGlobal();
  const { loggedIn } = useSession();
  const bottomSpacing = loggedIn ? 'dense' : 'normal';
  const user = useGetItem(item?.user) as any;
  const { embed_object, item_type } = feed || {};
  const embed_objectData = useGetItem(embed_object);

  const isShared = isSharedProp || item_type === 'share';
  const tagged_friends_list = useSelector((state: GlobalState) =>
    getTaggedFriendsPhotoSelector(state, item)
  );
  const {
    info,
    location,
    is_show_location,
    status,
    status_background,
    link,
    feed_link,
    feed_status
  } = item || {};

  // feed_link to check for case share post link
  const feedLink = feed_link ?? link;
  // feed_status to check for case share event
  const feedStatus = feed_status ?? status;

  return (
    <RootStyled
      data-testid="feedEmbedCard"
      variant={variant}
      bottomSpacing={bottomSpacing}
      isShared={isShared}
      sx={sx}
    >
      {children ? (
        <ItemOuter sx={sxOuter} className={variant} isShared={isShared}>
          {children}
        </ItemOuter>
      ) : null}
      {item && isShared ? (
        <Box mb={isEmpty(status_background) ? 2 : 0}>
          <HeaderStyled>
            <AvatarWrapper>
              <UserAvatar user={user} size={48} />
            </AvatarWrapper>
            <HeaderInfoStyled>
              <HeaderHeadlineStyled>
                <HeadlineSpan>
                  <UserName to={user?.user_name || user?.link} user={user} />{' '}
                </HeadlineSpan>
                <HeadlineInfo
                  isEmbed
                  info={info}
                  embed_object={embed_objectData}
                  classes={classes}
                  item={item}
                  tagged_friends={tagged_friends_list}
                  location={location}
                  is_show_location={is_show_location}
                  item_type={item?.item_type || item?.resource_name}
                  item_id={item.item_id || item?.id}
                  total_friends_tagged={item.total_friends_tagged}
                />
              </HeaderHeadlineStyled>
              <PrivacyBlockStyled>
                <SeparateSpanStyled>
                  <span>
                    <Link color="inherit" to={feedLink}>
                      <FromNow value={item?.creation_date} />
                    </Link>
                  </span>
                  <PrivacyIcon
                    value={item?.privacy}
                    item={item?.privacy_detail}
                  />
                </SeparateSpanStyled>
              </PrivacyBlockStyled>
            </HeaderInfoStyled>
          </HeaderStyled>
          {feedStatus ? (
            <Box mt={1}>
              <FeedStatus
                status={feedStatus}
                backgroundImage={status_background}
                identity={feed?._identity}
                isShared={isShared}
              />
            </Box>
          ) : null}
        </Box>
      ) : null}
    </RootStyled>
  );
}
