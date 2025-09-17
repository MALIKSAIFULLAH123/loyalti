import { useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { Box, Skeleton, Typography } from '@mui/material';
import * as React from 'react';
import EditPayment from './EditPayment';

export type Props = {
  loaded: boolean;
  data: Record<string, any>[];
  title: string;
};

export default function GeneralSettings({ title, data, loaded }: Props) {
  const { dispatch, i18n } = useGlobal();

  // refresh for new data anytime its mount
  React.useEffect(() => {
    dispatch({ type: 'ewallet/paymentSettings' });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (!loaded) {
    return (
      <Block>
        <BlockHeader title={title} />
        <BlockContent>
          <Box>
            <Skeleton variant="text" />
            <Skeleton variant="rectangular" height={60} />
          </Box>
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        {data?.length ? (
          <Box>
            {data.map(payment => (
              <EditPayment key={`p${payment?.id}`} data={payment} />
            ))}
          </Box>
        ) : (
          <Typography color="text.secondary" variant="body1">
            {i18n.formatMessage({ id: 'no_payment_options_available' })}
          </Typography>
        )}
      </BlockContent>
    </Block>
  );
}
