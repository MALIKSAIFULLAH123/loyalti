import { BlockViewProps, useGlobal } from '@metafox/framework';
import { AddFormContext } from '@metafox/story/context';
import { Box, styled } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';

export interface Props extends BlockViewProps {}

const name = 'AddForm';
const Root = styled(Box, {
  name,
  slot: 'Root',
  overridesResolver: (props, styles) => [styles.root]
})(({ theme }) => ({
  display: 'flex',
  width: '100%',
  height: '100%'
}));

const SideBarWrapper = styled(Box, {
  name,
  slot: 'SideBarWrapper',
  overridesResolver: (props, styles) => [styles.buddyWrap]
})(({ theme }) => ({
  width: '360px',
  backgroundColor: theme.palette.background.paper
}));

const ContainerView = styled(Box, {
  name,
  slot: 'room-wrap'
})(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',

  margin: theme.spacing(3)
}));

export default function Base(props: Props) {
  const { jsxBackend } = useGlobal();
  const SidebarAddStory = jsxBackend.get('story.block.sidebarAddStory');
  const SideBarForm = jsxBackend.get('story.block.sideBarForm');
  const MainViewForm = jsxBackend.get('story.block.mainViewForm');
  const SideAppHeader = jsxBackend.get('story.block.sideStoryHeader');

  const [status, setStatus] = React.useState();
  const [filePhoto, setFilePhoto] = React.useState();
  const [uploading, setUploading] = React.useState(false);
  const [init, setInit] = React.useState(false);

  return (
    <AddFormContext.Provider
      value={{
        init,
        setInit,
        status,
        setStatus,
        setFilePhoto,
        filePhoto,
        uploading,
        setUploading
      }}
    >
      <Root>
        <SideBarWrapper>
          {init ? (
            <SideBarForm status={status} title={'create_story'} />
          ) : (
            <>
              <SideAppHeader />
              <SidebarAddStory />
            </>
          )}
        </SideBarWrapper>
        <ContainerView data-testid={camelCase('Main View Form')}>
          <MainViewForm />
        </ContainerView>
      </Root>
    </AddFormContext.Provider>
  );
}
