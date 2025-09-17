/**
 * @type: ui
 * name: featured.itemView.no_content_record
 * chunkName: featured
 */

import React from 'react';
import { styled } from '@mui/material';
import { useGlobal } from '@metafox/framework';

const ItemStyled = styled('div', { name: 'ItemStyled' })(({ theme }) => ({
  fontSize: 15,
  color: theme.palette.text.secondary,
  marginBottom: theme.spacing(1),
  display: 'flex',
  padding: theme.spacing(2)
}));

const EmptyRow = () => {
  const { i18n } = useGlobal();

  return (
    <ItemStyled>{i18n.formatMessage({ id: 'no_results_found' })}</ItemStyled>
  );
};

export default EmptyRow;
