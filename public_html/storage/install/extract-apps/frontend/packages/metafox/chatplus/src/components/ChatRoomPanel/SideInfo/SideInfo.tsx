import {
  usePublicSettings,
  useRoomPermission,
  useSessionUser,
  useUsers
} from '@metafox/chatplus/hooks';
import {
  BuddyItemShape,
  RoomItemShape,
  RoomType,
  UserShape
} from '@metafox/chatplus/types';
import { BlockViewProps, useGlobal } from '@metafox/framework';
import { LineIcon, TruncateText } from '@metafox/ui';
import { filterShowWhen, shortenFileName, parseFileSize } from '@metafox/utils';
import { Box, Button, styled, Tab, Tabs, Tooltip } from '@mui/material';
import React from 'react';
import Avatar from '../../Avatar';
import FileList from './FileList';
import MediaList from './MediaList';
import MemberList from './MemberList';
import TabPanel, { a11yProps } from './TabPanel';

const name = 'sideInfo';

const Root = styled('div', { name })(({ theme }) => ({
  height: '100%',
  backgroundColor: theme.palette.background.paper,
  position: 'relative',
  borderLeft: theme.mixins.border('divider'),
  display: 'flex',
  flexDirection: 'column'
}));
const CollapseIcon = styled(Button)(({ theme }) => ({
  position: 'absolute',
  top: 16,
  right: 16,
  fontSize: theme.spacing(2.25),
  color: theme.palette.grey['600']
}));
const Header = styled('div', { name })(({ theme }) => ({
  boxSizing: 'border-box',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  flexDirection: 'column',
  paddingTop: theme.spacing(5)
}));
const WrapperTitle = styled('div')(({ theme }) => ({
  padding: theme.spacing(1.75, 0),
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  flexDirection: 'column'
}));
const TitleName = styled(TruncateText)(({ theme }) => ({
  marginTop: theme.spacing(2),
  textAlign: 'center',
  padding: theme.spacing(0, 1)
}));
const TextMore = styled('span', {
  shouldForwardProp: props => props !== 'bio'
})<{ bio?: any }>(({ theme, bio }) => ({
  ...theme.typography.body1,
  marginTop: theme.spacing(1),
  ...(bio && {
    color: theme.palette.text.hint
  })
}));

const Content = styled('div', { name })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  height: '100%',
  backgroundColor: theme.palette.background.paper,
  flex: 1,
  overflow: 'auto',
  marginTop: theme.spacing(3),
  '& .MuiTabs-root': {
    overflow: 'unset !important',
    '& .MuiTabs-scroller': {
      overflowX: 'auto !important'
    }
  }
}));

const ButtonView = styled(Button)(({ theme }) => ({
  fontSize: theme.spacing(2),
  width: '90%'
}));
const TabStyled = styled(Tab)(({ theme }) => ({
  ...theme.typography.h5
}));

export interface Props extends BlockViewProps {}
interface IProps {
  toggleInfo: () => void;
  room: RoomItemShape;
  buddy: BuddyItemShape;
}

