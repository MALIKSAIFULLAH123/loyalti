/* eslint-disable @typescript-eslint/no-unused-vars */
import { BlockViewProps, MenuShape, useGlobal } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { Block, BlockContent } from '@metafox/layout';
import {
  Container,
  LineIcon,
  ProfileMenu,
  StickyBar,
  UserAvatar,
  TruncateText,
  ButtonAction
} from '@metafox/ui';
import { filterShowWhen, getImageSrc, shortenFullName } from '@metafox/utils';
import {
  Box,
  Button,
  IconButton,
  styled,
  Tooltip,
  useScrollTrigger
} from '@mui/material';
import React, { useCallback } from 'react';
import { UserItemActions, UserItemShape } from '../../types';
import ActivityPointSummary from './ActivityPointSummary';
import { LoadingSkeleton } from './LoadingSkeleton';
import InviteCard from './InviteCard';
import PendingUser from './PendingUser';

const name = 'HeaderProfileInPageDetail';

const ItemSummary = styled('div', { name, slot: 'ItemSummary' })(
  ({ theme }) => ({
    color: theme.palette.text.secondary,
    fontSize: theme.mixins.pxToRem(15),
    paddingTop: theme.spacing(1),
    paddingRight: theme.spacing(2),
    '& p': {
      margin: 0
    },
    [theme.breakpoints.down('sm')]: {
      paddingRight: 0
    }
  })
);

const FeaturedIcon = styled(LineIcon, { name, slot: 'FeaturedIcon' })(
  ({ theme }) => ({
    color: theme.palette.primary.main,
    marginLeft: theme.spacing(2)
  })
);
const UserStickyWrapper = styled('div', { name, slot: 'UserStickyWrapper' })(
  ({ theme }) => ({
    position: 'relative',
    left: 16,
    transition: 'all .2s',
    display: 'flex',
    justifyContent: 'center',
    '& a': {
      borderWidth: 'thin',
      borderStyle: 'solid',
      borderColor: theme.palette.border.secondary
    }
  })
);

const ProfileMenuStyled = styled('div', {
  name,
  slot: 'ProfileMenuStyled',
  shouldForwardProp: prop => prop !== 'sticky'
})<{ sticky?: any }>(({ theme, sticky }) => ({
  flex: 1,
  minWidth: 0,
  transition: 'all .2s',
  ...(sticky && {
    marginLeft: theme.spacing(2.5),
    transition: 'all .2s'
  })
}));

const Wrapper = styled('div', { name, slot: 'Wrapper' })(({ theme }) => ({
  display: 'block'
}));

const UserInfoContainer = styled(Box, {
  name,
  slot: 'userInfo',
  overridesResolver(props, styles) {
    return [styles.userInfo];
  }
})(({ theme }) => ({
  backgroundColor: theme.mixins.backgroundColor('paper'),
  [theme.breakpoints.down('sm')]: {
    flexFlow: 'column',
    width: '100%'
  }
}));

const UserInfoWrapper = styled(Box, {
  name,
  slot: 'userAction',
  overridesResolver(props, styles) {
    return [styles.userAction];
  }
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-start',
  [theme.breakpoints.down('sm')]: {
    flexFlow: 'column',
    width: '100%'
  }
}));

const UserInfo = styled(Box, {
  name,
  slot: 'nameUser',
  overridesResolver(props, styles) {
    return [styles.nameUser];
  }
})(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  [theme.breakpoints.down('sm')]: {
    flexFlow: 'column',
    width: '100%',
    marginBottom: theme.spacing(2)
  }
}));

const Title = styled('h1', { name, slot: 'Title' })(({ theme }) => ({
  fontWeight: 'bold',
  fontSize: theme.mixins.pxToRem(24),
  color: theme.palette.text.primary,
  margin: 0,
  padding: 0
}));

