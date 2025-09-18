import {
  BlockViewProps,
  Link,
  MenuShape,
  useGlobal,
  useSession,
  GlobalState,
  getResourceMenuSelector
} from '@metafox/framework';
import {
  MEMBERSHIP,
  MEMBERSHIP_CONFIRM_AWAIT,
  NOT_MEMBERSHIP
} from '@metafox/group';
import { Block, BlockContent } from '@metafox/layout';
import {
  Container,
  LineIcon,
  ProfileMenu,
  SponsorFlag,
  StickyBar,
  DotSeparator
} from '@metafox/ui';
import { UserItemShape } from '@metafox/user';
import { filterShowWhen, getImageSrc, withDisabledWhen } from '@metafox/utils';
import {
  Avatar,
  Box,
  Button,
  styled,
  Typography,
  useScrollTrigger
} from '@mui/material';
import { isEmpty, nth } from 'lodash';
import React, { useCallback } from 'react';
import { GroupItemShape } from '../../types';
import InviteCard from './InviteCard';
import { LoadingSkeleton } from './LoadingSkeleton';
import MutedCard from './MutedCard';
import { useSelector } from 'react-redux';

export interface Props extends BlockViewProps {
  item: GroupItemShape;
  user: UserItemShape;
  identity: string;
  profileMenu: MenuShape;
  profileActionMenu: MenuShape;
  handleAction: any;
  actions: any;
}

const name = 'HeaderProfileInPageDetail';

const StyledReactButtonWrapper = styled(Box, {
  name,
  slot: 'buttonWrapper',
  overridesResolver(props, styles) {
    return [styles.buttonWrapper];
  }
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'end',
  alignItems: 'center',
  marginLeft: theme.spacing(4),
  '& button': {
    fontWeight: 'bold',
    boxShadow: 'none',
    width: 'max-content',
    marginLeft: theme.spacing(1),
    '& .ico': {
      fontSize: theme.mixins.pxToRem(15)
    }
  },
  [theme.breakpoints.down('sm')]: {
    justifyContent: 'center',
    flexFlow: 'row wrap',
    minHeight: 40,
    padding: '4px 0',
    marginLeft: '0',
    '& button': {
      minWidth: '120px'
    }
  }
}));

const StyledSummary = styled(Typography)(({ theme }) => ({
  fontWeight: 'normal',
  paddingTop: theme.spacing(1)
}));

const StyledGroupInfoWrapper = styled(Box, {
  name,
  slot: 'userInfo',
  overridesResolver(props, styles) {
    return [styles.userInfo];
  }
})(({ theme }) => ({
  backgroundColor: theme.mixins.backgroundColor('paper'),
  padding: theme.spacing(2, 2, 2)
}));

const StyledGroupInfo = styled(Box, {
  name,
  slot: 'infoNameActionMenu',
  overridesResolver(props, styles) {
    return [styles.infoNameActionMenu];
  }
})(({ theme }) => ({
  paddingTop: theme.spacing(0.5),
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  [theme.breakpoints.down('sm')]: {
    flexFlow: 'column',
    width: '100%',
    alignItems: 'center'
  }
}));

const StyledUserInfo = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  [theme.breakpoints.down('sm')]: {
    flexFlow: 'column',
    width: '100%',
    alignItems: 'flex-start',
    marginBottom: theme.spacing(2)
  }
}));

const StyledNotice = styled(Box)(({ theme }) => ({
  '> div': {
    marginTop: theme.spacing(2)
  }
}));

const FeaturedIcon = styled(LineIcon, { name: 'FeaturedIcon' })(
  ({ theme }) => ({
    color: theme.palette.primary.main,
    marginLeft: theme.spacing(2),
    fontSize: 24
  })
);

const ButtonJoin = styled(Button, { name: 'ButtonJoin' })(({ theme }) => ({
  '&.Mui-disabled': {
    ...(theme.palette.mode === 'dark' && {
      color: `${theme.palette.text.hint} !important`
    })
  }
}));

