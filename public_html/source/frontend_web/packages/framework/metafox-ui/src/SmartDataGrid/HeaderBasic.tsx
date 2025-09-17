/**
 * @type: ui
 * name: dataGrid.header.Basic
 */

import HeaderCell from './HeaderCell';
import React from 'react';

function HeaderBasic({ colDef, searchValues }) {
  return (
    <HeaderCell colDef={colDef} searchValues={searchValues}>
      {colDef.headerName ?? ''}
    </HeaderCell>
  );
}

export default HeaderBasic;
