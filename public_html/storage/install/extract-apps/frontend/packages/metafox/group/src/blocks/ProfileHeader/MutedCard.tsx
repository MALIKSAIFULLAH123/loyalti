import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, Card, IconButton, styled, Typography } from '@mui/material';
import React from 'react';

const StyledCard = styled(Card)(({ theme }) => ({
  display: 'flex',
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

const MutedCard = ({ item }) => {
  const { i18n } = useGlobal();

  if (!item?.is_muted) return null;

  return (
    <StyledCard>
      <Box sx={{ display: 'flex', alignItems: 'center' }}>
        <Box>
          <IconButton size={'medium'}>
            <LineIcon icon="ico-volume-del-o" />
          </IconButton>
        </Box>
        <Box sx={{ paddingLeft: 2 }}>
          <Typography variant="h5" color="text.primary" paddingBottom={0.5}>
            {i18n.formatMessage({ id: 'you_have_been_muted_in_this_group' })}
          </Typography>
          <StyledTypography variant="body2">
            {i18n.formatMessage({
              id: 'your_can_still_access_its_post_and_chat'
            })}
          </StyledTypography>
        </Box>
      </Box>
    </StyledCard>
  );
};

export default MutedCard;
