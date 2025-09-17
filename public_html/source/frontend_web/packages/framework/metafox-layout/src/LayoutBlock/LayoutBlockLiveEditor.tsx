/**
 * @type: ui
 * name: layout.blockWithQuickEdit
 */
/* eslint-disable no-console */
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import {
  Divider,
  IconButton,
  ListItem,
  ListItemText,
  Menu,
  Tooltip
} from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const Root = styled('div', {
  name: 'LiveEditorBlock',
  slot: 'Root'
})(({ theme }) => ({
  position: 'absolute',
  left: 10,
  zIndex: theme.zIndex.appBar + 1
  // paddingBottom: theme.spacing(1)
}));

export default function LayoutBlockLiveEditor(props) {
  const { blockName } = props;
  const { jsxBackend, dispatch, i18n } = useGlobal();
  const Controller = jsxBackend.get(blockName);
  const anchorRef = React.useRef<HTMLDivElement>();
  const [open, setOpen] = React.useState<boolean>(false);

  const openMenu = React.useCallback(() => setOpen(true), []);

  const closeMenu = React.useCallback(() => setOpen(false), []);

  const handleClick = (name: string) => {
    setOpen(false);
    dispatch({ type: name, payload: props });
  };

  if (!Controller) {
    console.error(`Unexpected not found "${blockName}"`);

    return null;
  }

  return (
    <Root ref={anchorRef}>
      <Tooltip title={i18n.formatMessage({ id: 'edit_layout_block' })}>
        <IconButton onClick={openMenu} size="smallest" color="success">
          <LineIcon icon="ico-pencilline" />
        </IconButton>
      </Tooltip>
      <Menu
        id="menuBlockEditor"
        open={open}
        anchorEl={anchorRef.current}
        onClose={closeMenu}
      >
        <ListItem
          dense
          button
          focusRipple={false}
          onClick={() => handleClick('@layout/editBlock')}
        >
          <ListItemText>
            {i18n.formatMessage({ id: 'edit_layout_block' })}
          </ListItemText>
        </ListItem>
        <ListItem
          dense
          button
          focusRipple={false}
          onClick={() => handleClick('@layout/createBlock')}
        >
          <ListItemText>
            {i18n.formatMessage({ id: 'create_layout_block' })}
          </ListItemText>
        </ListItem>
        <Divider light variant="middle" />
        <ListItem
          dense
          button
          focusRipple={false}
          onClick={() => handleClick('@layout/deleteBlock')}
        >
          <ListItemText>{i18n.formatMessage({ id: 'remove' })}</ListItemText>
        </ListItem>
      </Menu>
    </Root>
  );
}
