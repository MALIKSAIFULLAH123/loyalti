import { usePublicSettings } from '@metafox/chatplus/hooks';
import { RoomType } from '@metafox/chatplus/types';
import { setLocalStatusCall } from '@metafox/chatplus/utils';
import { useGlobal } from '@metafox/framework';
import { StyledIconButton } from '@metafox/ui';
import { styled } from '@mui/material';
import React, { useEffect } from 'react';
import Avatar from '@metafox/chatplus/components/Avatar';

const UICallView = styled('div', { slot: 'uiCallView' })(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-around',
  alignItems: 'center',
  flexDirection: 'column',
  maxWidth: '100%',
  overflowX: 'hidden',
  boxSizing: 'border-box',
  position: 'fixed',
  left: 0,
  right: 0,
  top: 0,
  bottom: 0,

  backgroundColor: theme.palette.grey['800'],

  '& h1, h2, h3,h4, h5, h6': {
    color: 'white'
  },
  '& h4': {
    fontSize: theme.spacing(3.75),
    margin: theme.spacing(1)
  }
}));
const ItemTitle = styled('div')(({ theme }) => ({
  color: 'white',
  fontSize: theme.spacing(2)
}));
const InfoGroup = styled('div')(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  flexDirection: 'column'
}));
const IconButtonCancel = styled(StyledIconButton)(({ theme }) => ({
  width: '48px',
  height: '48px',
  borderRadius: '100%',
  padding: 0,
  margin: '30px 0',
  color: '#ffffff',
  cursor: 'pointer',
  backgroundColor: '#bf2117 !important',
  '&:hover': {
    backgroundColor: '#bf2117 !important'
  }
}));

interface IProps {
  callId?: string;
  callInfo?: any;
  cancelCall: () => void;
}

export default function OutgoingView({ callId, callInfo, cancelCall }: IProps) {
  const { i18n } = useGlobal();
  const settings = usePublicSettings();
  const { Website, Metafox_Ringtone_Sound_Url } = settings;
  const ringtoneSrc = `${Website}${Metafox_Ringtone_Sound_Url}`;

  useEffect(() => {
    setLocalStatusCall(callId, 'invite');
  }, [callId]);

  const { displayName, avatar, callType } = callInfo;
  const roomType = (callType && callType.split('_')[2]) || RoomType.Direct;

  return (
    <div>
      <UICallView>
        <ItemTitle>{i18n.formatMessage({ id: 'calling_dots' })}</ItemTitle>
        <InfoGroup>
          <Avatar
            src={avatar}
            size={100}
            name={displayName}
            username={displayName}
            roomType={roomType}
          />
          <h4>{displayName}</h4>
        </InfoGroup>
        <IconButtonCancel
          size="small"
          color="primary"
          icon="ico-phone-off"
          title={i18n.formatMessage({ id: 'end_call' })}
          onClick={cancelCall}
        />
        {ringtoneSrc && (
          <audio
            style={{ display: 'none' }}
            src={ringtoneSrc}
            loop
            autoPlay
            type="audio/mpeg"
          ></audio>
        )}
      </UICallView>
    </div>
  );
}
