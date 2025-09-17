/**
 * @type: itemView
 * name: user.itemView.inviteCard
 * chunkName: user
 */
import { useGlobal } from '@metafox/framework';
import { ButtonStyled } from '@metafox/ui';
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
  flexWrap: 'wrap',
  padding: theme.spacing(1, 2),
  boxShadow: 'none',
  ...(theme.palette.mode === 'light' && {
    backgroundColor: theme.layoutSlot.background.paper
  }),
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column'
  }
}));

const StyledTypography = styled(Typography)(({ theme }) => ({
  color: theme.palette.text.primary
}));

const InviteCard = ({ item, actions }) => {
  const { i18n } = useGlobal();

  return (
    <StyledCard>
      <Box sx={{ display: 'flex', alignItems: 'center' }}>
        <StyledTypography
          component="div"
          variant="h5"
          sx={{ marginBottom: 0.5 }}
        >
          {i18n.formatMessage(
            { id: 'user_has_sent_you_a_friend_request' },
            { name: item.full_name }
          )}
        </StyledTypography>
      </Box>
      <CardActions sx={{ paddingX: 0 }}>
        <Button
          size="small"
          variant="contained"
          onClick={actions.acceptFriend}
          component="h5"
        >
          {i18n.formatMessage({ id: 'accept_friend_request' })}
        </Button>
        <ButtonStyled
          size="small"
          variant="outlined"
          onClick={actions.denyFriend}
          component="h5"
        >
          {i18n.formatMessage({ id: 'delete_friend_request' })}
        </ButtonStyled>
      </CardActions>
    </StyledCard>
  );
};

export default InviteCard;
