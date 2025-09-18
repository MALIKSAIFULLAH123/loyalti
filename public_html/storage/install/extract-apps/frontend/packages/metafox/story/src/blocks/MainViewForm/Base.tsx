import { useGlobal } from '@metafox/framework';
import {
  APP_STORY,
  STATUS_PHOTO_STORY,
  STATUS_TEXT_STORY
} from '@metafox/story/constants';
import useAddFormContext from '@metafox/story/hooks';
import { filterShowWhen } from '@metafox/utils';
import { Box, styled } from '@mui/material';
import React from 'react';
import ButtonItem from './ButtonItem';

const name = 'MainViewForm';

const RootStyled = styled(Box, {
  name,
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  width: '100%',
  height: '100%',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column'
  }
}));

function Base() {
  const {
    useAppMenu,
    useIsMobile,
    useSession,
    getAcl,
    getSetting,
    jsxBackend
  } = useGlobal();
  const isMobile = useIsMobile(true);
  const session = useSession();
  const acl = getAcl();
  const setting = getSetting();
  const PreviewStoryPhoto = jsxBackend.get('story.block.storyReviewPhoto');
  const PreviewStoryBackground = jsxBackend.get(
    'story.block.storyReviewBackground'
  );
  const PreviewStoryPhotoMobile = jsxBackend.get(
    'story.block.storyReviewPhotoMobile'
  );
  const PreviewStoryBackgroundMobile = jsxBackend.get(
    'story.block.storyReviewBackgroundMobile'
  );

  const context = useAddFormContext();

  const menu = useAppMenu(APP_STORY, 'addStoryMenu');

  // filter based on showWhen property
  const items = filterShowWhen(menu.items, {
    session,
    acl,
    setting,
    isMobile
  });

  if (!items) return;

  if (context.status === STATUS_PHOTO_STORY) {
    return isMobile ? <PreviewStoryPhotoMobile /> : <PreviewStoryPhoto />;
  }

  if (context.status === STATUS_TEXT_STORY) {
    return isMobile ? (
      <PreviewStoryBackgroundMobile />
    ) : (
      <PreviewStoryBackground />
    );
  }

  return (
    <RootStyled isMobile={isMobile}>
      {items.map(item => (
        <ButtonItem key={item?.name} item={item} />
      ))}
    </RootStyled>
  );
}

export default Base;
