/**
 * @type: itemView
 * name: group.itemView.pendingReviewCard
 * chunkName: group
 */
import { useGlobal } from '@metafox/framework';
import {
  Box,
  Button,
  Card,
  CardActions,
  styled,
  Typography
} from '@mui/material';
import React from 'react';

const StyledCard = styled(Card)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  background:
    theme.palette.mode === 'light'
      ? theme.layoutSlot.background.paper
      : theme.palette.background.default,
  padding: theme.spacing(2),
  boxShadow: 'none'
}));

const StyledTypography = styled(Typography)(({ theme }) => ({
  color: theme.palette.text.secondary
}));

const PreviewPendingCard = ({ item, actions }) => {
  const { i18n, dispatch } = useGlobal();

  const handleManage = () => {
    dispatch({
      type: 'group/openMyManagePage',
      payload: { identity: item._identity, item }
    });
  };

  if (!item?.pending_post_count) return null;

  return (
    <StyledCard>
      <Box sx={{ display: 'flex', alignItems: 'center' }}>
        <Box sx={{ paddingLeft: 2 }}>
          <StyledTypography variant="body1">
            {i18n.formatMessage(
              { id: 'you_have_posts_that_pending' },
              { value: item.pending_post_count }
            )}
          </StyledTypography>
        </Box>
      </Box>
      <CardActions sx={{ paddingX: 0 }}>
        <Button
          size="small"
          variant="contained"
          component="h5"
          onClick={handleManage}
        >
          {i18n.formatMessage({ id: 'manage' })}
        </Button>
      </CardActions>
    </StyledCard>
  );
};

export default PreviewPendingCard;
