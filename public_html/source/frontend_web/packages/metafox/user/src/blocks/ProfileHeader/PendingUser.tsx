import { useGlobal } from '@metafox/framework';
import { ButtonAction, LineIcon } from '@metafox/ui';
import {
  Box,
  Card,
  IconButton,
  styled,
  Typography,
  Stack
} from '@mui/material';
import React from 'react';

const StyledCard = styled(Card)(({ theme }) => ({
  display: 'flex',
  background:
    theme.palette.mode === 'dark'
      ? theme.palette.grey[800]
      : theme.palette.grey[100],
  padding: theme.spacing(2),
  color: theme.palette.text.secondary,
  boxShadow: 'none',
  [theme.breakpoints.down('sm')]: {
    flexWrap: 'wrap'
  }
}));

const StyledTypography = styled(Typography)(({ theme }) => ({}));

const PendingUser = ({ item }) => {
  const { i18n, dispatch } = useGlobal();

  if (!item?.is_pending) return null;

  const { extra, resource_name } = item;

  const handleApprove = onSuccess => {
    dispatch({
      type: 'approveItem',
      payload: { identity: item._identity },
      meta: { onSuccess }
    });
  };

  const handleDecline = cb => {
    dispatch({
      type: 'user/decline',
      payload: { identity: item._identity },
      meta: { onFinal: cb }
    });
  };

  return (
    <Box>
      <StyledCard>
        <Box>
          <IconButton size={'medium'}>
            <LineIcon icon="ico-clock-o" />
          </IconButton>
        </Box>
        <Box
          sx={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            flex: 1,
            flexWrap: 'wrap',
            rowGap: 1.5
          }}
        >
          <Box sx={{ paddingLeft: 2 }}>
            <Typography variant="h5" color="text.primary" paddingBottom={0.5}>
              {i18n.formatMessage(
                { id: 'this_app_is_pending_state' },
                {
                  value: i18n.formatMessage({
                    id: `resource_name_lower_case_${resource_name}`,
                    defaultMessage: resource_name
                  })
                }
              )}
            </Typography>
            <StyledTypography variant="body2">
              {i18n.formatMessage(
                {
                  id: 'contents_from_this_app_will_be_public_visible_after_admins_approve_it'
                },
                {
                  value: i18n.formatMessage({
                    id: `resource_name_lower_case_${resource_name}`,
                    defaultMessage: resource_name
                  })
                }
              )}
            </StyledTypography>
          </Box>
          <Stack spacing={1} alignItems="center" direction="row" ml={2}>
            {extra?.can_approve ? (
              <>
                <ButtonAction
                  size="small"
                  variant="contained"
                  action={handleApprove}
                >
                  {i18n.formatMessage({ id: 'approve' })}
                </ButtonAction>

                <ButtonAction
                  size="small"
                  variant="outlined"
                  action={handleDecline}
                >
                  {i18n.formatMessage({ id: 'decline' })}
                </ButtonAction>
              </>
            ) : null}
          </Stack>
        </Box>
      </StyledCard>
    </Box>
  );
};

export default PendingUser;
