import { Link, useGlobal } from '@metafox/framework';
import { mappingRelationship } from '@metafox/pages/utils';
import {
  ButtonList,
  LineIcon,
  Statistic,
  TruncateText,
  HtmlViewerWrapper
} from '@metafox/ui';
import { colorHash, getImageSrc, shortenFullName } from '@metafox/utils';
import { Avatar, Button, Paper, Popper } from '@mui/material';
import clsx from 'clsx';
import * as React from 'react';
import useStyles from './ProfilePopup.styles';
import HtmlViewer from '@metafox/html-viewer';

export default function ProfilePopup({
  item,
  actions,
  identity,
  handleAction,
  state,
  open,
  anchorEl
}) {
  const { i18n, ItemActionMenu, popoverBackend, useSession } = useGlobal();
  const { loggedIn } = useSession();
  const classes = useStyles();

  if (!loggedIn || !item) return null;

  const {
    statistic,
    is_liked,
    is_owner,
    extra,
    link: to,
    description
  } = item || {};

  const avatar = getImageSrc(item.avatar, '240');
  const bgColor = colorHash.hex(
    shortenFullName(item.full_name || item.title) || ''
  );

  const reactButton = mappingRelationship(
    is_owner,
    is_liked,
    extra?.can_unlike || false,
    extra?.can_like || false,
    actions
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
        <div className={classes.userHeader}>
          <Link to={to}>
            <Avatar
              alt={item.title}
              children={shortenFullName(item.title)}
              src={avatar}
              sx={{ width: 80, height: 80 }}
              style={{ backgroundColor: bgColor }}
            />
          </Link>
          <div className={classes.userName}>
            <Link to={to}>{item.title}</Link>
          </div>
        </div>
        <div className={classes.description}>
          <TruncateText lines={3} variant="body1">
            <HtmlViewerWrapper mt={0}>
              <HtmlViewer html={description} simpleTransform />
            </HtmlViewerWrapper>
          </TruncateText>
        </div>
        {item.location_name ? (
          <div className={classes.statistic}>
            <LineIcon icon="ico-checkin-o" />
            <TruncateText lines={1} variant="body2">
              {item.location_name}
            </TruncateText>
          </div>
        ) : null}
        <Statistic
          values={statistic}
          display={'total_like'}
          skipZero={false}
          sx={statistic?.total_like && { mt: 1 }}
        />
        {loggedIn ? (
          <div className={classes.buttonWrapper}>
            <ButtonList variant="fillFirst">
              {reactButton ? (
                <Button
                  className={clsx(is_liked && classes.liked)}
                  variant="contained"
                  size="medium"
                  disabled={reactButton.disabled}
                  startIcon={<LineIcon icon={reactButton.icon} />}
                  color={is_liked ? 'default' : 'primary'}
                  onClick={reactButton.actions}
                >
                  {i18n.formatMessage({ id: reactButton.textId })}
                </Button>
              ) : null}
              <ItemActionMenu
                identity={identity}
                state={state}
                handleAction={handleAction}
                size="medium"
                color="primary"
                variant="outlined-square"
                icon="ico-dottedmore-o"
                tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
                menuName="profilePopoverMenu"
                zIndex={1300}
              />
            </ButtonList>
          </div>
        ) : null}
      </Paper>
    </Popper>
  );
}

ProfilePopup.LoadingSkeleton = () => null;

ProfilePopup.displayName = 'PageItem_ProfilePopup';
