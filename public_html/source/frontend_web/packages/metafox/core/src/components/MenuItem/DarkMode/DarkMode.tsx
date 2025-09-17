/**
 * @type: ui
 * name: menuItem.as.darkMode
 */
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import {
  Radio,
  ListItemIcon,
  ListItemText,
  MenuItem,
  ListItem
} from '@mui/material';
import React from 'react';
import useStyles from './styles';

export default function NormalMenuItem({
  item,
  handleAction,
  onClick,
  iconClassName,
  closePopover
}: any) {
  const classes = useStyles();
  const { usePreference, preferenceBackend, i18n } = useGlobal();
  const { themeType } = usePreference();

  const handleClick = (value: string) => {
    preferenceBackend.setThemeType(value);
  };

  const [open, setOpen] = React.useState<boolean>(false);
  const handleToggle = () => setOpen(open => !open);

  return (
    <>
      <ListItem
        className={classes[item.className]}
        onClick={handleToggle}
        variant="contained"
        data-testid={item.testid || item.label || item.icon}
      >
        {item.icon ? (
          <ListItemIcon>
            <LineIcon icon={item.icon} />
          </ListItemIcon>
        ) : null}
        <ListItemText primary={item.label} />
        <LineIcon icon={open ? 'ico-angle-up' : 'ico-angle-down'} />
      </ListItem>
      {open ? (
        <>
          <MenuItem
            disableRipple
            sx={{ padding: '0 0 0 40px' }}
            onClick={() => handleClick('dark')}
          >
            <Radio size="small" checked={themeType === 'dark'} />
            {i18n.formatMessage({ id: 'on' })}
          </MenuItem>
          <MenuItem
            disableRipple
            onClick={() => handleClick('light')}
            sx={{ padding: '0 0 0 40px' }}
          >
            <Radio size="small" checked={themeType === 'light'} />
            {i18n.formatMessage({ id: 'off' })}
          </MenuItem>
          <MenuItem
            disableRipple
            sx={{ padding: '0 0 0 40px' }}
            onClick={() => handleClick('auto')}
          >
            <Radio size="small" checked={themeType === 'auto'} />
            {i18n.formatMessage({ id: 'auto' })}
          </MenuItem>
        </>
      ) : null}
    </>
  );
}
