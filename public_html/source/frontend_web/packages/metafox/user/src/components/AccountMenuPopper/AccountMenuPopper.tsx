import {
  RefOf,
  useAppMenu,
  useGlobal,
  useSession,
  Link
} from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { Popper, UserAvatar } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
/* eslint-disable max-len */
import { Paper, PopperProps, styled, Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import * as React from 'react';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      paper: {
        width: theme.popoverWidth.appbar,
        overflow: 'hidden',
        paddingBottom: theme.spacing(1)
      },
      menu: {
        minWidth: '296px'
      },
      menuItem: {
        color: theme.palette.text.secondary,
        padding: theme.spacing(1.5)
      },
      menuItemLink: {
        padding: 0
      },
      link: {
        color: theme.palette.text.secondary,
        textDecoration: 'none',
        padding: theme.spacing(1.5),
        display: 'block',
        flexGrow: 1
      },
      divider: {
        margin: `${theme.spacing(0.5)}px ${theme.spacing(2)}px ${theme.spacing(
          0.5
        )}px ${theme.spacing(2)}px`
      },
      icon: {
        textAlign: 'center',
        marginRight: theme.spacing(1.5)
      },
      iconButton: {
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(16),
        marginLeft: theme.spacing(1),
        width: '32px'
      },
      accountItem: {
        position: 'relative',
        borderBottom: '1px solid',
        borderBottomColor: theme.palette.border?.secondary,
        cursor: 'pointer',
        marginBottom: theme.spacing(1)
      },
      userAvatar: {},
      profileLink: {
        display: 'flex',
        padding: theme.spacing(2),
        alignItems: 'center',
        '&:hover': {
          backgroundColor: 'rgba(0,0,0,0.04)'
        }
      },
      userInfo: {
        maxWidth: 'calc(100% - 56px)'
      },
      viewProfileText: {
        fontSize: theme.mixins.pxToRem(15),
        color: theme.palette.text.secondary
      },
      fullName: {
        fontSize: theme.mixins.pxToRem(18),
        fontWeight: theme.typography.fontWeightBold,
        marginBottom: '2px',
        display: 'block',
        whiteSpace: 'nowrap',
        overflowX: 'hidden',
        textOverflow: 'ellipsis',
        color: theme.palette.text.primary
      }
    }),
  { name: 'MuiAppBarAccountMenu' }
);

const AvatarWrapper = styled('div', { name: 'AvatarWrapper' })(({ theme }) => ({
  marginRight: theme.spacing(1)
}));

export default function AccountMenuPopper({
  anchorRef,
  closePopover,
  ...rest
}: PopperProps & {
  anchorRef: RefOf<HTMLDivElement>;
  closePopover: () => void;
}) {
  const accountMenu = useAppMenu('core', 'accountMenu');
  const session = useSession();
  const { user } = session;
  const classes = useStyles();
  const {
    useActionControl,
    jsxBackend,
    i18n,
    getAcl,
    getSetting,
    useIsMobile
  } = useGlobal();
  const [handleAction] = useActionControl(null, {});
  const isMobile = useIsMobile();

  const setting = getSetting();
  const acl = getAcl();

  const accountMenuFilter = filterShowWhen(accountMenu.items, {
    setting,
    acl,
    session,
    isMobile
  });

  return (
    <Popper
      id="menuAccount"
      data-testid="menuAccount"
      anchorEl={anchorRef.current}
      className={classes.root}
      popperOptions={{
        strategy: 'fixed'
      }}
      {...rest}
    >
      <Paper className={classes.paper}>
        <ScrollContainer autoHide autoHeight autoHeightMax={500}>
          <div>
            <Link to={user.link} underline="none">
              <div className={classes.accountItem}>
                <div className={classes.profileLink}>
                  <AvatarWrapper>
                    <UserAvatar
                      user={user as any}
                      size={48}
                      noStory
                      showStatus={false}
                    />
                  </AvatarWrapper>
                  <div className={classes.userInfo}>
                    <span className={classes.fullName}>{user.full_name}</span>
                    <span className={classes.viewProfileText}>
                      {user?.role?.name ||
                        i18n.formatMessage({ id: 'view_profile' })}
                    </span>
                  </div>
                </div>
              </div>
            </Link>
            {accountMenuFilter.filter(Boolean).map((item, index) =>
              jsxBackend.render({
                component: `menuItem.as.${item.as || 'normal'}`,
                props: {
                  key: index.toString(),
                  item,
                  variant: item?.variant || 'contained',
                  handleAction,
                  closePopover
                }
              })
            )}
          </div>
        </ScrollContainer>
      </Paper>
    </Popper>
  );
}
