/**
 * @type: block
 * name: core.block.AdminMainTop
 * title: AdminCP - BreadCrumbs & Secondary Menu
 * bundle: admincp
 * experiment: true
 */
import { createBlock, useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import AdminBreadCrumb from './AdminBreadCrumb';
import SecondaryMenu from './SecondaryMenu';
import React from 'react';

const BlockWrapper = styled('div', {
  name: 'AdminMainTop',
  slot: 'block'
})({
  padding: '8px 16px 8px 16px',
  display: 'flex',
  flexDirection: 'row',
  justifyContent: 'space-between'
});

const AdminMainTop = () => {
  const { usePageParams, usePageMeta } = useGlobal();
  const { appName } = usePageParams();
  const data = usePageMeta();
  const menuName = data?.secondary_menu || `${appName}.admin`;

  return (
    <BlockWrapper>
      <AdminBreadCrumb appName={appName} breadcrumbs={data?.breadcrumbs} />
      <SecondaryMenu appName={appName} menuName={menuName} />
    </BlockWrapper>
  );
};

export default createBlock({
  extendBlock: AdminMainTop,
  defaults: {
    title: 'AdminMainTop'
  }
});
