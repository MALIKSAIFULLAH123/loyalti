/**
 * @type: dialog
 * name: marketplace.dialog.listingDetailView
 * chunkName: pages.marketplace
 */

import { connectItem, useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { Box, styled } from '@mui/material';
import * as React from 'react';
import { MarketplaceDetailViewProps } from '../../types';

const name = 'ListingViewDialog';

const DialogContentStyled = styled(DialogContent, { name, slot: 'root' })(
  ({ theme }) => ({
    padding: 0,
    paddingTop: '0 !important',
    overflowY: 'visible',
    display: 'flex'
  })
);
const ContentWrapper = styled(DialogContent, { name, slot: 'ContentWrapper' })(
  ({ theme }) => ({
    width: '100%',
    maxWidth: '100%'
  })
);

function ListingViewDialog({ item, identity }: MarketplaceDetailViewProps) {
  const { useDialog, jsxBackend, i18n, useIsMobile } = useGlobal();
  const { dialogProps } = useDialog();
  const DetailView = jsxBackend.get('listing.block.listingView');
  const isMobile = useIsMobile();

  if (!item) return null;

  return (
    <Dialog
      {...dialogProps}
      maxWidth={'xl'}
      scroll="body"
      data-testid="popupViewListing"
    >
      <DialogTitle enableBack={isMobile} disableClose={isMobile}>
        <Box display="flex" alignItems="center">
          {i18n.formatMessage({ id: 'listing' })}
        </Box>
      </DialogTitle>
      <DialogContentStyled>
        <ContentWrapper>
          <DetailView item={item} identity={identity} isModalView />
        </ContentWrapper>
      </DialogContentStyled>
    </Dialog>
  );
}

export default connectItem(ListingViewDialog);
