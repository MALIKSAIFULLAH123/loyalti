import {
  Panel,
  PanelHeader,
  PanelTitle,
  PanelToolbar
} from '@metafox/chat/components/DockPanel';
import { NEW_CHAT_ROOM } from '@metafox/chat/constants';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import SearchUsers from '../SearchUsers';
import actions from './actions';
import React from 'react';
// import SearchUsers from '../SearchUsers';

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
  const { i18n, useActionControl } = useGlobal();

  const [handleAction] = useActionControl<State, unknown>(NEW_CHAT_ROOM, {});

  const items = actions();

  return (
    <Panel>
      <WrapperPanelHeader>
        <PanelTitle status={0} variant="new_message">
          {i18n.formatMessage({ id: 'new_message' })}
        </PanelTitle>
        <PanelToolbar items={items} handleAction={handleAction} />
      </WrapperPanelHeader>
      <SearchUsers variant="direct" />
    </Panel>
  );
}

export default NewRoomPanel;
