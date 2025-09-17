import {
  useAppMenu,
  useGlobal,
  HistoryState,
  LINK_OFFLINE_MODE
} from '@metafox/framework';
import { MenuItems } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { Box, Chip } from '@mui/material';
import React from 'react';
import { useLocation } from 'react-router-dom';
import useStyles from './AppBar.styles';
import { styled } from '@mui/material/styles';

const OfflineModeStyled = styled(Chip, { name: 'OfflineMode' })(
  ({ theme }) => ({
    width: 'fit-content',
    backgroundColor: theme.palette.warning.main,
    color: '#fff',
    cursor: 'pointer'
  })
);

export default function AppBarSubMenu() {
  const {
    useActionControl,
    getAcl,
    useSession,
    getSetting,
    usePageParams,
    i18n
  } = useGlobal();
  const params = usePageParams();
  const session = useSession();
  const acl = getAcl();
  const setting = getSetting();
  const { pathname: _pathname, state } = useLocation<HistoryState>();
  const [handleAction] = useActionControl<unknown, unknown>(null, {});
  const subMenu = useAppMenu('core', 'headerSubMenu');
  const classes = useStyles();
  const pathname = state?.as || _pathname;
  const hasAdminAccess = getAcl('core.admincp.has_admin_access');
  const offline = getSetting('core.offline');

  if (!subMenu || !subMenu.items) return null;

  const filteredItems = filterShowWhen(subMenu.items, {
    acl,
    setting,
    session,
    params
  });

  return (
    <Box
      sx={{ display: 'flex', alignItems: 'center', marginLeft: 'auto' }}
      data-testid="menuAppBar"
    >
      {hasAdminAccess && offline && (
        <a href={LINK_OFFLINE_MODE} target="_blank" rel="noreferrer">
          <OfflineModeStyled
            size="small"
            label={i18n.formatMessage({
              id: 'offline_mode'
            })}
          />
        </a>
      )}
      <MenuItems
        prefixName="appbar.item."
        fallbackName="popover"
        items={filteredItems}
        handleAction={handleAction}
        classes={classes}
        pathname={pathname}
      />
    </Box>
  );
}
