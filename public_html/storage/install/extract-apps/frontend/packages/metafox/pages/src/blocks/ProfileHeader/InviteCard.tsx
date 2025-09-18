/**
 * @type: itemView
 * name: page.itemView.inviteCard
 * chunkName: page
 */
import { getItemSelector, GlobalState, useGlobal } from '@metafox/framework';
import { ButtonStyled, UserAvatar } from '@metafox/ui';
import {
  Box,
  Button,
  Card,
  CardActions,
  styled,
  Typography
} from '@mui/material';
import React from 'react';
import { useSelector } from 'react-redux';

const StyledCard = styled(Card)(({ theme }) => ({
  marginTop: theme.spacing(2),
  display: 'flex',
  justifyContent: 'space-between',
  padding: theme.spacing(2),
  boxShadow: 'none',
  ...(theme.palette.mode === 'light' && {
    backgroundColor: theme.layoutSlot.background.paper
  })
}));

const StyledTypography = styled(Typography)(({ theme }) => ({
  color: theme.palette.text.primary
}));

const InviteCard = ({ item, actions }) => {
  const { i18n } = useGlobal();

  const invite = useSelector<GlobalState>(state =>
    getItemSelector(state, item?.invite)
  ) as any;

  const user = useSelector<GlobalState>(state =>
    getItemSelector(state, invite?.user)
  ) as any;

  if (!user || !invite) return null;

  const { invited_admin, expired_description } = invite;

  if (!invited_admin) return null;

  const messageInvite = 'person_who_invite_me_to_admin_page';

  return (
    <StyledCard>
      <Box sx={{ display: 'flex', alignItems: 'center' }}>
        <UserAvatar size={48} user={user} />
        <Box sx={{ paddingLeft: 2 }}>
          <StyledTypography
            component="div"
            variant="h5"
            sx={{ marginBottom: 0.5 }}
          >
            {i18n.formatMessage(
              { id: messageInvite },
              { value: user.full_name }
            )}
          </StyledTypography>
          {expired_description ? (
            <Typography component="span" variant="body2" color="text.hint">
              {expired_description}
            </Typography>
          ) : null}
        </Box>
      </Box>
      <CardActions sx={{ paddingX: 0 }}>
        <Button
          size="small"
          variant="contained"
          onClick={actions.acceptInvite}
          component="h5"
        >
          {i18n.formatMessage({ id: 'accept_invite' })}
        </Button>
        <ButtonStyled
          size="small"
          variant="outlined"
          onClick={actions.declineInvite}
          component="h5"
        >
          {i18n.formatMessage({ id: 'decline_invite' })}
        </ButtonStyled>
      </CardActions>
    </StyledCard>
  );
};

export default InviteCard;
