import { useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import * as React from 'react';
import { ItemInteractionModal } from '@metafox/story/components';
import { Box, styled, useMediaQuery } from '@mui/material';
import { useGetSizeContainer } from '@metafox/story/hooks';
import ItemInteractionMobile from './ItemInteractionMobile';
import { StoryViewContext, initStateStory } from '@metafox/story/context';
import { reducerArchiveView } from '@metafox/story/context/reducerArchiveView';
import { isEmpty } from 'lodash';
import { ContentDetailArchiveBlock } from '../StoryArchiveDetail/Base';
import connectStoryArchive from '@metafox/story/hooks/connectStoryArchive';
import { PauseStatus } from '@metafox/story/types';
import { WidthInteractionLandscape } from '@metafox/story/constants';

const name = 'StoryArchiveDetailMobile';

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
  maxHeight: '100%',
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

function StoryArchiveDetailMobile(props: any) {
  const { useGetItem, useSession, useIsMobile, usePageParams, dispatch } =
    useGlobal();
  const isMinHeight = useMediaQuery('(max-height:667px)');

  const isMobile = useIsMobile(true);

  const imageRef = React.useRef();
  const { user } = useSession();

  const [width, height] = useGetSizeContainer(imageRef);

  const pageParams = usePageParams();
  const {
    identity,
    stories,
    story_id,
    total: totalProps,
    nextDate,
    prevDate,
    shouldPreload: shouldPreloadProps,
    indexStoryActive: indexStoryActiveProps,
    date,
    positionStory: positionStoryProps,
    pagingId
  } = props || {};

  const [state, fire] = React.useReducer(reducerArchiveView, {
    ...initStateStory
  });

  const { identityStoryActive, openStoryDetail } = state || {};

  const itemInit: any = useGetItem(identity);

  const item: any = useGetItem(identityStoryActive) || itemInit;

  const userStory = useGetItem(item?.user);

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
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [openStoryDetail, identityStoryActive]);

  React.useEffect(() => {
    if (!stories?.length) return;

    fire({
      type: 'setInit',
      payload: {
        isReady: false,
        stories,
        story_id,
        total: totalProps,
        nextDate,
        prevDate,
        date,
        positionStory: positionStoryProps,
        pagingId
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (isEmpty(itemInit)) return;

    fire({
      type: 'setInit',
      payload: {
        indexStoryActive: indexStoryActiveProps,
        identityStoryActive: identity
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [itemInit]);

  React.useEffect(() => {
    if (!pageParams?.id || !shouldPreloadProps) return;

    dispatch({
      type: 'story/story_archive/LOAD',
      payload: {
        user_id: pageParams?.user_id,
        date,
        direction: shouldPreloadProps
      },
      meta: {
        onSuccess: handleSuccessLoadMore
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleSuccessLoadMore = pagingData => {
    const pagingId = pagingData?.pagingId;
    const stories = pagingData?.ids;

    if (!pagingId || !stories?.length) return;

    const total = pagingData?.pagesOffset?.total;

    fire({
      type: 'setInit',
      payload: {
        stories,
        total
      }
    });
  };

  if (!userStory) return null;

  const isOwner = userStory?.id === user?.id;

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
                  height: height ? height : '100%',
                  ...(isMinHeight && {
                    width: WidthInteractionLandscape,
                    flex: 'unset'
                  })
                }}
              >
                <StoryImage ref={imageRef} width={width}>
                  <ContentDetailArchiveBlock item={item} isOwner={isOwner} />
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
              isArchive
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

export default connectStoryArchive(StoryArchiveDetailMobile);