export default function Block(props: Props & IProps) {
  const { toggleInfo, buddy, room } = props;
  const { i18n, dispatch, dialogBackend, navigate, useIsMobile } = useGlobal();

  const isMobile = useIsMobile(true);

  const perms = useRoomPermission(room?.id);
  const settings = usePublicSettings();
  const userSession = useSessionUser();
  const users = useUsers();

  const directChat = room?.t === 'd';

  const [tab, setTab] = React.useState<number>(0);
  const [roomAvatar, setRoomAvatar] = React.useState<string>(null);
  const fileUploadRef = React.useRef<HTMLInputElement>();
  const [memberCount, setMemberCount] = React.useState(0);

  const getUser = () => {
    try {
      if (room && room.t === RoomType.Direct && room.usersCount === 1) {
        return userSession;
      }

      if (room && room.t === RoomType.Direct && room.usersCount === 2) {
        const idUser = room?.uids?.find(uid => uid !== userSession?._id);

        if (!idUser) return null;

        const user: UserShape | number = Object.values(users).find(
          user => user._id === idUser
        );

        return user ? user : null;
      }
    } catch (err) {
      return null;
    }
  };

  const user = getUser();

  const name = React.useMemo(() => {
    let result = i18n.formatMessage({ id: 'name' });

    if (room?.isNameChanged) return buddy?.name || result;

    const nameSplit = buddy?.name.split(',') || '';

    if (buddy?.name && nameSplit?.length > 1) {
      result = `${nameSplit[0]} +${nameSplit?.length - 1}`;
    } else {
      result = buddy?.name;
    }

    return result;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [buddy?.name, room?.isNameChanged]);

  React.useEffect(() => {
    setRoomAvatar(null);
  }, [room]);

  const handleChangeTab = (event: React.SyntheticEvent, newValue: number) => {
    setTab(newValue);
  };

  const handleClick = React.useCallback(() => {
    if (directChat && user) {
      window.open(`/${user.username}`, '_blank', 'noreferrer');

      return;
    }

    if (!perms['edit-room']) return;

    if (fileUploadRef && fileUploadRef.current) {
      fileUploadRef.current.click();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [navigate, buddy, directChat, fileUploadRef, perms, user]);

  const fileUploadChanged = evt => {
    evt.preventDefault();
    evt.stopPropagation();
    const input = fileUploadRef.current;

    if (!input || !input.files?.length) return;

    const file = input.files[0];
    const FR = new FileReader();
    let base64: string = '';
    const RoomAvatarUpload_MaxFileSize =
      settings?.RoomAvatarUpload_MaxFileSize || 8000;

    // FR.onloadstart = () => {
    //   setLoading(true);
    // };

    // FR.onloadend = () => {
    //   setLoading(false);
    // };

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

        dispatch({
          type: 'chatplus/room/editSettings',
          payload: { identity: room?.id, value: { roomAvatar: base64 } }
        });
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

  const tabsData: any = [
    {
      label: i18n.formatMessage({ id: 'members' }).toUpperCase(),
      content: {
        component: <MemberList room={room} setMemberCount={setMemberCount} />
      },
      showWhen: ['neq', 'room.t', 'd']
    },
    {
      label: i18n.formatMessage({ id: 'files' }).toUpperCase(),
      query: { typeGroup: { $nin: ['image', 'video'] } },
      type: 'other',
      countLoad: 18,
      content: { component: <FileList room={room} /> },
      emptyPhrase: i18n.formatMessage({ id: 'no_files' }),
      emptyPhraseSub: directChat
        ? i18n.formatMessage(
            { id: 'no_files_subtext' },
            {
              name: buddy?.name
            }
          )
        : i18n.formatMessage({ id: 'no_files_group_subtext' })
    },
    {
      label: i18n.formatMessage({ id: 'media' }).toUpperCase(),
      query: { typeGroup: { $in: ['image', 'video'] } },
      type: 'media',
      countLoad: 18,
      content: { component: <MediaList room={room} /> },
      emptyPhrase: i18n.formatMessage({ id: 'no_media' }),
      emptyPhraseSub: directChat
        ? i18n.formatMessage(
            { id: 'no_medias_subtext' },
            {
              name: buddy?.name
            }
          )
        : i18n.formatMessage({ id: 'no_medias_group_subtext' })
    }
  ];

  const tabs = React.useMemo(
    () =>
      filterShowWhen(tabsData, {
        room
      }),
    [room]
  );

  return (
    <Root>
      <Tooltip title={i18n.formatMessage({ id: 'close' })} placement="top">
        <CollapseIcon onClick={toggleInfo}>
          {isMobile ? (
            <LineIcon icon="ico-close" />
          ) : (
            <LineIcon icon="ico-goright" />
          )}
        </CollapseIcon>
      </Tooltip>

      <Header>
        <WrapperTitle>
          <Avatar
            name={buddy?.name}
            username={buddy?.username}
            src={roomAvatar || buddy?.avatar}
            size={80}
            room={room}
            uploadLocal={!!roomAvatar}
            avatarETag={buddy?.avatarETag}
          />

          <TitleName showFull variant={name?.length < 100 ? 'h3' : 'h4'}>
            {name}
          </TitleName>
          <TextMore bio={user?.bio}>
            {directChat
              ? user?.bio
              : i18n.formatMessage(
                  { id: 'total_user_member' },
                  { value: memberCount }
                )}
          </TextMore>
        </WrapperTitle>
        <input
          accept="image/*"
          style={{ display: 'none' }}
          type="file"
          ref={fileUploadRef}
          onChange={fileUploadChanged}
        />
        {directChat && !room?.isBotRoom && (
          <ButtonView variant="outlined" onClick={handleClick}>
            {i18n.formatMessage({ id: 'view_main_profile' })}
          </ButtonView>
        )}
        {!directChat && perms['edit-room'] && (
          <ButtonView variant="outlined" onClick={handleClick}>
            {i18n.formatMessage({ id: 'change_group_image' })}
          </ButtonView>
        )}
      </Header>
      {room?.isBotRoom ? null : (
        <Content>
          <Tabs value={tab} onChange={handleChangeTab}>
            {tabs.map((tab, key) => (
              <TabStyled key={key} label={tab.label} {...a11yProps(key)} />
            ))}
          </Tabs>

          <Box sx={{ flex: 1, minHeight: 0 }}>
            {tabs.map((item, key) => (
              <TabPanel key={key} value={tab} index={key} {...item}>
                {item?.content?.component}
              </TabPanel>
            ))}
          </Box>
        </Content>
      )}
    </Root>
  );
}