const WrapperButtonInline = styled('div', {
  name,
  slot: 'wrapperButtonInline',
  overridesResolver(props, styles) {
    return [styles.wrapperButtonInline];
  }
})(({ theme }) => ({
  display: 'flex',
  justifyContent: ' center',
  alignItems: 'stretch',
  '& button': {
    marginLeft: theme.spacing(1),
    textTransform: 'capitalize',
    fontWeight: 'bold',
    whiteSpace: 'nowrap',
    borderRadius: theme.spacing(0.5),
    fontSize: theme.mixins.pxToRem(13),
    padding: theme.spacing(0.5, 1.25),
    marginBottom: theme.spacing(1),
    minWidth: theme.spacing(4),
    height: theme.spacing(4),
    '& .ico': {
      fontSize: theme.mixins.pxToRem(13)
    }
  },
  [theme.breakpoints.down('sm')]: {
    flexFlow: 'row wrap',
    padding: theme.spacing(0.5, 0),
    justifyContent: 'flex-start',
    '& button': {
      marginLeft: 0,
      marginRight: theme.spacing(1)
    }
  }
}));

const ProfileActionMenu = styled(Button, { name, slot: 'ProfileActionMenu' })(
  ({ theme }) => ({
    padding: `${theme.spacing(0.5, 1.25)} !important`,
    minWidth: theme.spacing(4),
    height: theme.spacing(4)
  })
);

const ProfileHeaderBottom = styled('div', {
  name,
  slot: 'profileHeaderBottom',
  overridesResolver(props, styles) {
    return [styles.profileHeaderBottom];
  }
})(({ theme }) => ({
  backgroundColor: theme.mixins.backgroundColor('paper'),
  display: 'flex',
  borderTop: 'solid 1px',
  borderTopColor: theme.palette.border?.secondary,
  borderBottomLeftRadius: theme.shape.borderRadius,
  borderBottomRightRadius: theme.shape.borderRadius,
  '& .MuiProfileMenu-tabItem': {
    padding: theme.spacing(2)
  },
  [theme.breakpoints.down('md')]: {
    paddingTop: theme.spacing(1)
  }
}));

const StyledNotice = styled(Box)(({ theme }) => ({
  '> div': {
    marginTop: theme.spacing(2)
  }
}));

const ViewProfile = styled(Box, {
  name,
  slot: 'viewProfile',
  overridesResolver(props, styles) {
    return [styles.viewProfile];
  }
})(({ theme }) => ({}));

export interface Props extends BlockViewProps {
  item: UserItemShape;
  identity: string;
  profileMenu: MenuShape;
  profileActionMenu: MenuShape;
  handleAction: any;
  state: any;
  actions: UserItemActions;
}

