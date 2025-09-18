import { ClickAwayListener, Button, ListItemIcon } from '@mui/material';
import { useGlobal } from '@metafox/framework';
import React, { useState, useMemo } from 'react';
import Box from '@mui/material/Box';
import Popper from '@mui/material/Popper';
import Fade from '@mui/material/Fade';
import MenuList from '@mui/material/MenuList';
import MenuItem from '@mui/material/MenuItem';
import ListItemText from '@mui/material/ListItemText';
import ArrowDropDownIcon from '@mui/icons-material/ArrowDropDown';
import GoogleIcon from '@mui/icons-material/Google';

const formatICSDate = (date) => {
  const year = date.getFullYear();
  const month = (date.getMonth() + 1).toString().padStart(2, '0'); 
  const day = date.getDate().toString().padStart(2, '0');
  const hours = date.getHours().toString().padStart(2, '0'); 
  const minutes = date.getMinutes().toString().padStart(2, '0'); 
  const seconds = date.getSeconds().toString().padStart(2, '0');

  return `${year}${month}${day}T${hours}${minutes}${seconds}`;
};

const generateICS = (item) => {
  const {
    title,
    location_name,
    start_date,
    end_date
  } = item;

  const startDate = new Date(start_date);
  const endDate = new Date(end_date);
  
  const dtStart = formatICSDate(startDate); 
  const dtEnd = formatICSDate(endDate);
  const currentDomain = window.location.hostname;
  const short_description = item.short_description + 
    (item.is_online ? ` ${item.online_link}` : '');

  return `
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//${currentDomain}//${currentDomain}//EN
BEGIN:VEVENT
UID:${Date.now()}@${currentDomain}
DTSTART:${dtStart}
DTEND:${dtEnd}
SUMMARY:${title}
DESCRIPTION:${short_description}
LOCATION:${location_name ? location_name : ''}
END:VEVENT
END:VCALENDAR
  `.trim(); 
};

