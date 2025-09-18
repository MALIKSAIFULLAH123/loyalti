import React from 'react';
import AddStoryButton from './AddStoryButton';
import { useGlobal } from '@metafox/framework';
import { getImageSrc } from '@metafox/utils';
import { Box, styled } from '@mui/material';
import { useStoryViewContext } from '@metafox/story/hooks';
import { StoryItemProps } from '@metafox/story/types';

const name = 'slider-story';

const RootStyled = styled(Box, {
  name,
  slot: 'Root',
  shouldForwardProp: props => props !== 'translate'
})<{ translate?: any }>(({ translate }) => ({
  display: 'flex',
  flexWrap: 'nowrap',
  maxHeight: '182px',
  alignItems: 'center',
  transform: `translate(${translate}px, 0)`,
  transition: 'all 300ms ease'
}));

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
  isArchive?: boolean;
}

const ItemImage = ({ story, active, translateItem, index }) => {
  const { assetUrl } = useGlobal();

  const { fire } = useStoryViewContext();

  const imageUrl = getImageSrc(
    story?.thumbs || story?.image,
    '100',
    assetUrl('story.no_image')
  );

  const handleClick = React.useCallback(() => {
    fire({
      type: 'setIdentityStoryActive',
      payload: story?._identity
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [story?._identity]);

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

const SliderStory = ({
  stories = [],
  item: story,
  isArchive = false
}: Props) => {
  const { getAcl } = useGlobal();
  const [translate, setTranslate] = React.useState(0);
  const createAcl = getAcl('story.story.create');

  const translateItem = ({ index }) => {
    if (index === 0) {
      setTranslate(0);

      return;
    }

    const x = index * 108 - THROTTLE;
    setTranslate(x);
  };

  return (
    <RootStyled translate={-translate}>
      {stories.map((item, index) => (
        <ItemImage
          index={index}
          key={item?.id}
          story={item}
          active={item?.id === story?.id}
          translateItem={translateItem}
        />
      ))}
      {createAcl && !isArchive ? <AddStoryButton /> : null}
    </RootStyled>
  );
};

export default SliderStory;
