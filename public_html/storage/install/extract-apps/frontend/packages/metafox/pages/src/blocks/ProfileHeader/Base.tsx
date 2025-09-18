import { BlockViewProps, Link, MenuShape, useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import {
  ButtonAction,
  Container,
  LineIcon,
  ProfileMenu,
  SponsorFlag,
  StickyBar
} from '@metafox/ui';
import {
  colorHash,
  filterShowWhen,
  getImageSrc,
  shortenFullName
} from '@metafox/utils';
import {
  Avatar,
  Box,
  Button,
  styled,
  Typography,
  useScrollTrigger
} from '@mui/material';
import { nth, isEmpty } from 'lodash';
import React, { useCallback } from 'react';
import { PagesItemShape } from '../../types';
import { LoadingSkeleton } from './LoadingSkeleton';
import PendingCard from './PendingCard';
import InviteCard from './InviteCard';
import { INVITE, LIKE, LIKED, MANAGE } from '@metafox/pages/constant';

export interface Props extends BlockViewProps {
  item: PagesItemShape;
  identity: string;
  profileMenu: MenuShape;
  profileActionMenu: MenuShape;
  handleAction: any;
  state: any;
}

const name = 'HeaderProfileInPageDetail';

const FeaturedIcon = styled(LineIcon, { name: 'FeaturedIcon' })(
  ({ theme }) => ({
    color: theme.palette.primary.main,
    marginLeft: theme.spacing(2),
    fontSize: 24
  })
);

const Wrapper = styled('div', { name: 'Wrapper' })(({ theme }) => ({
  display: 'block'
}));

const UserContainer = styled(Box, { name: 'UserContainer' })(({ theme }) => ({
  padding: theme.spacing(2),
  backgroundColor: theme.mixins.backgroundColor('paper'),
  [theme.breakpoints.down('sm')]: {
    paddingBottom: 0
  }
}));

const UserInfoContainer = styled('div', {
  name,
  slot: 'infoNameActionMenu',
  overridesResolver(props, styles) {
    return [styles.infoNameActionMenu];
  }
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  [theme.breakpoints.down('sm')]: {
    flexFlow: 'column',
    width: '100%',
    alignItems: 'center',
    paddingBottom: 0
  }
}));

const UserInfo = styled(Box, {
  name: 'UserInfoProfileHeader',
  slot: 'nameUser',
  overridesResolver(props, styles) {
    return [styles.nameUser];
  }
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  [theme.breakpoints.down('sm')]: {
    flexFlow: 'column',
    width: '100%',
    alignItems: 'center',
    marginBottom: theme.spacing(2)
  }
}));

const Summary = styled('div', { name: 'Summary' })(({ theme }) => ({
  color: theme.palette.text.secondary,
  fontSize: theme.mixins.pxToRem(18),
  paddingTop: theme.spacing(1)
}));

const WrapperButtonInline = styled('div', {
  name,
  slot: 'buttonWrapper',
  overridesResolver(props, styles) {
    return [styles.buttonWrapper];
  }
})(({ theme }) => ({
  display: 'flex',
  justifyContent: ' center',
  alignItems: 'center',
  flexFlow: 'column',
  width: 272,
  marginTop: theme.spacing(0.5),
  '& button': {
    width: '100%'
  },
  [theme.breakpoints.down('sm')]: {
    flexFlow: 'column wrap',
    minHeight: 40,
    padding: '4px 0px',
    width: '100%'
  }
}));

const ProfileHeaderBottom = styled('div', {
  name: 'HeaderProfileInPageDetail',
  slot: 'profileHeaderBottom',
  shouldForwardProp: props => props !== 'isSticky',
  overridesResolver(props, styles) {
    return [!props.isSticky && styles.profileHeaderBottom];
  }
})<{ isSticky?: boolean }>(({ theme }) => ({
  backgroundColor: theme.mixins.backgroundColor('paper'),
  borderTop: 'solid 1px',
  borderTopColor: theme.palette.border?.secondary,
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  borderBottomLeftRadius: theme.shape.borderRadius,
  borderBottomRightRadius: theme.shape.borderRadius,
  overflow: 'hidden',
  [theme.breakpoints.down('sm')]: {
    flexWrap: 'wrap',
    flexDirection: 'column',
    borderTop: 'none'
  },
  '& .MuiProfileMenu-tabItem': {
    padding: theme.spacing(2)
  }
}));

