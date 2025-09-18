import { useGlobal } from '@metafox/framework';
import { useStoryViewContext } from '@metafox/story/hooks';
import { LineIcon } from '@metafox/ui';
import { Box, IconButton, Typography, styled } from '@mui/material';
import StatisticView from './StatisticView';
import SliderStory from './SliderStory';
import React from 'react';
import { StoryItemProps } from '@metafox/story/types';
import WapperItemInteraction from '../WapperItemInteraction';
import { camelCase } from 'lodash';

const name = 'ContentViewDetail';

const HeaderBlock = styled(Box, {
  name,
  slot: 'HeaderBlock',
  shouldForwardProp: props => props !== 'hideSlider'
})<{ hideSlider?: boolean }>(({ theme, hideSlider }) => ({
  padding: theme.spacing(2),
  paddingTop: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  ...(hideSlider && {
    borderBottom: theme.mixins.border('secondary')
  })
}));
const HeaderTitle = styled(Box, { name, slot: 'HeaderTitle' })(() => ({}));
const ContentBlock = styled(Box, { name, slot: 'ContentBlock' })(
  ({ theme }) => ({
    borderTop: theme.mixins.border('secondary'),
    borderBottom: theme.mixins.border('secondary'),
    overflow: 'hidden'
  })
);

const CloseButton = styled(IconButton, { name })(() => ({
  marginLeft: 'auto',
  transform: 'translate(4px,0)',
  position: 'absolute',
  right: '16px'
}));

interface Props {
  item: StoryItemProps;
  open: boolean;
  setOpen: any;
  isMinHeight?: boolean;
  hideSlider?: boolean;
}

const ContentDetailView = ({
  item,
  open,
  setOpen,
  isMinHeight = false,
  hideSlider = false
}: Props) => {
  const { i18n, useGetItems, useGetItem, useTheme } = useGlobal();
  const theme = useTheme();
  const { identityUserStoryActive } = useStoryViewContext();
  const userStoryActive = useGetItem(identityUserStoryActive);
  const stories = useGetItems(userStoryActive?.stories) as StoryItemProps[];

  const handleClose = () => {
    setOpen(false);
  };

  if (!open || !stories.length) return null;

  return (
    <WapperItemInteraction
      setOpen={setOpen}
      open={open}
      isMinHeight={isMinHeight}
      sxProps={{ padding: theme.spacing(2), paddingTop: theme.spacing(2.5) }}
      data-testid={camelCase('story viewers')}
    >
      <HeaderBlock hideSlider={hideSlider}>
        <HeaderTitle>
          <Typography variant="h4" color={'text.primary'}>
            {i18n.formatMessage({ id: 'story_viewers' })}
          </Typography>
        </HeaderTitle>
        <CloseButton
          size="small"
          onClick={handleClose}
          data-testid="buttonClose"
          role="button"
        >
          <LineIcon icon="ico-close" />
        </CloseButton>
      </HeaderBlock>
      {hideSlider ? null : (
        <ContentBlock>
          <SliderStory stories={stories} item={item} />
        </ContentBlock>
      )}
      <StatisticView item={item} />
    </WapperItemInteraction>
  );
};

export default ContentDetailView;
