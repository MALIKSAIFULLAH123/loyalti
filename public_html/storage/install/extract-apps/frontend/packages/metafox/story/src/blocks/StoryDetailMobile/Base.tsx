import { connectItem, useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import * as React from 'react';
import { ItemInteractionModal } from '@metafox/story/components';
import { Box, styled, useMediaQuery } from '@mui/material';
import { useGetSizeContainer } from '@metafox/story/hooks';
import ItemInteractionMobile from './ItemInteractionMobile';
import { StoryViewContext, initStateStory } from '@metafox/story/context';
import { reducerStoryView } from '@metafox/story/context/reducerStoryView';
import { ContentDetailBlock } from '../StoryDetail/Base';
import { WidthInteractionLandscape } from '@metafox/story/constants';
import { PauseStatus } from '@metafox/story/types';

const name = 'StoryDetailMobile';

const RootStyled = styled(Box, {
  name,
  slot: 'RootStyled',
  shouldForwardProp: props => props !== 'isOwner' && props !== 'isMobile'
})<{ isOwner?: boolean; isMobile?: boolean }>(
  ({ theme, isOwner, isMobile }) => ({
    width: '100%',
    height: '100%',
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    userSelect: 'none',
    backgroundColor: '#000',
    flexDirection: 'column',
    padding: theme.spacing(1.5, 0),
    ...((isMobile || !isOwner) && {
      paddingBottom: 0
    })
  })
);

const ItemWrapper = styled(Box, {
  name,
  slot: 'ItemWrapper'
})(({ theme }) => ({
  position: 'relative',
  margin: 'auto',
  backgroundColor: '#000',
  display: 'flex',
  justifyContent: 'center',
  borderRadius: theme.shape.borderRadius,
  height: '100%',
  flex: 1,
  minWidth: 0
}));

const StoryImage = styled('div', {
  name,
  slot: 'StoryImage',
  shouldForwardProp: props => props !== 'width'
})<{ height?: number; width?: number }>(({ theme, width }) => ({
  height: '100%',
  width,
  position: 'relative'
}));

const ContentWrapper = styled(Box, { name, slot: 'ContentWrapper' })(
  ({ theme }) => ({
    width: '100%',
    height: '100%',
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    flex: 1,
    minHeight: 0
  })
);

function StoryDetailMobile(props: any) {
  const { item } = props || {};
  const { useGetItem, useSession, useIsMobile } = useGlobal();
  const isMinHeight = useMediaQuery('(max-height:667px)');

  const isMobile = useIsMobile(true);
  const [state, fire] = React.useReducer(reducerStoryView, {
    ...initStateStory
  });

  React.useEffect(() => {
    fire({
      type: 'setReady',
      payload: false
    });
  }, []);

  const { openStoryDetail } = state || {};

  const imageRef = React.useRef();
  const { user } = useSession();

  const [width, height] = useGetSizeContainer(imageRef);

  const userStory = useGetItem(item?.user);

  const isOwner = userStory?.id === user?.id;

  React.useEffect(() => {
    if (openStoryDetail) {
      fire({
        type: 'setForcePause',
        payload: PauseStatus.Force
      });

      return;
    }

    fire({
      type: 'setForcePause',
      payload: PauseStatus.No
    });
  }, [openStoryDetail]);

  if (!item) return null;

  const handleViewDetail = () => {
    fire({ type: 'setOpenStoryDetail', payload: !openStoryDetail });
  };

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <StoryViewContext.Provider value={{ ...state, fire }}>
          <RootStyled isOwner={isOwner} isMobile={isMobile}>
            <ContentWrapper>
              <ItemWrapper
                style={{
                  ...(isMinHeight && {
                    width: WidthInteractionLandscape,
                    flex: 'unset'
                  }),
                  height: height ? height : '100%'
                }}
              >
                <StoryImage ref={imageRef} width={width}>
                  <ContentDetailBlock item={item} isOwner={isOwner} />
                </StoryImage>
              </ItemWrapper>
            </ContentWrapper>
            <ItemInteractionModal
              isMinHeight={isMinHeight}
              item={item}
              setOpen={value =>
                fire({ type: 'setOpenStoryDetail', payload: value })
              }
              open={openStoryDetail}
            />
            {openStoryDetail ? (
              <Box minHeight={64} />
            ) : (
              <ItemInteractionMobile
                item={item}
                isMinHeight={isMinHeight}
                width={width}
                handleViewDetail={handleViewDetail}
                isOwner={isOwner}
              />
            )}
          </RootStyled>
        </StoryViewContext.Provider>
      </BlockContent>
    </Block>
  );
}

export default connectItem(StoryDetailMobile);
