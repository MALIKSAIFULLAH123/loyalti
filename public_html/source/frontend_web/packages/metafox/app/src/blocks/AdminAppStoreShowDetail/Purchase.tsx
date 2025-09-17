import { useGlobal } from '@metafox/framework';
import { Box, Button, Typography, styled } from '@mui/material';
import React from 'react';
import { ProductContext } from './AdminAppStoreShowDetail';

const VersionWrapper = styled(Box, { name: 'versionWrapper' })(({ theme }) => ({
  border: 'solid 1px rgba(85, 85, 85, 0.1)',
  width: '100%',
  maxWidth: '100%',
  padding: theme.spacing(3)
}));

const VersionInfo = styled(Box, { name: 'versionInfo' })(({ theme }) => ({
  alignItems: 'center',
  justifyContent: 'space-between',
  height: 'fit-content',
  display: 'flex'
}));

const Divider = styled(Box, { name: 'divider' })(({ theme }) => ({
  height: '1px',
  backgroundColor: 'rgba(85, 85, 85, 0.1)',
  margin: theme.spacing(3)
}));

const Purchase = () => {
  const { redirectTo, i18n } = useGlobal();
  const item = React.useContext(ProductContext);

  const handlePurchase = () => {
    if (item?.purchase_url) {
      redirectTo(item.purchase_url);
    }
  };

  const {
    discount,
    price,
    can_install,
    can_purchase,
    pricing_type_label,
    pricing_type
  } = Object.assign({}, item);

  return (
    <VersionWrapper>
      <VersionInfo>
        <Typography variant="body1" color="text.hint">
          {pricing_type_label ?? i18n.formatMessage({ id: pricing_type })}
        </Typography>
        <Box display="flex" sx={{ alignItems: 'center' }}>
          {discount && (
            <Typography
              sx={{ textDecoration: 'line-through' }}
              color="text.hint"
              variant="body1"
            >
              ${price}
            </Typography>
          )}
          <Typography color="warning.main" variant="h2" ml={1}>
            ${discount || price}
          </Typography>
        </Box>
      </VersionInfo>
      <Divider />
      <Button
        variant="contained"
        sx={{ width: '100%' }}
        disabled={!(can_purchase && !can_install)}
        onClick={handlePurchase}
      >
        {i18n.formatMessage({ id: 'purchase' })}
      </Button>
    </VersionWrapper>
  );
};

export default Purchase;
