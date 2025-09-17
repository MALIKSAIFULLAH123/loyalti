import { useGlobal, SmartDataGridProps } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, IconButton, Tooltip, Typography } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

type Props = Pick<
  SmartDataGridProps,
  'title' | 'hideTitle' | 'description' | 'hideDescription' | 'hideToggleSearch'
> & {
  toggleSearchForm: () => void;
  multipleSelection?: boolean;
};

const GridBannerRoot = styled('div', {
  name: 'SmartDataGrid',
  slot: 'GridBannerRoot'
})({});

export default function GridBanner({
  title,
  description,
  hideTitle,
  hideDescription,
  hideToggleSearch,
  toggleSearchForm,
  multipleSelection
}: Props) {
  const { i18n } = useGlobal();

  if (!title || hideTitle) {
    return null;
  }

  return (
    <GridBannerRoot>
      <Box sx={{ display: 'flex' }}>
        <Typography
          component="h2"
          variant="h4"
          sx={{ flex: '1', padding: '16px 8px 8px 16px' }}
        >
          {title}
        </Typography>
        <Box sx={{ padding: '8px 8px 8px 16px' }}>
          {!hideToggleSearch ? (
            <Tooltip
              title={i18n.formatMessage({
                id: 'toggle_search'
              })}
            >
              <IconButton onClick={toggleSearchForm}>
                <LineIcon icon="ico-filter" />
              </IconButton>
            </Tooltip>
          ) : null}
        </Box>
      </Box>
      {hideDescription ? (
        <div>
          <Typography component="p" variant="body2">
            {description}
          </Typography>
        </div>
      ) : null}
    </GridBannerRoot>
  );
}
