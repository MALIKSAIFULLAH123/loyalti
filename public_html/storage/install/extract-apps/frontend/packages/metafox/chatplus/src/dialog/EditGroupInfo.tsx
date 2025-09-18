/**
 * @type: dialog
 * name: dialog.chatplus.EditGroupInfo
 */
import {
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle
} from '@metafox/dialog';
import { GlobalState, useGlobal } from '@metafox/framework';
import { ButtonAction, LineIcon } from '@metafox/ui';
import { shortenFileName, parseFileSize } from '@metafox/utils';
import { Box, FormHelperText, styled, TextField } from '@mui/material';
import React from 'react';
import { useSelector } from 'react-redux';
import Avatar from '@metafox/chatplus/components/Avatar';
import { usePublicSettings } from '../hooks';
import { getBuddyItem } from '../selectors';
import { RoomItemShape } from '../types';
import { CheckboxLabels } from './MultipleConverationPicker';
import { isEmpty } from 'lodash';
import HtmlViewer from '@metafox/html-viewer';
import { MAX_LENGTH_NAME_GROUP } from '../constants';

const WrapperAvatar = styled('div', { name: 'WrapperAvatar' })(({ theme }) => ({
  marginBottom: theme.spacing(2.5)
}));
const EditAvatarButton = styled('div', { name: 'EditAvatarButton' })(
  ({ theme }) => ({
    width: 100,
    height: 100,
    borderRadius: 100,
    position: 'relative',
    '&:hover': {
      opacity: 0.47
    },
    '&:hover .ico': {
      visibility: 'visible'
    }
  })
);
const LineIconCamera = styled(LineIcon, { name: 'LineIconCamera' })(
  ({ theme }) => ({
    fontSize: 30,
    position: 'absolute',
    left: '50%',
    top: '50%',
    transform: 'translate(-50%,-50%)',
    visibility: 'hidden',
    zIndex: 99
  })
);
const WrapperInput = styled(Box, { name: 'WrapperInput' })(({ theme }) => ({
  marginBottom: theme.spacing(2)
}));

interface Props {
  title: string;
  rid: string;
  allowEditing: boolean;
  room?: RoomItemShape;
}

function Description({ text, sx, error = false }: any) {
  if (!text) return null;

  return (
    <FormHelperText sx={sx} error={error}>
      <HtmlViewer html={text} />
    </FormHelperText>
  );
}

