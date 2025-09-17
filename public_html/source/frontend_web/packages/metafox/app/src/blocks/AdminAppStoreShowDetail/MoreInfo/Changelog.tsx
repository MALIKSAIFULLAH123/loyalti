import React from 'react';
import HtmlViewer from '@metafox/html-viewer';
import { Box, Typography, styled } from '@mui/material';
import { ProductContext } from '../AdminAppStoreShowDetail';

const Wrapper = styled(Box, { name: 'changelog' })(({ theme }) => ({
  '& .app_detail_version_history_item': {
    lineHeight: 1.5,
    paddingBottom: '40px',
    '& h3': {
      marginBottom: '8px',
      fontSize: '20px'
    }
  }
}));

const Changelog = () => {
  const item = React.useContext(ProductContext);

  return (
    <Wrapper>
      <Typography variant="body2" color="text.secondary">
        <HtmlViewer html={item?.text_changelog} />
      </Typography>
    </Wrapper>
  );
};

export default Changelog;