const WrapperMenu = styled('div', {
  name: 'HeaderProfileInPageDetail',
  overridesResolver(props, styles) {
    return [styles.wrapperMenu, props.isSticky && styles.wrapperMenuSticky];
  },
  shouldForwardProp: props => props !== 'isSticky' && props !== 'scrollTrigger'
})<{ isSticky?: boolean; scrollTrigger?: boolean }>(
  ({ theme, isSticky, scrollTrigger }) => ({
    display: 'flex',
    flexGrow: 1,
    ...(isSticky && {
      paddingLeft: theme.spacing(2)
    }),
    [theme.breakpoints.down('md')]: {
      paddingLeft: theme.spacing(2)
    },
    [theme.breakpoints.down('sm')]: {
      width: '100%',
      maxWidth: '100%',
      padding: theme.spacing(0, 2),
      borderBottom: 'solid 1px',
      borderBottomColor: theme.palette.border?.secondary,
      ...(scrollTrigger && {
        marginTop: theme.spacing(1)
      })
    }
  })
);

const UserStickyWrapper = styled('div', { name: 'UserStickyWrapper' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    padding: theme.spacing(1, 0)
  })
);

const UserAvatarSticky = styled(Avatar, { name: 'UserAvatarSticky' })(
  ({ theme }) => ({
    cursor: 'pointer',
    width: 48,
    height: 48
  })
);

const UserNameSticky = styled('div', { name: 'UserNameSticky' })(
  ({ theme }) => ({
    cursor: 'pointer',
    fontSize: theme.mixins.pxToRem(18),
    fontWeight: 'bold',
    marginLeft: theme.spacing(1.5),
    WebkitLineClamp: 2,
    display: '-webkit-box',
    padding: '0',
    overflow: 'hidden',
    maxWidth: '100%',
    whiteSpace: 'normal',
    textOverflow: 'ellipsis',
    WebkitBoxOrient: 'vertical',
    color: theme.palette.text.primary
  })
);

const ActionButtons = styled('div', {
  name: 'HeaderProfileInPageDetail',
  slot: 'actionButtons',
  overridesResolver(props, styles) {
    return [styles.actionButtons];
  }
})(({ theme }) => ({
  display: 'flex',
  paddingRight: theme.spacing(2),
  '& button': {
    marginLeft: theme.spacing(1),
    textTransform: 'capitalize',
    fontWeight: 'bold',
    whiteSpace: 'nowrap',
    borderRadius: theme.spacing(0.5),
    fontSize: theme.mixins.pxToRem(13),
    padding: theme.spacing(0.5, 1.25),
    marginBottom: theme.spacing(0.5),
    marginTop: theme.spacing(0.5),
    minWidth: theme.spacing(4),
    height: theme.spacing(4),
    '& .ico': {
      fontSize: theme.mixins.pxToRem(13)
    }
  },
  [theme.breakpoints.down('sm')]: {
    width: '100%',
    alignItems: 'center',
    justifyContent: 'flex-start',
    '& button': {
      margin: theme.spacing(2, 0, 2, 2)
    },
    '& button + button': {
      marginLeft: theme.spacing(1)
    }
  }
}));

const ProfileMenuWrapper = styled('div', {
  name: 'HeaderProfileInPageDetail',
  slot: 'profileMenuWrapper',
  overridesResolver(props, styles) {
    return [styles.profileMenuWrapper];
  }
})(({ theme }) => ({
  flex: 1,
  minWidth: 0
}));

const ActionPage = styled(Box, { name: 'ActionPage' })(({ theme }) => ({
  marginTop: theme.spacing(2)
}));

const Title = styled(Typography, { name: 'Title' })(({ theme }) => ({
  color: theme.palette.text.primary
}));