const downloadICS = (item) => {
  const icsContent = generateICS(item);
  const blob = new Blob([icsContent], { type: 'text/calendar' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `${item.title.replace(/\s+/g, '_')}.ics`; 
  a.click();
  window.URL.revokeObjectURL(url);
};

export default function Attend({ item, isLogged }) {
  const [open, setOpen] = React.useState(false);
  const [favourite, setFavourite] = useState(item.is_favourite);
  const [anchorEl, setAnchorEl] = React.useState(null);
  const { i18n, useTheme, dispatch, getSetting } = useGlobal();
  const identity = item?._identity;
  const theme = useTheme();
  let output;

  const handleClick = (event) => {
    setAnchorEl(event.currentTarget);
    setOpen((previousOpen) => !previousOpen);
  };

  const updateFavourite = () => {
    setFavourite(prevFavourite => !prevFavourite);
    dispatch({ type: 'sevent/favourite', payload: { identity } });
  };

  const buttonText = useMemo(() => {
    return favourite
      ? i18n.formatMessage({ id: 'remove_sevent_favourite' })
      : i18n.formatMessage({ id: 'add_sevent_favourite' });
  }, [favourite, i18n]);

  const handleClickAway = () => {
    setOpen(false);
  };

  const canBeOpen = open && Boolean(anchorEl);
  const id = canBeOpen ? 'transition-popper' : undefined;

  const startDateObj = new Date(item.start_date);
  const enabledAddCalendar = getSetting('sevent.enable_add_calendar');
  const startYear = startDateObj.getFullYear();
  const startMonth = (startDateObj.getMonth() + 1).toString().padStart(2, '0');
  const startDay = startDateObj.getDate().toString().padStart(2, '0');
  const startHours = startDateObj.getHours().toString().padStart(2, '0');
  const startMinutes = startDateObj.getMinutes().toString().padStart(2, '0');
  const startSeconds = startDateObj.getSeconds().toString().padStart(2, '0');

  const formattedStartDate = `${startYear}${startMonth}${startDay}T${startHours}${startMinutes}${startSeconds}`;

  const endDateObj = new Date(item.end_date);

  const endYear = endDateObj.getFullYear();
  const endMonth = (endDateObj.getMonth() + 1).toString().padStart(2, '0');
  const endDay = endDateObj.getDate().toString().padStart(2, '0');
  const endHours = endDateObj.getHours().toString().padStart(2, '0');
  const endMinutes = endDateObj.getMinutes().toString().padStart(2, '0');
  const endSeconds = endDateObj.getSeconds().toString().padStart(2, '0');

  const formattedEndDate = `${endYear}${endMonth}${endDay}T${endHours}${endMinutes}${endSeconds}`;

  const googleLink = 'https://calendar.google.com/calendar/u/0/r/eventedit?text='
    + encodeURIComponent(item.title) + '&dates=' + formattedStartDate + '/' + formattedEndDate + '&details='
   + encodeURIComponent(item.short_description + 
   (item.is_online ? `&nbsp;<a href="${item.online_link}">${item.online_link}</a>` : ''))
   + '&location='
   + (!item.is_online ? encodeURIComponent(item.location_name) : '') + '&sf=true&output=xml';

  const yahooLink = 'http://calendar.yahoo.com/?v=60&ST=' + formattedStartDate + '&ET=' + formattedEndDate + 
  '&REM1=01d&TITLE=' + encodeURIComponent(item.title) + '&VIEW=d&in_loc=' + 
  (!item.is_online ? encodeURIComponent(item.location_name) : '');

  if (enabledAddCalendar)
    output = (
      <div>
        <ClickAwayListener onClickAway={handleClickAway}>
          <div style={{ position: 'relative' }}>
          <Button
            variant='outlined'
            size='small'
            style={theme.palette.mode !== 'light' ? { color: '#eee' } : null}
            onClick={handleClick}
            color="secondary"
            disabled={item.is_expiry ? true : false}
            startIcon={<i className='ico ico-calendar-plus-o' />}
            endIcon={<ArrowDropDownIcon/>}
          >
          {i18n.formatMessage({ id: 'sevent_add_to_calendar' })}
        </Button>
          <Popper style={{ zIndex: 3 }} placement='bottom-end'
            id={id} open={open} anchorEl={anchorEl} transition>
            {({ TransitionProps }) => (
              <Fade {...TransitionProps} timeout={350}>
                <Box sx={{ marginTop: '5px', boxShadow: '0 0 1px #333', p: 1, 
                    borderRadius: '5px', bgcolor: 'background.paper' }}>
                  <MenuList>
                    {isLogged ? (
                    <MenuItem onClick={() => { updateFavourite(); } }>
                      <ListItemIcon>
                        <i className={!favourite ? 'ico ico-star-o' : 'ico ico-star'}/>
                      </ListItemIcon>
                      <ListItemText >
                        {buttonText}
                      </ListItemText>
                    </MenuItem>
                    ) : null}
                    <MenuItem
                      component="a" 
                      href={googleLink}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <ListItemIcon>
                        <GoogleIcon style={{ fontSize: '15px' }} />
                      </ListItemIcon>
                      <ListItemText primary={i18n.formatMessage({ id: 'sevent_google_calendar' })} />
                    </MenuItem>
                    <MenuItem
                      component="a" 
                      href={yahooLink}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <ListItemIcon>
                        <i className='ico ico-external-link' />
                      </ListItemIcon>
                      <ListItemText primary={i18n.formatMessage({ id: 'sevent_yahoo_calendar' })} />
                    </MenuItem>
                    <MenuItem onClick={() => { downloadICS(item); setOpen(false); } }>
                      <ListItemIcon>
                        <i className='ico ico-download' />
                      </ListItemIcon>
                      <ListItemText primary={i18n.formatMessage({ id: 'sevent_ical' })} />
                    </MenuItem>
                    <MenuItem onClick={() => { downloadICS(item); setOpen(false); } }>
                      <ListItemIcon>
                        <i className='ico ico-download' />
                      </ListItemIcon>
                      <ListItemText primary={i18n.formatMessage({ id: 'sevent_outlook' })} />
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
  else 
    output = (
      <Button
      variant="outlined"
      size='small'
      style={theme.palette.mode !== 'light' ? { color: '#eee' } : null}
      onClick={updateFavourite}
      color="secondary"
      startIcon={<i className={!favourite ? 'ico ico-star-o' : 'ico ico-star'} />}
    >
      {buttonText}
    </Button>
  );

  return output;
}
