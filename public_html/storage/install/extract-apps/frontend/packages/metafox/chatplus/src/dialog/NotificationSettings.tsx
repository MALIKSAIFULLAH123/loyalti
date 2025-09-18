/**
 * @type: dialog
 * name: dialog.chatplus.NotificationSettings
 */
import {
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle
} from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { TruncateText } from '@metafox/ui';
import { when } from '@metafox/utils';
import {
  Button,
  List,
  ListItem,
  ListItemText,
  MenuItem,
  Select,
  styled
} from '@mui/material';
import { isArray } from 'lodash';
import React, { useState } from 'react';
import { useSubscriptionItem, useUserPreferences } from '../hooks';
import { RoomType } from '../types';

interface Props {
  rid?: string;
}

const StyledListItem = styled(ListItem, { name: 'StyledListItem' })(
  ({ theme }) => ({
    borderBottom: 'solid 1px',
    borderBottomColor: theme.palette.border?.secondary,
    padding: '22px 0 !important',
    display: 'flex',
    justifyContent: 'space-between',
    '&:first-of-type': {
      paddingTop: 6
    },
    '&:last-child': {
      paddingBottom: 6,
      borderBottom: 'none'
    }
  })
);

const TruncateTextStyled = styled(TruncateText)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  flex: 1,
  minWidth: 0
}));

const SelectStyled = styled(Select)(({ theme }) => ({
  '& .MuiInputBase-input': {
    fontWeight: 'normal'
  }
}));

export type PropsSelectItem = {
  item: Record<string, any>;
  onChanged: (value: any, var_name: string) => void;
  disableNotifications?: any;
};

export function SelectItem({
  item,
  onChanged,
  disableNotifications
}: PropsSelectItem) {
  const [value, setValue] = React.useState<string>(item?.value);

  const handleChange = (evt: any) => {
    const newValue = evt.target.value as string;

    setValue(newValue);
    onChanged(newValue, item?.var_name);
  };

  const disableItem = item.disable
    ? when({ disableNotifications }, item.disable)
    : false;

  return (
    <StyledListItem>
      <ListItemText primary={item.phrase} sx={{ pr: 2 }} />
      <SelectStyled
        className="select-quick-sort"
        variant={'standard'}
        value={value}
        onChange={handleChange}
        disableUnderline
        disabled={disableItem}
      >
        {item?.options?.map((option, index) => (
          <MenuItem value={option.value} key={index}>
            {option.label}
          </MenuItem>
        ))}
      </SelectStyled>
    </StyledListItem>
  );
}