export default function PageProfileHeaderView({
  item,
  identity,
  profileMenu,
  profileActionMenu,
  blockProps,
  handleAction,
  actions
}: Props) {
  const {
    i18n,
    usePageParams,
    ProfileHeaderCover,
    ProfileHeaderAvatar,
    ItemActionMenu,
    dispatch,
    getAcl,
    getSetting,
    useSession,
    assetUrl,
    useIsMobile,
    useTheme
  } = useGlobal();
  const session = useSession();
  const acl = getAcl();
  const setting = getSetting();
  const { tab = 'home' } = usePageParams();
  const scrollTrigger = useScrollTrigger();
  const isMobile = useIsMobile(true);
  const theme = useTheme();
  const minHeight = theme.appBarMobileConfig?.nav ?? 48;

  const handleControlClick = useCallback(() => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }, []);

  if (!item?.statistic) {
    return <LoadingSkeleton />;
  }

  const {
    cover_photo_id,
    extra,
    full_name,
    id,
    statistic,
    cover_photo_position
  } = item;

  const bgColor = colorHash.hex(shortenFullName(full_name) || '');
  const condition = { item, acl, setting, session };

  const listNoMore = [LIKE, LIKED, INVITE];

  const profileMenuItems = filterShowWhen(profileMenu.items, condition);

  const actionMenuItems = filterShowWhen(profileActionMenu.items, condition);

  const moreItemsAction = actionMenuItems.filter(
    item => !listNoMore.includes(item?.name)
  );

  const actionButtons = actionMenuItems?.filter(item => item?.name === INVITE);

  const likeButton = nth(actionMenuItems, 0);

  const avatar = getImageSrc(item.avatar, '200x200', assetUrl('page.no_image'));
  const cover = getImageSrc(
    item?.cover,
    '1024',
    assetUrl('page.cover_no_image')
  );

  const handleSearch = () => {
    dispatch({ type: 'page/search', payload: { identity } });
  };

  const onEditAvatar = () => {
    dispatch({ type: 'editProfileHeaderAvatar', payload: { identity } });
  };

  const handleShowLoginDialog = () => {
    dispatch({ type: 'user/showDialogLogin' });
  };

  return (
    <Block>
      <BlockContent>
        <Box>
          <Wrapper>
            <Box>
              <ProfileHeaderCover
                identity={identity}
                image={cover}
                imageId={cover_photo_id}
                alt={''}
                left={0}
                top={cover_photo_position || 0}
              />
              <Box>
                <Container maxWidth="md" gutter>
                  <UserContainer>
                    <UserInfoContainer>
                      <UserInfo>
                        <ProfileHeaderAvatar
                          alt={shortenFullName(item.title)}
                          canEdit={extra?.can_upload_avatar}
                          onEdit={onEditAvatar}
                          avatar={avatar}
                          avatarId={item.avatar_id}
                        />
                        <Box width="100%" textAlign="left">
                          <SponsorFlag
                            variant="itemView"
                            value={item.is_sponsor}
                            item={item}
                          />
                          <Title variant="h2">
                            {item.title}
                            {item.is_featured ? (
                              <FeaturedIcon icon="ico-check-circle" />
                            ) : null}
                          </Title>
                          <Summary>
                            {item.summary}
                            {statistic.total_like ? (
                              <>
                                {' '}
                                &middot;{' '}
                                <Link
                                  to={`/page/${item.id}/member?stab=all_members`}
                                >
                                  {i18n.formatMessage(
                                    { id: 'total_like' },
                                    { value: statistic.total_like }
                                  )}
                                </Link>
                              </>
                            ) : null}
                          </Summary>
                        </Box>
                      </UserInfo>
                      <WrapperButtonInline>
                        {likeButton && likeButton.name !== MANAGE ? (
                          <ButtonAction
                            key={likeButton.value}
                            disabled={
                              likeButton.name === LIKED && item?.is_owner
                            }
                            disableElevation
                            variant="contained"
                            autoEnable
                            size="small"
                            startIcon={<LineIcon icon={likeButton.icon} />}
                            color={likeButton.color as any}
                            action={
                              isEmpty(session?.user)
                                ? handleShowLoginDialog
                                : () => handleAction(likeButton.value)
                            }
                          >
                            {likeButton.label}
                          </ButtonAction>
                        ) : null}
                      </WrapperButtonInline>
                    </UserInfoContainer>
                    {item?.is_pending || item?.membership ? (
                      <ActionPage>
                        <InviteCard item={item} actions={actions} />
                        <PendingCard item={item} actions={actions} />
                      </ActionPage>
                    ) : null}
                  </UserContainer>
                </Container>
                <StickyBar
                  sx={isMobile && !scrollTrigger ? { top: minHeight } : ''}
                >
                  {({ sticky }) => (
                    <Container maxWidth="md" gutter>
                      {sticky ? (
                        <ProfileHeaderBottom isSticky>
                          <WrapperMenu isSticky scrollTrigger={scrollTrigger}>
                            <UserStickyWrapper>
                              <UserAvatarSticky
                                src={avatar}
                                children={shortenFullName(item.title)}
                                style={{ backgroundColor: bgColor }}
                                onClick={handleControlClick}
                              />
                              <UserNameSticky onClick={handleControlClick}>
                                {item.title}
                              </UserNameSticky>
                            </UserStickyWrapper>
                          </WrapperMenu>
                          <ActionButtons>
                            {actionButtons.map((btn, index) => (
                              <Button
                                key={btn.label}
                                variant={'outlined'}
                                startIcon={<LineIcon icon={btn.icon} />}
                                color="primary"
                                sx={{ width: '100%' }}
                                onClick={() =>
                                  dispatch({
                                    type: btn.value,
                                    payload: {
                                      identity
                                    }
                                  })
                                }
                              >
                                {btn.label}
                              </Button>
                            ))}
                            <Button
                              variant={'outlined'}
                              color="primary"
                              onClick={handleSearch}
                            >
                              <LineIcon icon={'ico-search-o'} />
                            </Button>
                            <ItemActionMenu
                              id="actionMenu"
                              label="ActionMenu"
                              handleAction={handleAction}
                              items={moreItemsAction}
                              control={
                                <Button
                                  variant="outlined"
                                  color="primary"
                                  size="large"
                                >
                                  <LineIcon icon={'ico-dottedmore-o'} />
                                </Button>
                              }
                            />
                          </ActionButtons>
                        </ProfileHeaderBottom>
                      ) : (
                        <ProfileHeaderBottom>
                          <WrapperMenu scrollTrigger={scrollTrigger}>
                            <ProfileMenuWrapper>
                              <ProfileMenu
                                items={profileMenuItems}
                                activeTab={tab}
                                prefix={item?.link || `/page/${id}`}
                                maxDisplayTab={3}
                              />
                            </ProfileMenuWrapper>
                          </WrapperMenu>
                          <ActionButtons>
                            {actionButtons.map((btn, index) => (
                              <Button
                                key={btn.label}
                                variant={'outlined'}
                                startIcon={<LineIcon icon={btn.icon} />}
                                color="primary"
                                sx={{ width: '100%' }}
                                onClick={() =>
                                  dispatch({
                                    type: btn.value,
                                    payload: {
                                      identity
                                    }
                                  })
                                }
                              >
                                {btn.label}
                              </Button>
                            ))}
                            <Button
                              variant={'outlined'}
                              color="primary"
                              onClick={handleSearch}
                            >
                              <LineIcon icon={'ico-search-o'} />
                            </Button>
                            <ItemActionMenu
                              id="actionMenu"
                              label="ActionMenu"
                              handleAction={handleAction}
                              items={moreItemsAction}
                              control={
                                <Button
                                  variant="outlined"
                                  color="primary"
                                  size="large"
                                >
                                  <LineIcon icon={'ico-dottedmore-o'} />
                                </Button>
                              }
                            />
                          </ActionButtons>
                        </ProfileHeaderBottom>
                      )}
                    </Container>
                  )}
                </StickyBar>
              </Box>
            </Box>
          </Wrapper>
        </Box>
      </BlockContent>
    </Block>
  );
}

PageProfileHeaderView.LoadingSkeleton = LoadingSkeleton;