const UserProfileHeaderView = ({
  item,
  identity,
  profileMenu,
  profileActionMenu,
  blockProps,
  handleAction,
  state,
  actions
}: Props) => {
  const {
    ItemActionMenu,
    usePageParams,
    ProfileHeaderCover,
    ProfileHeaderAvatar,
    useSession,
    getAcl,
    getSetting,
    assetUrl,
    i18n,
    useIsMobile,
    navigate,
    useTheme
  } = useGlobal();
  const isMobile = useIsMobile(true);
  const { id: user_id, tab = 'home' } = usePageParams();
  const session = useSession();
  const { user: userAuth } = session || {};
  const acl = getAcl();
  const setting = getSetting();
  const scrollTrigger = useScrollTrigger();
  const theme = useTheme();
  const minHeight = theme.appBarMobileConfig?.nav ?? 48;

  const handleControlClick = useCallback(() => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }, []);

  if (!item?.profile_menu_settings || !item?.profile_settings) {
    return <LoadingSkeleton />;
  }

  const { cover_photo_id, extra, cover_photo_position, profile_settings } =
    item;

  const avatar = getImageSrc(item.avatar, '200x200', assetUrl('user.no_image'));
  const cover = getImageSrc(
    item?.cover,
    '1024',
    assetUrl('user.cover_no_image')
  );
  const condition = { item, acl, setting, session };
  const profileMenuItems = filterShowWhen(profileMenu.items, condition);
  const actionMenuItemsFull = filterShowWhen(
    profileActionMenu.items,
    condition
  );
  const actionButtons = actionMenuItemsFull.slice(0, 2);
  const actionMenuItems = actionMenuItemsFull.slice(2);

  return (
    <Block>
      <BlockContent>
        <Box data-testid="userProfileHeader">
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
                  <UserInfoContainer p={2}>
                    <UserInfoWrapper>
                      <UserInfo display="flex" alignItems="flex-start">
                        <ProfileHeaderAvatar
                          alt={shortenFullName(item.full_name)}
                          canEdit={extra?.can_upload_avatar}
                          onEdit={actions.editProfileHeaderAvatar}
                          avatar={avatar}
                          avatarId={item.avatar_id}
                          identity={identity}
                          showLiveStream
                        />
                        <ViewProfile data-testid="viewProfileInfo">
                          <Title data-testid="viewProfileTitle">
                            {item.full_name}
                            {item.is_featured ? (
                              <FeaturedIcon icon="ico-check-circle" />
                            ) : null}
                          </Title>
                          {!profile_settings?.profile_view_profile &&
                          !item.bio ? null : (
                            <ItemSummary>
                              <TruncateText lines={2}>
                                <HtmlViewer html={item.bio} simpleTransform />
                              </TruncateText>
                            </ItemSummary>
                          )}
                        </ViewProfile>
                      </UserInfo>
                      <Box
                        display="flex"
                        flexDirection="column"
                        alignItems={{ sm: 'flex-end', xs: 'flex-start' }}
                      >
                        <WrapperButtonInline>
                          {actionButtons.map((btn, index) => (
                            <ButtonAction
                              key={btn.label}
                              data-testid={btn?.name}
                              variant={
                                btn?.variant ||
                                (0 === index ? 'contained' : 'outlined')
                              }
                              startIcon={
                                btn?.icon && <LineIcon icon={btn.icon} />
                              }
                              autoEnable
                              action={onFinally =>
                                handleAction(
                                  btn.value,
                                  { isMobile },
                                  { onSuccess: onFinally, onFinally }
                                )
                              }
                              color={(btn.color || 'primary') as any}
                              size="small"
                            >
                              {btn.label}
                            </ButtonAction>
                          ))}
                          {userAuth?.id !== parseInt(user_id) &&
                          actionMenuItems?.length ? (
                            <ItemActionMenu
                              id="actionMenu"
                              label="ActionMenu"
                              handleAction={handleAction}
                              items={actionMenuItems}
                              control={
                                <Tooltip
                                  title={i18n.formatMessage({
                                    id: 'more_options'
                                  })}
                                >
                                  <ProfileActionMenu
                                    variant="outlined"
                                    color="primary"
                                    size="small"
                                  >
                                    <LineIcon icon={'ico-dottedmore-o'} />
                                  </ProfileActionMenu>
                                </Tooltip>
                              }
                            />
                          ) : null}
                        </WrapperButtonInline>
                        {extra?.can_view_profile_activity_point ? (
                          <ActivityPointSummary
                            isOwner={userAuth?.id === parseInt(user_id)}
                          />
                        ) : null}
                      </Box>
                    </UserInfoWrapper>
                    <StyledNotice>
                      {item?.friendship === 2 ? (
                        <InviteCard item={item} actions={actions} />
                      ) : null}
                      <PendingUser item={item} />
                    </StyledNotice>
                  </UserInfoContainer>
                </Container>
              </Box>
              {profile_settings?.profile_view_profile && (
                <StickyBar
                  sx={isMobile && !scrollTrigger ? { top: minHeight } : ''}
                >
                  {({ sticky }) => (
                    <Container maxWidth="md" gutter>
                      <ProfileHeaderBottom>
                        {sticky ? (
                          <UserStickyWrapper onClick={handleControlClick}>
                            <UserAvatar
                              user={item}
                              size={48}
                              noStory
                              showStatus={false}
                            />
                          </UserStickyWrapper>
                        ) : null}

                        <ProfileMenuStyled sticky={sticky}>
                          <ProfileMenu
                            items={profileMenuItems}
                            activeTab={tab}
                            maxDisplayTab={5}
                            prefix={item?.link}
                          />
                        </ProfileMenuStyled>
                      </ProfileHeaderBottom>
                    </Container>
                  )}
                </StickyBar>
              )}
            </Box>
          </Wrapper>
        </Box>
      </BlockContent>
    </Block>
  );
};

UserProfileHeaderView.displayName = 'UserProfileHeaderView';

export default UserProfileHeaderView;
