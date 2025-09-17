import { Link } from '@metafox/framework';
import { Box, styled } from '@mui/material';
import { FormatNumber } from '@metafox/ui';
import React from 'react';

const ItemContent = styled(Box, { slot: 'ItemContent' })(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  marginLeft: theme.spacing(1),
  padding: theme.spacing(1.5, 0),
  borderBottom: '1px solid #eaeaea',
  color: theme.palette.grey['A700'],
  fontSize: 15
}));

export default function AdminItemStats({ item }) {
  if (!item) return null;

  return (
    <ItemContent>
      <Box>{item.label}</Box>
      <Box color="text.secondary">
        {item.value > 0 && item.url ? (
          <Link to={item.url} target="_blank" color="primary">
            <FormatNumber
              value={item.value}
              formatOptions={item?.format_options}
            />
          </Link>
        ) : (
          <FormatNumber
            value={item.value}
            formatOptions={item?.format_options}
          />
        )}
      </Box>
    </ItemContent>
  );
}
