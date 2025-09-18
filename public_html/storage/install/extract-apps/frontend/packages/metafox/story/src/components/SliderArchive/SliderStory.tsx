import React from 'react';
import { useGlobal } from '@metafox/framework';
import { getImageSrc } from '@metafox/utils';
import { Box, CircularProgress, styled } from '@mui/material';
import { useArchiveStory, useStoryViewContext } from '@metafox/story/hooks';
import { StoryItemProps } from '@metafox/story/types';
import { isEmpty } from 'lodash';
import Slider from './Slider';

const name = 'slider-story-archive';

const WrapperImage = styled(Box, {
  name,
  shouldForwardProp: props => props !== 'active'
})<{ active?: boolean }>(({ theme, active }) => ({
  padding: theme.spacing(3, 0),
  height: '182px',
  width: '100px',
  minWidth: '100px',
  marginRight: theme.spacing(1),
  ...(active && {
    height: '200px'
  })
}));

const ImageUrl = styled('div', {
  name,
  slot: 'iamge-story',
  shouldForwardProp: prop => prop !== 'imageUrl'
})<{ imageUrl: string }>(({ theme, imageUrl }) => ({
  cursor: 'pointer',
  display: 'flex',
  alignItems: 'center',
  height: '100%',
  width: '100%',
  borderRadius: theme.shape.borderRadius,
  backgroundImage: `url(${imageUrl})`,
  backgroundRepeat: 'no-repeat',
  backgroundSize: 'cover',
  backgroundPosition: 'center',
  border: theme.mixins.border('secondary')
}));

interface Props {
  stories: StoryItemProps[];
  item: StoryItemProps;
}

const ItemImage = ({ story, active, index, translateItem }) => {
  const { assetUrl } = useGlobal();

  const context = useStoryViewContext();
  const { fire } = context;

  const imageUrl = getImageSrc(
    story?.thumbs || story?.image,
    '100',
    assetUrl('story.no_image')
  );

  const handleClick = React.useCallback(() => {
    if (active) return;

    fire({
      type: 'setStoryActive',
      payload: {
        identity: story?._identity,
        index,
        positionStory: index
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [story?._identity, index, active]);

  React.useEffect(() => {
    if (active) {
      translateItem({ index });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [active, index]);

  return (
    <WrapperImage active={active} onClick={handleClick}>
      <ImageUrl key={imageUrl} imageUrl={imageUrl} />
    </WrapperImage>
  );
};

const THROTTLE = 45;

const SliderStory = ({ stories = [], item: story }: Props) => {
  const [translate, setTranslate] = React.useState(0);
  const [loading, setLoading] = React.useState(false);

  const context = useStoryViewContext();
  const { stories: storyIdentities, total } = context;

  const { loadmore } = useArchiveStory();

  const loadMoreSlider = index => {
    if (loading) return;

    if (total > storyIdentities.length - 1) {
      setLoading(true);
      loadmore(index, () => {
        setLoading(false);
      });
    }
  };

  const translateItem = ({ index }) => {
    if (index === 0) {
      setTranslate(0);

      return;
    }

    const x = index * 108 - THROTTLE;

    setTranslate(x);

    loadMoreSlider(index);
  };

  if (isEmpty(storyIdentities)) return null;

  return (
    <Slider
      translate={translate}
      setTranslate={setTranslate}
      loadMore={() => loadMoreSlider(storyIdentities.length)}
    >
      {stories.map((item, index) => (
        <ItemImage
          index={index}
          key={item?.id}
          story={item}
          active={item?.id === story?.id}
          translateItem={translateItem}
        />
      ))}
      {loading ? <CircularProgress size={16} /> : null}
    </Slider>
  );
};

export default SliderStory;
