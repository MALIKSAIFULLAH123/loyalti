import React from 'react';
import HtmlViewer from '@metafox/html-viewer';
import { Box, Typography, styled } from '@mui/material';
import { ProductContext } from '../AdminAppStoreShowDetail';

const Wrapper = styled(Box, { name: 'ProductStoreDetailInstallationInfo' })(
  ({ theme }) => ({
    '& h1,h2,h3': {
      margin: '16px 0'
    },
    '& h4,h5,h6': {
      margin: '8px 0'
    }
  })
);

const Installation = () => {
  const item = React.useContext(ProductContext);

  return (
    <Wrapper>
      <Typography variant="body2" color="text.secondary">
        <HtmlViewer html={item?.text_installation} />
      </Typography>
    </Wrapper>
  );
};

export default Installation;