function NotificationSettings({ rid }: Props) {
  const { i18n, useDialog, useIsMobile } = useGlobal();
  const subscription = useSubscriptionItem(rid);

  const isMobile = useIsMobile();

  const preferences = useUserPreferences();
  const roomType = subscription.t || RoomType.Direct;
  const tmpMPNotification =
    roomType === RoomType.Direct &&
    (preferences?.mobilePushNotifications === 'mentions' ||
      subscription?.mobilePushNotifications === 'mentions')
      ? 'all'
      : subscription.mobilePushNotifications ||
        preferences?.mobilePushNotifications ||
        'all';
  const tmpAudioNotifications =
    roomType === RoomType.Direct &&
    (preferences?.audioNotifications === 'mentions' ||
      subscription.audioNotifications === 'mentions')
      ? 'all'
      : subscription.audioNotifications ||
        preferences?.audioNotifications ||
        'all';

  const { dialogProps, closeDialog, setDialogValue } = useDialog();
  const [disableNotifications, setDisableNotifications] = useState(
    subscription.disableNotifications ||
      preferences.disableNotifications ||
      false
  );
  const [mobilePushNotifications, setMobilePushNotifications] =
    useState(tmpMPNotification);
  const [audioNotifications, setAudioNotifications] = useState(
    tmpAudioNotifications
  );

  const optionNotification =
    roomType === RoomType.Direct
      ? [
          { label: i18n.formatMessage({ id: 'all_messages' }), value: 'all' },

          { label: i18n.formatMessage({ id: 'nothing' }), value: 'nothing' }
        ]
      : [
          { label: i18n.formatMessage({ id: 'all_messages' }), value: 'all' },
          {
            label: i18n.formatMessage({ id: 'mentions' }),
            value: 'mentions'
          },
          { label: i18n.formatMessage({ id: 'nothing' }), value: 'nothing' }
        ];

  const data = [
    {
      default_value: disableNotifications,
      ordering: 1,
      value: disableNotifications,
      phrase: i18n.formatMessage({ id: 'disable_notifications' }),
      var_name: 'disableNotifications',
      options: [
        { label: i18n.formatMessage({ id: 'yes' }), value: true },
        { label: i18n.formatMessage({ id: 'no' }), value: false }
      ]
    },
    {
      default_value: mobilePushNotifications,
      ordering: 2,
      value: mobilePushNotifications,
      phrase: i18n.formatMessage({ id: 'receive_mobile_notifications' }),
      var_name: 'mobilePushNotifications',
      options: optionNotification,
      disable: ['eq', 'disableNotifications', true]
    },
    {
      default_value: audioNotifications,
      ordering: 3,
      value: audioNotifications,
      phrase: i18n.formatMessage({ id: 'receive_sound_notifications' }),
      var_name: 'audioNotifications',
      options: optionNotification,
      disable: ['eq', 'disableNotifications', true]
    }
  ];

  const handleChangeSelect = (value: any, var_name: string) => {
    switch (var_name) {
      case 'mobilePushNotifications':
        setMobilePushNotifications(value);
        break;
      case 'audioNotifications':
        setAudioNotifications(value);
        break;
      case 'disableNotifications':
        setDisableNotifications(value);
        break;

      default:
        break;
    }
  };

  const handleCancel = () => {
    closeDialog();
  };

  const handleSubmit = () => {
    // Dont pass all value from preferences to here
    // When saving settings it does not unnecessarily duplicate data in subscription
    const initPre = {
      ignored: 1,
      audioNotifications: 'all',
      audioNotificationValue: 1,
      desktopNotificationDuration: 1,
      mobilePushNotifications: 'all',
      disableNotifications: false,
      userHighlights: 1,
      emailNotifications: preferences?.emailNotifications || 'mentions',
      desktopNotifications: preferences?.desktopNotifications || 'all',
      muteGroupMentions:
        roomType === RoomType.Direct && !!preferences?.muteGroupMentions
          ? false
          : preferences?.muteGroupMentions || false
    };

    const payload = {
      ...initPre,
      mobilePushNotifications,
      audioNotifications,
      disableNotifications
    };
    setDialogValue(payload);
  };

  if (isMobile) {
    return (
      <Dialog maxWidth="sm" fullWidth {...dialogProps}>
        <DialogTitle
          enableDone
          onDoneClick={handleSubmit}
          enableBack
          onBackClick={handleCancel}
          disableClose
        >
          <TruncateTextStyled
            lines={1}
            color="text.primary"
            variant="h2"
            fontSize={18}
          >
            {i18n.formatMessage({
              id: 'settings'
            })}
          </TruncateTextStyled>
        </DialogTitle>
        <DialogContent sx={{ p: '0 16px' }}>
          <List disablePadding>
            {isArray(data)
              ? data.map(menu => (
                  <SelectItem
                    key={menu?.var_name}
                    onChanged={handleChangeSelect}
                    item={menu}
                    disableNotifications={disableNotifications}
                  />
                ))
              : null}
          </List>
        </DialogContent>
      </Dialog>
    );
  }

  return (
    <Dialog maxWidth="sm" fullWidth {...dialogProps}>
      <DialogTitle disableClose={false}>
        {i18n.formatMessage({ id: 'settings' })}
      </DialogTitle>
      <DialogContent sx={{ p: '0 16px' }}>
        <List disablePadding>
          {isArray(data)
            ? data.map(menu => (
                <SelectItem
                  key={menu?.var_name}
                  onChanged={handleChangeSelect}
                  item={menu}
                  disableNotifications={disableNotifications}
                />
              ))
            : null}
        </List>
      </DialogContent>
      <DialogActions>
        <Button
          onClick={handleSubmit}
          color="primary"
          autoFocus
          variant="contained"
          children={i18n.formatMessage({ id: 'save' })}
        />
        <Button
          onClick={handleCancel}
          color="primary"
          autoFocus
          variant="outlined"
          children={i18n.formatMessage({ id: 'cancel' })}
        />
      </DialogActions>
    </Dialog>
  );
}

export default NotificationSettings;