export default function EditGroupInfo(props: Props) {
  const { title, allowEditing, rid, room } = props;
  const { i18n, useDialog, dispatch, dialogBackend } = useGlobal();
  const { dialogProps, closeDialog } = useDialog();

  const isReadOnlyProp = room?.ro || false;
  const isPublicProp = room?.t === 'c' || false;
  const nameRoom = room?.fname || '';
  const roomTopic = room?.topic || '';
  const roomDescription = room?.description || '';
  const roomAnnouncement = room?.announcement || '';
  const [readonly, setReadonly] = React.useState<boolean>(isReadOnlyProp);
  const [isPublic, setIsPublic] = React.useState<boolean>(isPublicProp);
  const [inputName, setInputName] = React.useState<string>(nameRoom);
  const [inputTopic, setInputTopic] = React.useState<string>(roomTopic);
  const [loadingSubmit, setLoadingSubmit] = React.useState(false);
  const [inputDescription, setInputDescription] =
    React.useState<string>(roomDescription);
  const [inputAnnouncement, setInputAnnouncement] =
    React.useState<string>(roomAnnouncement);
  const [roomAvatar, setRoomAvatar] = React.useState<string>('');

  const fileUploadRef = React.useRef<HTMLInputElement>();

  const buddy = useSelector((state: GlobalState) => getBuddyItem(state, rid));
  const settings = usePublicSettings();

  let helperNameError: any = null;

  const handleClick = () => {
    if (fileUploadRef && fileUploadRef.current) {
      fileUploadRef.current.click();
    }
  };

  const fileUploadChanged = evt => {
    evt.preventDefault();
    evt.stopPropagation();
    const input = fileUploadRef.current;

    if (!input || !input.files?.length) return;

    const file = input.files[0];
    const FR = new FileReader();
    let base64 = '';
    const RoomAvatarUpload_MaxFileSize =
      settings?.RoomAvatarUpload_MaxFileSize || 8000;

    FR.addEventListener('load', e => {
      base64 = e.target.result;
      const fileSize = file?.size;
      const fileName = file.name;

      if (
        fileSize &&
        fileSize < RoomAvatarUpload_MaxFileSize &&
        RoomAvatarUpload_MaxFileSize !== 0
      ) {
        if (!base64) return;

        setRoomAvatar(base64);
      } else {
        dialogBackend.alert({
          message: i18n.formatMessage(
            { id: 'warning_upload_limit_one_file' },
            {
              fileName: shortenFileName(fileName, 30),
              fileSize: parseFileSize(file.size),
              maxSize: parseFileSize(RoomAvatarUpload_MaxFileSize)
            }
          )
        });
      }
    });

    FR.readAsDataURL(file);
  };

  const handleCancel = onSuccess => {
    closeDialog();
    onSuccess();
  };

  const omitSettings = (originValues, newValues) => {
    const settings = {};

    Object.keys(originValues).forEach(key => {
      if (newValues[key] !== originValues[key]) {
        settings[key] =
          typeof newValues[key] === 'string'
            ? newValues[key].trim()
            : newValues[key];
      }
    });

    return settings;
  };

  const originValues = {
    roomAvatar: '',
    readOnly: isReadOnlyProp,
    roomType: isPublicProp ? 'c' : 'p',
    roomName: nameRoom,
    roomTopic,
    roomDescription,
    roomAnnouncement
  };

  const newValue = {
    roomAvatar,
    readOnly: readonly,
    roomType: isPublic ? 'c' : 'p',
    roomName: inputName,
    roomTopic: inputTopic,
    roomDescription: inputDescription,
    roomAnnouncement: inputAnnouncement
  };

  const settingValues: any = omitSettings(originValues, newValue);

  const handleSubmit = onSuccess => {
    if (isEmpty(inputName)) {
      helperNameError = <Description text="Group Name is required" error />;

      return;
    }

    setLoadingSubmit(true);
    dispatch({
      type: 'chatplus/room/editSettings',
      payload: { identity: room?.id, value: settingValues },
      meta: {
        onSuccess: () => {
          closeDialog();
          onSuccess();
          setLoadingSubmit(false);
        },
        onFailure: () => {
          onSuccess();
          setLoadingSubmit(false);
        }
      }
    });
  };

  if (isEmpty(inputName)) {
    helperNameError = (
      <Description
        text={i18n.formatMessage({ id: 'group_name_is_required' })}
        error
      />
    );
  }

  const haveError = React.useMemo(() => {
    return Boolean(isEmpty(inputName));
  }, [inputName]);

  const handleBlur = () => {
    setInputName(inputName?.trim());
  };

  return (
    <Dialog maxWidth="sm" fullWidth {...dialogProps}>
      <DialogTitle disableClose={false}>
        {title || i18n.formatMessage({ id: 'room_info' })}
      </DialogTitle>
      <DialogContent sx={{ py: 2, px: 3 }}>
        <WrapperAvatar>
          {allowEditing ? (
            <EditAvatarButton onClick={handleClick}>
              <Avatar
                name={buddy?.name}
                username={buddy?.name}
                src={roomAvatar || buddy?.avatar}
                size={100}
                room={room}
                uploadLocal={!!roomAvatar}
              />
              <LineIconCamera icon="ico-camera" />
            </EditAvatarButton>
          ) : (
            <Avatar
              name={buddy?.name}
              username={buddy?.name}
              size={100}
              src={buddy?.avatar}
              room={room}
            />
          )}
          <input
            accept="image/*"
            style={{ display: 'none' }}
            type="file"
            ref={fileUploadRef}
            onChange={fileUploadChanged}
          />
        </WrapperAvatar>
        <WrapperInput>
          <TextField
            disabled={!allowEditing}
            defaultValue={inputName}
            value={inputName}
            error={haveError}
            onChange={evt => {
              setInputName(evt.currentTarget.value);
            }}
            onBlur={handleBlur}
            fullWidth
            required
            variant="outlined"
            placeholder={i18n.formatMessage({ id: 'name' })}
            label={i18n.formatMessage({ id: 'channel_name' })}
            helperText={helperNameError}
            inputProps={{ maxLength: MAX_LENGTH_NAME_GROUP }}
          />
        </WrapperInput>

        {!allowEditing && !roomTopic ? null : (
          <WrapperInput>
            {allowEditing ? (
              <TextField
                disabled={!allowEditing}
                defaultValue={inputTopic}
                value={inputTopic}
                onChange={evt => setInputTopic(evt.currentTarget.value)}
                fullWidth
                variant="outlined"
                placeholder={i18n.formatMessage({ id: 'topic' })}
                label={i18n.formatMessage({ id: 'topic' })}
              />
            ) : (
              <TextField
                disabled
                defaultValue={
                  roomTopic ? roomTopic : i18n.formatMessage({ id: 'empty' })
                }
                fullWidth
                variant="outlined"
                label={i18n.formatMessage({ id: 'topic' })}
              />
            )}
          </WrapperInput>
        )}
        {!allowEditing && !roomDescription ? null : (
          <WrapperInput>
            {allowEditing ? (
              <TextField
                multiline
                rows={2}
                disabled={!allowEditing}
                defaultValue={inputDescription}
                value={inputDescription}
                onChange={evt => setInputDescription(evt.currentTarget.value)}
                fullWidth
                variant="outlined"
                placeholder={i18n.formatMessage({ id: 'description' })}
                label={i18n.formatMessage({ id: 'description' })}
              />
            ) : (
              <TextField
                multiline
                rows={4}
                disabled
                value={
                  roomDescription
                    ? roomDescription
                    : i18n.formatMessage({ id: 'empty' })
                }
                fullWidth
                variant="outlined"
                label={i18n.formatMessage({ id: 'description' })}
              />
            )}
          </WrapperInput>
        )}
        {!allowEditing && !roomAnnouncement ? null : (
          <WrapperInput>
            {allowEditing ? (
              <TextField
                multiline
                rows={2}
                disabled={!allowEditing}
                defaultValue={inputAnnouncement}
                value={inputAnnouncement}
                onChange={evt => setInputAnnouncement(evt.currentTarget.value)}
                fullWidth
                variant="outlined"
                placeholder={i18n.formatMessage({ id: 'announcement' })}
                label={i18n.formatMessage({ id: 'announcement' })}
              />
            ) : (
              <TextField
                multiline
                rows={4}
                disabled
                value={
                  roomAnnouncement
                    ? roomAnnouncement
                    : i18n.formatMessage({ id: 'empty' })
                }
                fullWidth
                variant="outlined"
                label={i18n.formatMessage({ id: 'announcement' })}
              />
            )}
          </WrapperInput>
        )}
        <Box>
          <CheckboxLabels
            allowEditing={allowEditing}
            readonly={readonly}
            isPublic={isPublic}
            setReadonly={setReadonly}
            setIsPublic={setIsPublic}
          />
        </Box>
      </DialogContent>
      {allowEditing ? (
        <DialogActions>
          <ButtonAction
            disabled={haveError || isEmpty(settingValues)}
            action={handleSubmit}
            color="primary"
            variant="contained"
            children={i18n.formatMessage({ id: 'save' })}
          />
          <ButtonAction
            disabled={loadingSubmit}
            action={handleCancel}
            color="primary"
            variant="outlined"
            children={i18n.formatMessage({ id: 'cancel' })}
          />
        </DialogActions>
      ) : null}
    </Dialog>
  );
}
