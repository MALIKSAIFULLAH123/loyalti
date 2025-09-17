import { RefOf, useGlobal, useLocation, Link } from '@metafox/framework';
import { AutoCompactMenu, MenuItemShape } from '@metafox/ui';
import { comparePathName, filterShowWhen } from '@metafox/utils';
import { Box, Button, ListItem, ListItemText } from '@mui/material';
import React from 'react';

// 1 day in milliseconds
const MS_1_HOUR = 36e5;

type ItemProps = {
  item: MenuItemShape;
  activeTab?: string;
  closeMenu?: any;
};

const SecondMenuItem = ({ item, activeTab, closeMenu }: ItemProps) => {
  const { to, label } = item;
  const { navigate } = useGlobal();

  const isActive = comparePathName(activeTab, to);

  const handleClick = () => {
    closeMenu();
    navigate(to);
  };

  return (
    <ListItem
      onClick={handleClick}
      data-testid={item.testid ?? item.name}
      sx={{
        cursor: 'pointer',
        backgroundColor: isActive ? 'background.default' : 'unset'
      }}
    >
      <ListItemText>{label}</ListItemText>
    </ListItem>
  );
};

const MenuItem = ({ item, activeTab }: ItemProps) => {
  const { to, label, value, params } = item;

  const { dispatch } = useGlobal();

  const handleClick = () => {
    dispatch({ type: value, payload: params });
  };

  const active = comparePathName(activeTab, to);

  return (
    <Box sx={{ px: 1 }}>
      <Button
        data-testid={item.testid ?? item.name}
        size="small"
        variant="text"
        sx={{
          textDecoration: 'none',
          fontWeight: active ? 'bold' : 'normal',
          textAlign: 'center'
        }}
        onClick={value ? handleClick : undefined}
        component={!value ? Link : undefined}
        to={!value ? to : undefined}
      >
        {label}
      </Button>
    </Box>
  );
};

interface MoreButtonProps {
  onClick: () => void;
}

const MoreButton = React.forwardRef(
  (props: MoreButtonProps, ref: RefOf<HTMLButtonElement>) => {
    const { i18n } = useGlobal();

    return (
      <Button
        size="small"
        variant="outlined"
        color="primary"
        ref={ref}
        // endIcon={<LineIcon icon="ico-caret-down" />}
        sx={{ fontWeight: 'normal' }}
        {...props}
        data-testid="moreButton"
      >
        {i18n.formatMessage({ id: 'more' })}
      </Button>
    );
  }
);

const Menu = ({ menuName }: { appName: string; menuName: string }) => {
  const { useFetchDetail, getSetting, getAcl } = useGlobal();
  const location = useLocation();
  const [data] = useFetchDetail({
    dataSource: {
      apiUrl: `/admincp/menu/${menuName}`
    },
    forceReload: false,
    cachePrefix: 'menu',
    cacheKey: menuName,
    ttl: MS_1_HOUR
  });

  if (!data || !data.length) return null;

  const items = filterShowWhen(data, {
    settings: getSetting(),
    setting: getSetting(),
    acl: getAcl()
  });

  if (!items.length) return null;

  // auto collapse menu
  return (
    <Box
      sx={{
        display: 'flex',
        flex: 1,
        overflow: 'hidden',
        '& button': { minWidth: 'auto' }
      }}
      data-testid="secondaryMenu"
    >
      <AutoCompactMenu
        items={items}
        activeTab={location.pathname}
        SecondMenuItem={SecondMenuItem}
        MoreButton={MoreButton}
        MenuItem={MenuItem}
        menuName={menuName}
      />
    </Box>
  );
};

export default Menu;
