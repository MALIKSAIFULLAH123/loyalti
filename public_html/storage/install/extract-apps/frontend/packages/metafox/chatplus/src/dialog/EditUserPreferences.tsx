/**
 * @type: dialog
 * name: chatplus.dialog.EditUserPreferences
 */
import {
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle
} from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { TruncateText } from '@metafox/ui';
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
import { useUserPreferences } from '../hooks';

interface Props {}

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
};

export function SelectItem({ item, onChanged }: PropsSelectItem) {
  const [value, setValue] = React.useState<string>(item?.value);

  const handleChange = (evt: any) => {
    const newValue = evt.target.value as string;

    setValue(newValue);
    onChanged(newValue, item?.var_name);
  };

  return (
    <StyledListItem>
      <ListItemText primary={item.phrase} sx={{ pr: 2 }} />
      <SelectStyled
        className="select-quick-sort"
        variant={'standard'}
        value={value}
        onChange={handleChange}
        disableUnderline
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

function EditUserPreferences(props: Props) {
  const { i18n, useDialog, dispatch, useIsMobile } = useGlobal();

  const isMobile = useIsMobile();

  const preferences = useUserPreferences();

  const { dialogProps, closeDialog } = useDialog();
  const [mobileNotifications, setMobileNotifications] = useState(
    preferences?.mobileNotifications ?? 'all'
  );
  const [audioNotifications, setAudioNotifications] = useState(
    preferences?.audioNotifications ?? 'all'
  );
  // const [allowMessageFrom, setAllowMessageFrom] = useState(
  //   preferences?.allowMessageFrom ?? 'all'
  // );

  const data = [
    {
      default_value: mobileNotifications,
      ordering: 1,
      value: mobileNotifications,
      phrase: i18n.formatMessage({ id: 'receive_mobile_notifications' }),
      var_name: 'mobileNotifications',
      options: [
        { label: i18n.formatMessage({ id: 'all_messages' }), value: 'all' },
        { label: i18n.formatMessage({ id: 'mentions' }), value: 'mentions' },
        { label: i18n.formatMessage({ id: 'nothing' }), value: 'nothing' }
      ]
    },
    {
      default_value: audioNotifications,
      ordering: 2,
      value: audioNotifications,
      phrase: i18n.formatMessage({ id: 'receive_sound_notifications' }),
      var_name: 'audioNotifications',
      options: [
        { label: i18n.formatMessage({ id: 'all_messages' }), value: 'all' },
        { label: i18n.formatMessage({ id: 'mentions' }), value: 'mentions' },
        { label: i18n.formatMessage({ id: 'nothing' }), value: 'nothing' }
      ]
    }
    // {
    //   default_value: allowMessageFrom,
    //   ordering: 3,
    //   value: allowMessageFrom,
    //   phrase: i18n.formatMessage({ id: 'allow_messages_from' }),
    //   var_name: 'allowMessageFrom',
    //   options: [
    //     { label: i18n.formatMessage({ id: 'anyone' }), value: 'all' },
    //     { label: i18n.formatMessage({ id: 'no_one' }), value: 'noone' }
    //   ]
    // }
  ];

  const handleChangeSelect = (value: any, var_name: string) => {
    switch (var_name) {
      case 'mobileNotifications':
        setMobileNotifications(value);
        break;
      case 'audioNotifications':
        setAudioNotifications(value);
        break;
      // case 'allowMessageFrom':
      //   setAllowMessageFrom(value);
      //   break;

      default:
        break;
    }
  };

  const handleCancel = () => {
    closeDialog();
  };

  const handleSubmit = () => {
    const payload = {
      ...preferences,
      mobileNotifications,
      audioNotifications
      // allowMessageFrom
    };

    dispatch({
      type: 'chatplus/room/saveUserPreferences',
      payload,
      meta: { onSuccess: () => closeDialog() }
    });
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
                />
              ))
            : null}
        </List>
      </DialogContent>
      <DialogActions sx={{ mt: 0.5 }}>
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

export default EditUserPreferences;
