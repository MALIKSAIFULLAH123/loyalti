import { useNewChatRoom, useSessionUser } from '@metafox/chatplus/hooks';
import { SuggestionItemShape } from '@metafox/chatplus/types';
import { GlobalState, useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import { connect } from 'react-redux';
import { Panel, PanelHeader, PanelTitle, PanelToolbar } from '../DockPanel';
import SearchUsers from '../SearchUsers';
import actions from './actions';

const name = 'NewRoomPanel';

const WrapperPanelHeader = styled(PanelHeader, {
  name,
  slot: 'WrapperPanelHeader'
})(({ theme }) => ({
  padding: theme.spacing(2, 0)
}));

interface State {}

interface Props {
  active: boolean;
  rid: string;
}

function NewRoomPanel(props: Props) {
  const { i18n, useActionControl, dispatch } = useGlobal();
  const [handleAction] = useActionControl<State, unknown>(props.rid, {});

  const { collapsed } = useNewChatRoom();
  const userSession = useSessionUser();

  const items = actions();
  const [users, setUsers] = React.useState<SuggestionItemShape[]>([]);

  const isSelfChat = React.useMemo(
    () => users.find(user => user.value === userSession?.username),
    [users, userSession]
  );

  React.useEffect(() => {
    if (isSelfChat) {
      dispatch({ type: 'chatplus/newChatRoom/submit', payload: { users } });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isSelfChat, users]);

  const onSubmit = React.useCallback(() => {
    const memberCount = users.length;

    if (memberCount) {
      dispatch({ type: 'chatplus/newChatRoom/submit', payload: { users } });
    }
  }, [dispatch, users]);

  return (
    <Panel>
      <WrapperPanelHeader>
        <PanelTitle status={0} variant="new_message">
          {i18n.formatMessage({ id: 'new_message' })}
        </PanelTitle>
        {collapsed ? null : (
          <PanelToolbar items={items} handleAction={handleAction} />
        )}
      </WrapperPanelHeader>
      <SearchUsers
        users={users}
        setUsers={setUsers}
        onSubmit={onSubmit}
        variant={users.length === 0 ? 'direct' : 'group'}
      />
    </Panel>
  );
}

const mapStateToProps = (state: GlobalState) => state.chatplus.newChatRoom;

export default connect(mapStateToProps)(NewRoomPanel);