const ProfileHeaderBottom = styled('div', {
  name,
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
    borderTop: 'none',
    height: 'auto'
  },
  '& .MuiProfileMenu-tabItem': {
    padding: theme.spacing(2)
  }
}));

const WrapperMenu = styled('div', {
  name,
  overridesResolver(props, styles) {
    return [styles.wrapperMenu, props.isSticky && styles.wrapperMenuSticky];
  },
  shouldForwardProp: props => props !== 'isSticky' && props !== 'scrollTrigger'
})<{ isSticky?: boolean; scrollTrigger?: boolean }>(
  ({ theme, isSticky, scrollTrigger }) => ({
    display: 'flex',
    flexGrow: 1,
    maxWidth: '100%',
    ...(isSticky && {
      paddingLeft: theme.spacing(2)
    }),
    [theme.breakpoints.down('md')]: {
      paddingLeft: theme.spacing(2),
      ...(scrollTrigger && {
        marginTop: theme.spacing(1)
      })
    },
    [theme.breakpoints.down('sm')]: {
      width: '100%',
      maxWidth: '100%',
      padding: theme.spacing(0, 2),
      borderBottom: 'solid 1px',
      borderBottomColor: theme.palette.border?.secondary
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
    height: 48,
    borderRadius: theme.shape.borderRadius
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
    WebkitBoxOrient: 'vertical'
  })
);

const ActionButtons = styled('div', {
  name,
  slot: 'actionButtons',
  overridesResolver(props, styles) {
    return [styles.actionButtons];
  }
})(({ theme }) => ({
  display: 'flex',
  padding: theme.spacing(0, 2),
  '& button': {
    marginLeft: theme.spacing(1),
    textTransform: 'capitalize',
    fontWeight: 'bold',
    whiteSpace: 'nowrap',
    borderRadius: theme.spacing(0.5),
    fontSize: theme.mixins.pxToRem(13),
    padding: theme.spacing(0.5, 1.25),
    marginTop: theme.spacing(0.5),
    marginBottom: theme.spacing(0.5),
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

const ProfileMenuWrapper = styled('div', { name: 'ProfileMenuWrapper' })(
  ({ theme }) => ({
    flex: 1,
    minWidth: 0
  })
);

export default function GroupProfileHeaderView({
  item,
  user,
  identity,
  profileMenu,
  profileActionMenu,
  handleAction,
  actions,
  state
}: Props) {
  const {
    i18n,
    usePageParams,
    ProfileHeaderCover,
    ItemActionMenu,
    dispatch,
    jsxBackend,
    getAcl,
    getSetting,
    assetUrl,
    useIsMobile,
    useTheme
  } = useGlobal();
  const acl = getAcl();
  const setting = getSetting();
  const { tab = 'home' } = usePageParams();
  const { user: authorUser } = useSession();
  const [isLoadingSubmit, setIsLoadingSubmit] = React.useState(false);
  const PendingPreview = jsxBackend.get('group.itemView.pendingReviewCard');
  const PendingGroupPreview = jsxBackend.get(
    'group.itemView.pendingReviewGroupCard'
  );
  const inviteMenu = useSelector((state: GlobalState) =>
    getResourceMenuSelector(
      state,
      item?.module_name,
      item?.resource_name,
      'itemActionInviteMenu'
    )
  );
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

  if (!item) {
    return <LoadingSkeleton />;
  }

  const {
    cover_photo_id,
    extra,
    title,
    id,
    statistic,
    cover_photo_position,
    membership
  } = item;
  const avatar =
    getImageSrc(item.cover, '240') || getImageSrc(item.image, '240');

  const condition = { item, acl, setting };

  const conditionCustom = {
    item: { ...item, is_admin: user?.id === authorUser?.id },
    acl,
    setting
  };

  const profileMenuItems = filterShowWhen(profileMenu.items, condition);
  const actionMenuItems = withDisabledWhen(
    filterShowWhen(profileActionMenu.items, conditionCustom),
    conditionCustom
  );
  const inviteMenuItems = filterShowWhen(inviteMenu?.items, condition);

  const itemButtonMembership =
    nth(actionMenuItems, 0)?.name !== 'manage'
      ? nth(actionMenuItems, 0)
      : undefined;

  const membershipStatus = [
    MEMBERSHIP,
    MEMBERSHIP_CONFIRM_AWAIT,
    NOT_MEMBERSHIP
  ].includes(membership)
    ? itemButtonMembership
    : undefined;

  const actionMoreItems = membershipStatus
    ? actionMenuItems.slice(1)
    : actionMenuItems;

  const cover = getImageSrc(
    item.cover,
    '1024',
    assetUrl('group.cover_no_image')
  );

  const handleSearch = () => {
    dispatch({ type: 'group/search', payload: { identity } });
  };

  const handleShowLoginDialog = () => {
    dispatch({
      type: 'user/showDialogLogin',
      meta: { onSuccess: () => setIsLoadingSubmit(false) }
    });
  };

  const inviteFriends = () => {
    dispatch({ type: 'group/inviteFriends', payload: { identity } });
  };

  const onAction = value => {
    setIsLoadingSubmit(true);

    if (isEmpty(authorUser)) {
      handleShowLoginDialog();
    } else {
      handleAction(membershipStatus?.value, {
        onSuccess: () => setIsLoadingSubmit(false)
      });
    }
  };

  return (
    <Block>
      <BlockContent>
        <Box>
          <ProfileHeaderCover
            identity={identity}
            image={cover}
            imageId={cover_photo_id}
            alt={title}
            left={0}
            top={cover_photo_position || 0}
          />
          <Box>
            <Container maxWidth="md" gutter>
              <StyledGroupInfoWrapper>
                <SponsorFlag
                  variant="itemView"
                  value={item.is_sponsor}
                  item={item}
                />
                <StyledGroupInfo>
                  <StyledUserInfo>
                    <Box>
                      <Typography variant="h2">
                        {item.title}
                        {item.is_featured ? (
                          <FeaturedIcon icon="ico-check-circle" />
                        ) : null}
                      </Typography>
                      <StyledSummary variant="subtitle1" color="text.secondary">
                        <DotSeparator>
                          {extra?.can_view_privacy ? (
                            <span>{item.reg_name}</span>
                          ) : null}
                          {statistic?.total_member &&
                          extra?.can_view_members ? (
                            <Link
                              to={`/group/${item.id}/member?stab=all_members`}
                            >
                              {i18n.formatMessage(
                                { id: 'total_member' },
                                { value: statistic.total_member }
                              )}
                            </Link>
                          ) : null}
                          {statistic?.total_pending_requests &&
                          extra?.can_view_members &&
                          extra?.can_manage_pending_request_tab ? (
                            <Link
                              to={`/group/${item.id}/member?stab=pending_requests`}
                            >
                              {i18n.formatMessage(
                                { id: 'total_pending_requests' },
                                { value: statistic.total_pending_requests }
                              )}
                            </Link>
                          ) : null}
                        </DotSeparator>
                      </StyledSummary>
                    </Box>
                  </StyledUserInfo>
                  <StyledReactButtonWrapper>
                    {membershipStatus && (
                      <ButtonJoin
                        size="small"
                        variant={membershipStatus?.variant}
                        disabled={membershipStatus?.disabled || isLoadingSubmit}
                        startIcon={<LineIcon icon={membershipStatus?.icon} />}
                        color="primary"
                        onClick={() => onAction(membershipStatus.value)}
                      >
                        {membershipStatus?.label}
                      </ButtonJoin>
                    )}
                    {inviteMenuItems.length === 1 ? (
                      <Button
                        onClick={inviteFriends}
                        size="small"
                        variant="outlined"
                        startIcon={
                          <LineIcon
                            sx={{ marginLeft: '0 !important' }}
                            icon={'ico-envelope'}
                          />
                        }
                      >
                        <span>
                          {i18n.formatMessage({ id: 'invite_friends' })}
                        </span>
                      </Button>
                    ) : null}
                    {inviteMenuItems.length > 1 ? (
                      <ItemActionMenu
                        items={inviteMenuItems}
                        state={state}
                        handleAction={handleAction}
                        control={
                          <Button
                            size="small"
                            variant="outlined"
                            startIcon={
                              <LineIcon
                                sx={{ marginLeft: '0 !important' }}
                                icon={'ico-envelope'}
                              />
                            }
                          >
                            <span>{i18n.formatMessage({ id: 'invite' })}</span>
                          </Button>
                        }
                      />
                    ) : null}
                  </StyledReactButtonWrapper>
                </StyledGroupInfo>
                <StyledNotice>
                  <InviteCard item={item} actions={actions} />
                  <PendingPreview item={item} actions={actions} />
                  <PendingGroupPreview item={item} actions={actions} />
                  <MutedCard item={item} />
                </StyledNotice>
              </StyledGroupInfoWrapper>
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
                            onClick={handleControlClick}
                          />
                          <UserNameSticky onClick={handleControlClick}>
                            {item.title}
                          </UserNameSticky>
                        </UserStickyWrapper>
                      </WrapperMenu>
                      <ActionButtons>
                        {item.reg_method === 0 ||
                        membershipStatus?.name === 'joined' ? (
                          <Button
                            variant={'outlined'}
                            color="primary"
                            onClick={handleSearch}
                            sx={{ display: 'none' }}
                          >
                            <LineIcon icon={'ico-search-o'} />
                          </Button>
                        ) : null}
                        {inviteMenuItems.length === 1 ? (
                          <Button
                            onClick={inviteFriends}
                            size="small"
                            variant="outlined"
                            sx={{ width: '100%' }}
                            startIcon={
                              <LineIcon
                                sx={{ marginLeft: '0 !important' }}
                                icon={'ico-envelope'}
                              />
                            }
                          >
                            <span>
                              {i18n.formatMessage({ id: 'invite_friends' })}
                            </span>
                          </Button>
                        ) : null}
                        {inviteMenuItems.length > 1 ? (
                          <ItemActionMenu
                            items={inviteMenuItems}
                            state={state}
                            handleAction={handleAction}
                            control={
                              <Button
                                size="small"
                                variant="outlined"
                                startIcon={
                                  <LineIcon
                                    sx={{ marginLeft: '0 !important' }}
                                    icon={'ico-envelope'}
                                  />
                                }
                              >
                                <span>
                                  {i18n.formatMessage({ id: 'invite' })}
                                </span>
                              </Button>
                            }
                          />
                        ) : null}
                        {item.reg_method === 0 ||
                        membershipStatus?.name === 'joined' ? (
                          <Button
                            variant={'outlined'}
                            color="primary"
                            onClick={handleSearch}
                          >
                            <LineIcon icon={'ico-search-o'} />
                          </Button>
                        ) : null}
                        <ItemActionMenu
                          id="actionMenu"
                          label="ActionMenu"
                          handleAction={handleAction}
                          items={actionMoreItems}
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
                            prefix={item?.link || `/group/${id}`}
                            maxDisplayTab={5}
                          />
                        </ProfileMenuWrapper>
                      </WrapperMenu>
                      <ActionButtons>
                        {item.reg_method === 0 ||
                        membershipStatus?.name === 'joined' ? (
                          <Button
                            variant={'outlined'}
                            color="primary"
                            onClick={handleSearch}
                          >
                            <LineIcon icon={'ico-search-o'} />
                          </Button>
                        ) : null}
                        <ItemActionMenu
                          id="actionMenu"
                          label="ActionMenu"
                          handleAction={handleAction}
                          items={actionMoreItems}
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
      </BlockContent>
    </Block>
  );
}

GroupProfileHeaderView.LoadingSkeleton = LoadingSkeleton;
