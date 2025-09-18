/**
 * @type: dialog
 * name: dialog.IncommingCallPopup
 */
import {
  useCallItem,
  usePublicSettings,
  useSessionUser
} from '@metafox/chatplus/hooks';
import { RoomType } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  styled
} from '@mui/material';
import React, { useEffect } from 'react';
import Avatar from '@metafox/chatplus/components/Avatar';

const name = 'IncomingCallPopup';

const ActionWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  justifyContent: 'flex-end',
  alignItems: 'center'
}));
const ButtonCancel = styled(Button)(({ theme }) => ({
  marginRight: theme.spacing(1)
}));
const UIChatCallInfo = styled('div', { name, slot: 'uiChat_call-info' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center'
  })
);
const UserCallingYou = styled('div', { name, slot: 'UserCallingYou-info' })(
  ({ theme }) => ({
    marginLeft: theme.spacing(1),
    ...theme.typography.body1
  })
);

function IncomingCallPopup({ callId }) {
  const { useDialog, i18n, dispatch } = useGlobal();
  const { dialogProps, closeDialog } = useDialog();
  const userSession = useSessionUser();

  const callInfo = useCallItem(callId);
  const settings = usePublicSettings();
  const { Website, Metafox_Ringtone_Sound_Url } = settings;
  const ringtoneSrc = `${Website}${Metafox_Ringtone_Sound_Url}`;

  const { subject, displayName, avatar, group, callStatus, callType, userId } =
    callInfo;
  const roomType = (callType && callType.split('_')[2]) || RoomType.Direct;

  useEffect(() => {
    if (
      callStatus === 'end' ||
      (callStatus === 'start' && userId === userSession?._id)
    ) {
      closeDialog();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [callStatus, userId, userSession?._id]);

  const handleReject = () => {
    dispatch({
      type: 'chatplus/room/rejectCallFromPopup',
      payload: { callId }
    });
    closeDialog();
  };

  const handleAcceptCall = () => {
    dispatch({
      type: 'chatplus/room/acceptCallFromPopup',
      payload: { callId }
    });
    closeDialog();
  };

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <DialogTitle>
        {group ? subject : i18n.formatMessage({ id: subject })}
      </DialogTitle>
      <DialogContent>
        <UIChatCallInfo>
          <Avatar
            size={40}
            src={avatar}
            name={displayName}
            username={displayName}
            roomType={roomType}
          />
          <UserCallingYou>
            {i18n.formatMessage(
              { id: 'user_is_calling_you' },
              { user: <b>{displayName}</b> }
            )}
            .
          </UserCallingYou>
          {ringtoneSrc && (
            <audio
              style={{ display: 'none' }}
              src={ringtoneSrc}
              loop
              autoPlay
              type="audio/mpeg"
            ></audio>
          )}
        </UIChatCallInfo>
      </DialogContent>
      <DialogActions>
        <ActionWrapper>
          <ButtonCancel variant="outlined" onClick={handleReject} size="small">
            {i18n.formatMessage({ id: 'reject' })}
          </ButtonCancel>
          <Button
            variant="contained"
            onClick={handleAcceptCall}
            size="small"
            color="primary"
          >
            {i18n.formatMessage({ id: 'accept' })}
          </Button>
        </ActionWrapper>
      </DialogActions>
    </Dialog>
  );
}

export default IncomingCallPopup;
