import { ClickAwayListener, Button, ListItemIcon } from '@mui/material';
import { useGlobal } from '@metafox/framework';
import * as React from 'react';
import Box from '@mui/material/Box';
import Popper from '@mui/material/Popper';
import Fade from '@mui/material/Fade';
import MenuList from '@mui/material/MenuList';
import MenuItem from '@mui/material/MenuItem';
import ListItemText from '@mui/material/ListItemText';
import ArrowDropDownIcon from '@mui/icons-material/ArrowDropDown';

export default function Attend({ defaultRsvp, identity, item }) {
  const [open, setOpen] = React.useState(false);
  const [rsvp, setRsvp] = React.useState(defaultRsvp);
  const [anchorEl, setAnchorEl] = React.useState(null);
  const { i18n, useTheme, dispatch } = useGlobal();

  const theme = useTheme();

  const handleClick = (event) => {
    setAnchorEl(event.currentTarget);
    setOpen((previousOpen) => !previousOpen);
  };

  const handleRsvp = (typeId) => {
    setOpen(false);
    setRsvp(typeId);
    dispatch({ type: 'sevent/attend', payload: { identity, typeId } });
  };

  const handleClickAway = () => {
    setOpen(false);
  };

  let label = 'noaction';
  let icon = 'calendar-o';

  switch (rsvp) {
    case 2:
      label = 'sevent_interested';
      icon = 'calendar-star-o';
      break;
    case 1:
      label = 'sevent_going';
      icon = 'check-circle-o';
      break;
  }

  const canBeOpen = open && Boolean(anchorEl);
  const id = canBeOpen ? 'transition-popper' : undefined;
 
  return (
    <div>
      <ClickAwayListener onClickAway={handleClickAway}>
        <div style={{ position: 'relative' }}>
        <Button
          variant="outlined"
          style={theme.palette.mode !== 'light' ? { color: '#eee' } : null}
          onClick={handleClick}
          color="secondary"
          disabled={item.is_expiry ? true : false}
          startIcon={<i className={`ico ico-${icon}`} />}
          endIcon={<ArrowDropDownIcon/>}
        >
        {i18n.formatMessage({ id: label })}
      </Button>
        <Popper style={{ zIndex: 3 }} placement='bottom-end'
          id={id} open={open} anchorEl={anchorEl} transition>
          {({ TransitionProps }) => (
            <Fade {...TransitionProps} timeout={350}>
              <Box sx={{ marginTop: '5px', boxShadow: '0 0 1px #333', p: 1, 
                  borderRadius: '5px', bgcolor: 'background.paper' }}>
                <MenuList>
                  <MenuItem onClick={() => { handleRsvp(1); } }>
                    <ListItemIcon>
                      <i className='ico ico-check-circle-o'/>
                    </ListItemIcon>
                    <ListItemText >
                      {i18n.formatMessage({ id: 'sevent_going' })}
                    </ListItemText>
                  </MenuItem>
                  <MenuItem onClick={() => { handleRsvp(2); } }>
                    <ListItemIcon>
                      <i className='ico ico-calendar-star-o'/>
                    </ListItemIcon>
                    <ListItemText >
                      {i18n.formatMessage({ id: 'sevent_interested' })}
                    </ListItemText>
                  </MenuItem>
                </MenuList>
              </Box>
            </Fade>
          )}
        </Popper>
        </div>
      </ClickAwayListener>
    </div>
  );
}
