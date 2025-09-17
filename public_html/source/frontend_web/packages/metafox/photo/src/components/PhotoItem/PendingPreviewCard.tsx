/**
 * @type: itemView
 * name: photo.itemView.pendingReviewCard
 * chunkName: photo
 */
import { useGlobal } from '@metafox/framework';
import { Box, Button, Stack, Card, styled, Typography } from '@mui/material';
import React from 'react';

const StyledCard = styled(Card)(({ theme }) => ({
  display: 'flex',
  margin: theme.spacing(2),
  flexDirection: 'column',
  justifyContent: 'space-between',
  background:
    theme.palette.mode === 'light'
      ? theme.layoutSlot.background.paper
      : theme.palette.background.default,
  padding: theme.spacing(2),
  color: theme.palette.text.secondary,
  boxShadow: 'none'
}));

const StyledTypography = styled(Typography)(({ theme }) => ({}));

const PreviewPendingCard = ({ item }) => {
  const { i18n, dispatch } = useGlobal();

  if (!item?.is_pending) return null;

  const { extra } = item;

  const handleApprove = () => {
    dispatch({ type: 'approveItem', payload: { identity: item._identity } });
  };

  const handleDecline = () => {
    dispatch({ type: 'deleteItem', payload: { identity: item._identity } });
  };

  return (
    <StyledCard>
      <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
        <Box>
          <Typography variant="h5" color="text.primary">
            {i18n.formatMessage(
              { id: 'this_media_is_pending_state' },
              { value: item.module_name }
            )}
          </Typography>
          <StyledTypography variant="body2">
            {i18n.formatMessage(
              {
                id: 'contents_from_this_media_will_be_public_visible_after_admins_approve_it'
              },
              { value: item.module_name }
            )}
          </StyledTypography>
        </Box>
      </Box>
      <Stack spacing={1} alignItems="center" direction="row">
        {extra?.can_approve ? (
          <>
            <Button size="small" variant="contained" onClick={handleApprove}>
              {i18n.formatMessage({ id: 'approve' })}
            </Button>
            <Button size="small" variant="outlined" onClick={handleDecline}>
              {i18n.formatMessage({ id: 'decline' })}
            </Button>
          </>
        ) : null}
      </Stack>
    </StyledCard>
  );
};

export default PreviewPendingCard;
