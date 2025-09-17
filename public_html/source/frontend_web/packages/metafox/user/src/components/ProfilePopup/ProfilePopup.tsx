import { Link, useGlobal, IS_ADMINCP } from '@metafox/framework';
import { FRIENDSHIP_IS_FRIEND } from '@metafox/friend';
import HtmlViewer from '@metafox/html-viewer';
import {
  ButtonList,
  HtmlViewerWrapper,
  ItemSummary,
  ItemText,
  LineIcon,
  MenuItemShape,
  TruncateText,
  UserAvatar
} from '@metafox/ui';
import { getMenuAction } from '@metafox/user/utils';
import { dateDiffInDays, filterShowWhen } from '@metafox/utils';
import { Button, Paper, Popper, styled, Box, Typography } from '@mui/material';
import * as React from 'react';
import useStyles from './ProfilePopup.styles';

const AvatarWrapper = styled('div', { name: 'AvatarWrapper' })(({ theme }) => ({
  marginRight: theme.spacing(1.5)
}));

export default function ProfilePopup({
  open,
  anchorEl,
  item,
  actions,
  loaded,
  identity,
  state,
  handleAction
}) {
  const {
    i18n,
    ItemActionMenu,
    useSession,
    popoverBackend,
    getAcl,
    getSetting,
    useIsMobile,
    moment
  } = useGlobal();
  const isMobile = useIsMobile(true);
  const classes = useStyles();

  const acl = getAcl();
  const setting = getSetting();

  const session = useSession();
  const birthday_phrase = React.useMemo(() => {
    if (item?.friendship !== FRIENDSHIP_IS_FRIEND || !item?.birthday)
      return null;

    const duration = dateDiffInDays(
      moment(item.birthday, item.birthday_format)
    );
    const dayUpcoming = +getSetting('friend.days_to_check_for_birthday');

    if (duration === 0 || duration === 1)
      return i18n.formatMessage(
        { id: 'value_is_gender_birthday' },
        {
          value: duration,
          gender: item?.gender?.possessive_gender
        }
      );

    if (duration <= dayUpcoming && duration > 0)
      return i18n.formatMessage(
        { id: 'birthday_is_in_value_days' },
        {
          value: duration
        }
      );

    return null;
  }, [item?.id]);
  const { loggedIn, userAuth } = session;
  const menus = getMenuAction('profilePopoverMenu');
  const [isHovering, setIsHovering] = React.useState(false);

  if (!loggedIn || !item) return null;

  const { statistic, profile_settings } = item;

  // if (profile_settings?.profile_view_profile === false) return;

  const friendshipCanAddFriend = 0 === item.friendship;
  const friendshipIsFriend = 1 === item.friendship;
  const friendshipConfirmAwait = 2 === item.friendship;
  const friendshipRequesting = 3 === item.friendship;
  const friendshipIsOwner = 5 === item.friendship;
  const to = `/${item.user_name}`;
  const total_mutual =
    profile_settings?.profile_view_profile && (statistic?.total_mutual || 0);
  const total_friend = statistic?.total_friend || 0;

  const condition = {
    item,
    acl,
    setting,
    isAuth: userAuth?.id === item?.id,
    session
  };
  const actionMenuItemsFull = filterShowWhen(menus, condition);

  const menuIsFriend: MenuItemShape[] = actionMenuItemsFull.filter(
    item => item.name !== 'delete'
  );

  const menuRequesting: MenuItemShape[] = actionMenuItemsFull.filter(
    item => item.name !== 'cancel_request'
  );

  return (
    <Popper
      open={open}
      style={{ zIndex: 1300 }}
      anchorEl={anchorEl}
      onMouseEnter={popoverBackend.onEnterContent}
      onMouseLeave={popoverBackend.onLeaveContent}
      variant="hidden-outview"
    >
      <Paper className={classes.paper}>
        <Box mb={0} className={classes.header}>
          <AvatarWrapper>
            <UserAvatar
              to={to}
              user={item}
              size={80}
              className={classes.avatar}
              hoverCard={false}
            />
          </AvatarWrapper>
          <ItemText>
            <div className={classes.profileLink}>
              <Link to={to}>{item.full_name}</Link>
            </div>
            {birthday_phrase && (
              <Box>
                <ItemSummary>
                  <Typography variant="body2" color="text.secondary">
                    <LineIcon icon="ico-birthday-cake-alt" sx={{ mr: 1 }} />
                    {birthday_phrase}
                  </Typography>
                </ItemSummary>
              </Box>
            )}
          </ItemText>
        </Box>
        {profile_settings?.profile_view_profile && (
          <>
            {item.about_me ? (
              <Box mt={2}>
                <TruncateText variant={'body1'} lines={3}>
                  <HtmlViewerWrapper mt={0}>
                    <HtmlViewer html={item.about_me} simpleTransform />
                  </HtmlViewerWrapper>
                </TruncateText>
              </Box>
            ) : null}
            {item.address ? (
              <Box mt={2}>
                <LineIcon icon="ico-globe-o" /> {item.address}
              </Box>
            ) : null}
            {!friendshipIsOwner ? (
              <div className={classes.friends}>
                <div>
                  {profile_settings?.friend_view_friend && (
                    <span>
                      {i18n.formatMessage(
                        { id: 'total_friend' },
                        { value: total_friend }
                      )}
                    </span>
                  )}
                  {total_mutual ? (
                    <span role="button" onClick={actions.presentMutualFriends}>
                      <span children=" (" />
                      {i18n.formatMessage(
                        { id: 'total_mutual' },
                        { value: total_mutual }
                      )}
                      <span children=")" />
                    </span>
                  ) : null}
                </div>
              </div>
            ) : null}
          </>
        )}
        {!IS_ADMINCP && loggedIn && friendshipIsFriend && (
          <ButtonList variant="fillWidth" spacing="medium">
            <Button
              sx={{ flex: 1 }}
              size="medium"
              variant="outlined"
              color="primary"
              startIcon={
                <LineIcon
                  icon={isHovering ? 'ico-user3-minus-o' : 'ico-user3-check-o'}
                />
              }
              onClick={actions.unfriend}
              onMouseOver={() => setIsHovering(true)}
              onMouseOut={() => setIsHovering(false)}
            >
              {i18n.formatMessage({ id: isHovering ? 'unfriend' : 'friend' })}
            </Button>
            {item.extra?.can_message && (
              <Button
                data-testid="itemActionMessage"
                sx={{ flex: 1 }}
                size="medium"
                variant="contained"
                color="primary"
                startIcon={<LineIcon icon={'ico-comment-o'} />}
                onClick={() => actions.chatWithFriend(isMobile)}
              >
                {i18n.formatMessage({ id: 'message' })}
              </Button>
            )}
            <ItemActionMenu
              items={menuIsFriend}
              menuName="profilePopoverMenu"
              identity={identity}
              state={state}
              handleAction={handleAction}
              className={classes.actionsDropdown}
              size="medium"
              variant="outlined-square"
              color="primary"
              icon="ico-dottedmore-o"
              zIndex={1300}
              tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
            />
          </ButtonList>
        )}
        {!IS_ADMINCP && loggedIn && friendshipRequesting && (
          <ButtonList variant="fillWidth" spacing="medium">
            <Button
              size="medium"
              variant="outlined"
              color="primary"
              startIcon={<LineIcon icon={'ico-user2-del-o'} />}
              onClick={actions.cancelRequest}
            >
              {i18n.formatMessage({ id: 'cancel_request' })}
            </Button>
            {item.extra?.can_message && (
              <Button
                data-testid="itemActionMessage"
                size="medium"
                variant="contained"
                color="primary"
                startIcon={<LineIcon icon={'ico-comment-o'} />}
                onClick={() => actions.chatWithFriend(isMobile)}
              >
                {i18n.formatMessage({ id: 'message' })}
              </Button>
            )}

            <ItemActionMenu
              items={menuRequesting}
              identity={identity}
              state={state}
              handleAction={handleAction}
              className={classes.actionsDropdown}
              size="medium"
              variant="outlined-square"
              color="primary"
              icon="ico-dottedmore-o"
              zIndex={1300}
              tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
            />
          </ButtonList>
        )}
        {!IS_ADMINCP && loggedIn && friendshipCanAddFriend && (
          <ButtonList variant="fillWidth" spacing="medium">
            <Button
              className={classes.buttonDisplay}
              color="primary"
              variant="outlined"
              size="medium"
              startIcon={<LineIcon icon={'ico-plus'} />}
              onClick={actions.addFriend}
            >
              {i18n.formatMessage({ id: 'add_as_friend' })}
            </Button>
            {item.extra?.can_message && (
              <Button
                data-testid="itemActionMessage"
                className={classes.buttonDisplay}
                color="primary"
                variant="contained"
                size="medium"
                startIcon={<LineIcon icon={'ico-comment-o'} />}
                onClick={() => actions.chatWithFriend(isMobile)}
              >
                {i18n.formatMessage({ id: 'message' })}
              </Button>
            )}
            <ItemActionMenu
              menuName="profilePopoverMenu"
              identity={identity}
              state={state}
              handleAction={handleAction}
              className={classes.actionsDropdown}
              size="medium"
              variant="outlined-square"
              color="primary"
              icon="ico-dottedmore-o"
              zIndex={1300}
              tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
            />
          </ButtonList>
        )}
        {!IS_ADMINCP && loggedIn && friendshipConfirmAwait && (
          <div>
            <div className={classes.sendRequest}>
              {i18n.formatMessage({
                id: 'This person sent you a friend request'
              })}
            </div>
            <ButtonList variant="fillWidth" spacing="medium">
              <Button
                variant="contained"
                color="primary"
                size="medium"
                type="submit"
                onClick={actions.acceptFriend}
              >
                {i18n.formatMessage({ id: 'confirm' })}
              </Button>
              <Button
                variant="outlined"
                color="primary"
                size="medium"
                type="submit"
                onClick={actions.denyFriend}
              >
                {i18n.formatMessage({ id: 'decline' })}
              </Button>
              <ItemActionMenu
                menuName="profilePopoverMenu"
                identity={identity}
                state={state}
                handleAction={handleAction}
                className={classes.actionsDropdown}
                size="medium"
                variant="outlined-square"
                color="primary"
                icon="ico-dottedmore-o"
                zIndex={1300}
                tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
              />
            </ButtonList>
          </div>
        )}
      </Paper>
    </Popper>
  );
}

ProfilePopup.displayName = 'UserItem_ProfilePopup';
