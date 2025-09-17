import { LineIcon } from '@metafox/ui';
import Box from '@mui/material/Box';
import IconButton from '@mui/material/IconButton';
import Menu from '@mui/material/Menu';
import * as React from 'react';
import { ProfileMenuProps } from '../../types';
import ProfileMenuItem from './ProfileMenuItem';

type ProfileMenuMoreProps = {
  id?: string;
  icon?: string;
  keepMounted?: boolean;
  open?: boolean;
  className?: string;
  children?: JSX.Element;
  handleAction: (action: string) => void;
} & ProfileMenuProps;

const ProfileMenuMore = ({
  id = 'simpleMenu',
  handleAction,
  open,
  icon = 'ico-dottedmore-o',
  keepMounted,
  children,
  className,
  menuItems = [],
  classes,
  prefix,
  activeTab
}: ProfileMenuMoreProps) => {
  const anchorRef = React.useRef();

  return (
    <>
      <Box fontSize="small">
        <IconButton
          aria-haspopup="true"
          onClick={() => handleAction('toggleMenu')}
          ref={anchorRef}
          style={{ display: 'inline-block' }}
          size="small"
          className={className}
        >
          {children ? (
            children
          ) : (
            <LineIcon icon={icon} variant="itemActionIcon" />
          )}
        </IconButton>
      </Box>
      <Menu
        id={id}
        getContentAnchorEl={null}
        anchorOrigin={{
          vertical: 'bottom',
          horizontal: 'center'
        }}
        transformOrigin={{
          vertical: 'top',
          horizontal: 'center'
        }}
        anchorEl={anchorRef.current}
        keepMounted={keepMounted}
        open={open}
        onClose={() => handleAction('closeMenu')}
      >
        {menuItems.map((item, index) => (
          <ProfileMenuItem
            to={`${prefix}${item.to}`}
            label={item.label}
            key={item.to.toString()}
            active={item.tab === activeTab}
            className={classes.profileMenuItemMore}
            classes={classes}
          />
        ))}
      </Menu>
    </>
  );
};

export default ProfileMenuMore;
