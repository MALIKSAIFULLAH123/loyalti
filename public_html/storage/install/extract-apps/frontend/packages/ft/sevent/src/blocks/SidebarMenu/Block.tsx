/**
 * @type: block
 * name: sevent.block.sidebarMenu
 * title: SidebarMenu
 * keywords: sevent
 * description: Sevent SidebarMenu
 * experiment: true
 */
import { useGlobal, createBlock, ListViewBlockProps } from '@metafox/framework';
import React from 'react';
import { Divider, Button, Box } from '@mui/material';
import { Link } from 'react-router-dom';
import AddCircleIcon from '@mui/icons-material/AddCircle';

export default function SideBarMenu(props) {
  const {
    i18n,
    getAcl,
    useLoggedIn
  } = useGlobal();

  const acl = getAcl();
  const isLogged = useLoggedIn();

  const MainMenu = createBlock<ListViewBlockProps>({
    name: 'Search Block',
    extendBlock: 'core.block.sidebarAppMenu',
    overrides: {
      title: '',
      menuName: 'sidebarMenu',
      blockLayout: 'sidebar app menu'
    }
  });

  const link = '/sevent/add';
  const canCreate = 'sevent' && acl['sevent'] && acl['sevent']['sevent'].create;
  const phrase = 'add_a_sevent';

  const ResourceMenu = createBlock<ListViewBlockProps>({
    name: 'Search Block',
    extendBlock: 'core.block.sidebarAppMenu',
    overrides: {
      title: '',
      menuName: 'sidebarMyMenu',
      blockLayout: 'sidebar app menu'
    }
  });
  
  return (
    <>
      <h1 style={{ marginLeft: '16px' }}>
        {i18n.formatMessage({ id: 'sevent_menu' })}
      </h1>
      <MainMenu/>
      {canCreate && isLogged ? (
        <Box sx={{ mt: 1, mb: 1, ml: 2, mr: 2 }}>
          <Button startIcon={<AddCircleIcon/>} component={Link} style={{ width: '100%' }} 
            to={link} variant='contained' color='primary'>
            {i18n.formatMessage({ id: phrase })}
          </Button>
        </Box> 
      ) : null}
      {isLogged && (
        <>
        <Divider style={{ margin: '16px 0' }} />
        <h3 style={{ marginLeft: '16px' }}>
          {i18n.formatMessage({ id: 'you' })}
        </h3>
        <ResourceMenu />
        </>
      )}
    </>
  );
}
