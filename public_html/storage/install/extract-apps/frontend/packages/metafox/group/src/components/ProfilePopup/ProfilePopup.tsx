import { Link, useGlobal } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { APP_GROUP, RESOURCE_GROUP } from '@metafox/group/constant';
import {
  ButtonAction,
  ButtonList,
  ItemActionMenu,
  LineIcon,
  TruncateText,
  HtmlViewerWrapper
} from '@metafox/ui';
import { filterShowWhen, getImageSrc, withDisabledWhen } from '@metafox/utils';
import { Paper, Popper, styled, Typography } from '@mui/material';
import * as React from 'react';
import useStyles from './ProfilePopup.styles';

const TypographyStyled = styled(Typography)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  fontWeight: '600',
  marginLeft: theme.spacing(1)
}));

export default function ProfilePopup({
  item,
  actions,
  handleAction,
  state,
  identity,
  open,
  loaded,
  anchorEl,
  user
}) {
  const {
    i18n,
    useSession,
    popoverBackend,
    assetUrl,
    getSetting,
    getAcl,
    useResourceMenu
  } = useGlobal();
  const { loggedIn, user: authUser } = useSession();
  const classes = useStyles();

  const acl = getAcl();
  const setting = getSetting();

  const itemMenu: any = useResourceMenu(
    APP_GROUP,
    RESOURCE_GROUP,
    'profilePopoverMenu'
  );

  if (!loaded || !loggedIn || !item) return null;

  const to = `/group/${item.id}`;
  const cover = getImageSrc(
    item?.cover,
    '500',
    assetUrl('group.cover_no_image')
  );
  const total_member = item.statistic?.total_member || 0;

  const condition = {
    item,
    acl,
    setting,
    isAuth: authUser?.id === user?.id
  };

  const actionMenuItems = withDisabledWhen(
    filterShowWhen(itemMenu.items, condition),
    condition
  );

  const reactButton: any = actionMenuItems.splice(0, 1)[0];

  const onAction = onSuccess => {
    handleAction(reactButton.value, { onSuccess });
  };

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
        <div
          className={classes.cover}
          style={{ backgroundImage: `url(${cover})` }}
        ></div>
        <div className={classes.popupInner}>
          <div className={classes.userName}>
            <TruncateText lines={2} className={classes.link}>
              <Link color="inherit" to={to}>
                {item.title}
              </Link>
            </TruncateText>
          </div>
          {item.description && (
            <div className={classes.description}>
              <TruncateText lines={3} variant="body1">
                <HtmlViewerWrapper mt={0}>
                  <HtmlViewer html={item.description} simpleTransform />
                </HtmlViewerWrapper>
              </TruncateText>
            </div>
          )}
          {item?.extra?.can_view_privacy ? (
            <div className={classes.type}>{item.reg_name}</div>
          ) : null}
          <div className={classes.statistic}>
            {i18n.formatMessage(
              { id: 'total_member' },
              { value: total_member }
            )}
          </div>
          {loggedIn ? (
            <div className={classes.buttonWrapper}>
              <ButtonList variant="fillFirst">
                {reactButton && (
                  <ButtonAction
                    isIcon
                    size="medium"
                    color="primary"
                    variant="outlined-square"
                    disabled={reactButton?.disabled}
                    action={onAction}
                    sx={{ width: '100%' }}
                    className={reactButton.name}
                  >
                    <LineIcon icon={reactButton?.icon} />
                    <TypographyStyled variant="body1">
                      {i18n.formatMessage({ id: reactButton.label })}
                    </TypographyStyled>
                  </ButtonAction>
                )}
                <ItemActionMenu
                  identity={identity}
                  items={actionMenuItems}
                  handleAction={handleAction}
                  size="medium"
                  variant="outlined-square"
                  color="primary"
                  icon="ico-dottedmore-o"
                  tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
                  zIndex={1300}
                />
              </ButtonList>
            </div>
          ) : null}
        </div>
      </Paper>
    </Popper>
  );
}

ProfilePopup.LoadingSkeleton = () => null;

ProfilePopup.displayName = 'GroupItem_ProfilePopup';
