/**
 * @type: ui
 * name: dataGrid.additionalSection.LanguageSection
 */

import { Box, Typography, styled } from '@mui/material';
import React from 'react';
import { ItemProps } from '../type';
import { useGlobal } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';

const gridContainerProps = { spacing: 2 };
const gridItemProps = { xs: 12, sm: 6, md: 3 };
const name = 'LanguageSectionDatagrid';

const Wrapper = styled(Box, { name })(({ theme }) => ({
  display: 'block',
  padding: theme.spacing(2),
  textAlign: 'center',
  borderRadius: theme.shape.borderRadius
}));

function DataGridAdditionalListing({ config }: ItemProps) {
  const { ListView } = useGlobal();

  const {
    title,
    description,
    descriptionProps,
    dataSource,
    titleProps,
    emptyPage = 'hide'
  } = config || {};

  if (!dataSource) return null;

  return (
    <Wrapper>
      {title ? (
        <Typography variant="h3" mb={2} {...titleProps}>
          {title}
        </Typography>
      ) : null}
      {description ? (
        <Box {...descriptionProps}>
          <HtmlViewer disableNl2br html={description} />
        </Box>
      ) : null}
      <Box sx={{ maxWidth: '100%', width: '800px', margin: '0 auto' }}>
        <ListView
          dataSource={dataSource}
          canLoadMore={false}
          clearDataOnUnMount
          gridContainerProps={gridContainerProps}
          gridItemProps={gridItemProps}
          itemProps={{
            text: { textAlign: 'center', alignItems: 'center' },
            content: { display: 'flex', flexDirection: 'column' }
          }}
          emptyPage={emptyPage}
          itemView={'language.itemView.recommendCard'}
          displayLimit={4}
        />
      </Box>
    </Wrapper>
  );
}

export default DataGridAdditionalListing;
