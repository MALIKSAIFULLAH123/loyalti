import { FormBuilder, FormSchemaShape } from '@metafox/form';
import { MenuShape, useGlobal } from '@metafox/framework';
import { ClickOutsideListener, MenuItems, MenuItemShape } from '@metafox/ui';
import { Box, Button, IconButtonProps, Paper, Popper } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';
import GridActions from './GridActions';

interface Props {
  value?: unknown;
  menu: MenuShape;
  size?: IconButtonProps['size'];
  handleAction: (type: string, value?: object, meta?: object) => void;
  invisible?: boolean;
  disabled?: boolean;
  selectionCount?: number;
  hideTitle?: boolean;
  multipleSelection?: boolean;
  hideDescription?: boolean;
  formSchema?: FormSchemaShape;
  onSearchChange?: any;
  totalItems?: React.ReactNode;
  gridActionMenu?: MenuShape;
}

const DataGridToolbarRoot = styled(Box, {
  name: 'DataGridToolbar',
  slot: 'Root'
})(({ theme }) => {
  return Object.assign({
    display: 'flex',
    flexDirection: 'row',
    padding: '8px 16px 4px 8px',
    borderBottom: theme.mixins.border('secondary')
  });
});

const DataGridToolbarMenu = styled(Box, {
  name: 'DataGridToolbar',
  slot: 'Menu'
})(() => ({
  marginLeft: 0,
  minWidth: 110,
  paddingTop: 8,
  marginRight: 'auto',
  display: 'flex',
  alignItems: 'flex-start',
  justifyContent: 'flex-end',
  flexWrap: 'wrap'
}));

export default function GridToolbar({
  selectionCount,
  formSchema,
  onSearchChange,
  menu,
  handleAction,
  invisible,
  gridActionMenu,
  multipleSelection,
  totalItems
}: Props) {
  if (invisible) return null;

  if (!menu?.items?.length && !formSchema && !gridActionMenu) return null;

  const items = menu?.items;

  // convert to popover menu buttons.
  return (
    <DataGridToolbarRoot>
      <Box sx={{ pl: 1, flex: 1, minWidth: 0 }}>
        {totalItems}
        {formSchema ? (
          <FormBuilder
            initialValues={{}}
            noHeader
            onSubmit={onSearchChange}
            formSchema={formSchema}
            navigationConfirmWhenDirty={false}
          />
        ) : null}
      </Box>
      {gridActionMenu ? <GridActions menu={gridActionMenu} /> : null}
      {multipleSelection && items?.length ? (
        <DataGridToolbarMenu>
          <BatchActionMenu
            selectionCount={selectionCount}
            items={items}
            handleAction={handleAction}
          />
        </DataGridToolbarMenu>
      ) : null}
    </DataGridToolbarRoot>
  );
}

function BatchActionMenu({
  selectionCount,
  items,
  handleAction
}: {
  selectionCount: number;
  items: MenuItemShape[];
  handleAction: (action: any) => void;
}) {
  const { i18n, useTheme } = useGlobal();
  const theme = useTheme();
  const [open, setOpen] = React.useState<boolean>(false);
  const buttonRef = React.useRef<HTMLButtonElement>();

  return (
    <>
      <Button
        disabled={!selectionCount}
        ref={buttonRef}
        variant="outlined"
        onClick={() => setOpen(true)}
        sx={{ fontWeight: 'normal', flexWrap: 'nowrap' }}
      >
        {i18n.formatMessage(
          {
            id: 'with_n_selected',
            defaultMessage: 'Selected'
          },
          { value: selectionCount }
        )}
      </Button>
      <ClickOutsideListener
        onClickAway={() => setOpen(false)}
        excludeRef={buttonRef}
      >
        <Popper
          open={Boolean(open && buttonRef.current)}
          anchorEl={buttonRef.current}
          placement="bottom-end"
          style={{
            zIndex: theme.zIndex.drawer
          }}
        >
          <Paper elevation={3} sx={{ py: 1, mr: 1 }}>
            <MenuItems
              items={items}
              handleAction={handleAction}
              closeMenu={() => setOpen(false)}
            />
          </Paper>
        </Popper>
      </ClickOutsideListener>
    </>
  );
}
