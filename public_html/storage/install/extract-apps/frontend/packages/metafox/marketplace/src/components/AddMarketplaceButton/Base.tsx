/**
 * @type: ui
 * name: marketplace.ui.addMarketplaceButton
 */
import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { Box, Button } from '@mui/material';
import { isEmpty } from 'lodash';
import * as React from 'react';

export default function AddPNewListingButton({
  blockProps = { variant: 'contained' }
}: BlockViewProps) {
  const { useSession, i18n, dispatch, usePageParams } = useGlobal();
  const { user: authUser } = useSession();
  const pageParams = usePageParams();

  if (isEmpty(authUser)) return null;

  const handleClick = () => {
    dispatch({
      type: 'addNewListing',
      payload: { identity: pageParams.identity }
    });
  };

  return (
    <Block>
      <BlockContent>
        <Box display="flex" flexDirection="row">
          <Button
            color="primary"
            onClick={handleClick}
            data-testid={'addMarketplace'}
          >
            {i18n.formatMessage({ id: 'add_marketplace' })}
          </Button>
        </Box>
      </BlockContent>
    </Block>
  );
}
