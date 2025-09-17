/**
 * @type: ui
 * name: core.ui.iconCloseVideo
 */

import { useGetItem, useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, IconButton, Tooltip, styled } from '@mui/material';
import React from 'react';

const name = 'IconCloseVideo';

const IconButtonModal = styled(IconButton, { name, slot: 'iconClose' })(
  ({ theme }) => ({
    width: 32,
    height: 32,
    fontSize: theme.mixins.pxToRem(18)
  })
);

function IconCloseVideo() {
  const { i18n, useIsMobile, useDialog, location, usePageParams, navigate } =
    useGlobal();
  const pageParams = usePageParams();
  const { identity } = pageParams;
  const item = useGetItem(identity);
  const { closeDialog } = useDialog();

  const isMobile = useIsMobile(true);

  const handleClose = () => {
    if (location.state?.asModal) {
      closeDialog();
    } else {
      navigate(item?.owner_navigation_link);
    }
  };

  if (isMobile || !(location.state?.asModal || item?.owner_navigation_link))
    return null;

  return (
    <Box>
      <Tooltip title={i18n.formatMessage({ id: 'close' })}>
        <IconButtonModal onClick={handleClose}>
          <LineIcon icon="ico-close" />
        </IconButtonModal>
      </Tooltip>
    </Box>
  );
}

export default IconCloseVideo;
