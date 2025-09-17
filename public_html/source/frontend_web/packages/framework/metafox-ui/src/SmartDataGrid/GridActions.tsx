import { RefOf, useGlobal, MenuShape } from '@metafox/framework';
import { AutoCompactMenu, MenuItemShape } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { Box, Button, ListItem, ListItemText, styled } from '@mui/material';
import React from 'react';
import useDataGridContext from './useDataGridContext';

interface Props {
  menu: MenuShape;
}

type ItemProps = {
  item: MenuItemShape;
  activeTab?: string;
  closeMenu?: any;
};

const DataGridToolbarRoot = styled(Box, {
  name: 'DataGridToolbar',
  slot: 'Root'
})(({ theme }) => {
  return Object.assign({
    display: 'flex',
    flexDirection: 'row',
    padding: '8px 0',
    minWidth: '30%'
  });
});

const SecondMenuItem = ({ item, closeMenu }: ItemProps) => {
  const { to, label, value, params } = item;
  const { handleGridAction } = useDataGridContext();
  const { navigate } = useGlobal();

  const handleClick = () => {
    closeMenu();

    if (!item.value) {
      navigate(to);
    } else {
      handleGridAction(value, { ...params });
    }
  };

  return (
    <ListItem
      onClick={handleClick}
      data-testid={item.testid ?? item.name}
      sx={{
        cursor: 'pointer'
      }}
    >
      <ListItemText>{label}</ListItemText>
    </ListItem>
  );
};

const MenuItem = ({ item }: ItemProps) => {
  const { to, label, value, params } = item;
  const { handleGridAction } = useDataGridContext();
  const { navigate } = useGlobal();

  const handleClick = () => {
    if (!item.value) {
      navigate(to);
    } else {
      handleGridAction(value, { ...params });
    }
  };

  return (
    <Box sx={{ pl: 1 }}>
      <Button
        data-testid={item.testid ?? item.name}
        onClick={handleClick}
        variant="text"
        size="smaller"
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
      >
        {i18n.formatMessage({ id: 'more' })}
      </Button>
    );
  }
);

const Menu = ({ menu }: Props) => {
  const { getSetting, getAcl } = useGlobal();
  const data = menu?.items;

  if (!data || !data.length) return null;

  const items = filterShowWhen(data, {
    settings: getSetting(),
    setting: getSetting(),
    acl: getAcl()
  });

  if (!items.length) return null;

  // auto collapse menu
  return (
    <DataGridToolbarRoot>
      <Box
        style={{ display: 'flex', flex: 1, overflow: 'hidden' }}
        data-testid="datagridMenu"
      >
        <AutoCompactMenu
          items={items}
          SecondMenuItem={SecondMenuItem}
          MoreButton={MoreButton}
          MenuItem={MenuItem}
        />
      </Box>
    </DataGridToolbarRoot>
  );
};

export default Menu;
