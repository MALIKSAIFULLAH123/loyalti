import { useGetSizeContainer } from '@metafox/story/hooks';
import { Box, Button, LinearProgress, Typography, styled } from '@mui/material';
import React from 'react';
import AddItemCard from '../StoryListing/AddItemCard';
import { useGlobal } from '@metafox/framework';

const name = 'EndStoryView';

const RootStyled = styled(Box, { name })(({ theme }) => ({
  width: '100%',
  height: '100%',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  userSelect: 'none',
  backgroundColor: '#000',
  paddingTop: theme.spacing(1.5),
  flexDirection: 'column',
  '& .MuiSkeleton-root': {
    ...(theme.palette.mode === 'light' && {
      backgroundColor: theme.palette.grey['A200']
    })
  }
}));

const ItemWrapper = styled(Box, {
  name,
  slot: 'root',
  shouldForwardProp: props => props !== 'width' && props !== 'height'
})<{ height?: number; width?: number }>(({ theme, width, height }) => ({
  position: 'relative',
  margin: 'auto',
  height: height || '100%',
  display: 'flex',
  justifyContent: 'center',
  flexDirection: 'column',
  width
}));

const HeaderContainer = styled(Box, { name })(({ theme }) => ({
  position: 'absolute',
  top: 0,
  left: 0,
  right: 0,
  zIndex: 9999,
  width: '100%',
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(1.5),
  paddingTop: theme.spacing(2),
  justifyContent: 'space-between'
}));

const ItemInteraction = styled(Box)(({ theme }) => ({
  minHeight: 64
}));

const ContentStyled = styled(Box, {
  shouldForwardProp: props => props !== 'width' && props !== 'height'
})<{ height?: number; width?: number }>(({ theme, height, width }) => ({
  backgroundColor: theme.palette.grey['900'],
  borderRadius: theme.shape.borderRadius,
  height,
  width,
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  flexDirection: 'column',
  minWidth: '120px'
}));

const ButtonStyled = styled(Button)(({ theme }) => ({
  width: '80%'
}));

const TitleStyled = styled(Typography)(({ theme }) => ({
  color: '#fff',
  paddingTop: theme.spacing(1.5),
  paddingBottom: theme.spacing(1),
  fontSize: theme.mixins.pxToRem(18),
  fontWeight: theme.typography.fontWeightSemiBold
}));

const TextStyled = styled(Typography)(({ theme }) => ({
  color: '#fff',
  fontSize: theme.mixins.pxToRem(14),
  paddingBottom: theme.spacing(1.5),
  textAlign: 'center',
  width: '80%'
}));

const LinearProgressStyled = styled(LinearProgress, { name })(({ theme }) => ({
  width: '100%',
  borderRadius: theme.shape.borderRadius / 4,
  backgroundColor: 'rgba(255, 255, 255, 0.5)',
  '& .MuiLinearProgress-bar': {
    borderRadius: theme.shape.borderRadius / 2,
    backgroundColor: 'rgba(255, 255, 255, 0.9)',
    transition: 'transform .3s linear',
    transformOrigin: 'left',
    willChange: 'transform'
  }
}));

const initialTime = 10;

export default function EndStoryView() {
  const { i18n, navigate } = useGlobal();
  const imageRef = React.useRef();
  const timeRef = React.useRef<any>(0);

  const [timeState, setTimeState] = React.useState<number>(0);

  React.useEffect(() => {
    const timer = setInterval(() => {
      timeRef.current = timeRef.current + 0.1;
      setTimeState(prev => prev + 0.1);

      if (timeRef.current >= initialTime) {
        navigate('/');
      }
    }, 100);

    return () => {
      clearInterval(timer);
    };
  }, []);

  const [width, height] = useGetSizeContainer(imageRef);

  const handleClick = () => {
    navigate('/story/add');
  };

  const calculatorProgress = React.useCallback(
    () => {
      if (timeState >= initialTime) return 100;

      return Math.round((timeState / initialTime) * 100) || 0;
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [timeState, initialTime]
  );

  return (
    <RootStyled>
      <ItemWrapper ref={imageRef} width={width} height={height}>
        <HeaderContainer>
          <LinearProgressStyled
            variant="determinate"
            value={calculatorProgress()}
          />
        </HeaderContainer>
        <ContentStyled height={height} width={width}>
          <AddItemCard title={'create_story'} preventShowAvatar />
          <TitleStyled component={'h4'}>
            {i18n.formatMessage({ id: 'continue_story' })}
          </TitleStyled>
          <TextStyled component={'span'}>
            {i18n.formatMessage({ id: 'create_story_description' })}
          </TextStyled>
          <ButtonStyled
            variant="contained"
            size="medium"
            color="primary"
            onClick={handleClick}
          >
            {i18n.formatMessage({ id: 'create_story' })}
          </ButtonStyled>
        </ContentStyled>
      </ItemWrapper>
      <ItemInteraction />
    </RootStyled>
  );
}
