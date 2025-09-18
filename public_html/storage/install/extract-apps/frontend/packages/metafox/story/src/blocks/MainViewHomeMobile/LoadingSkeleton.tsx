/**
 * @type: skeleton
 * name: story.block.mainViewHome.skeleton
 */
import { useGetSizeContainer } from '@metafox/story/hooks';
import { Box, Skeleton, styled } from '@mui/material';
import React from 'react';

const name = 'MainViewHomeMobileSkeleton';

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
  height: height ? height : '100%',
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

const WrapperTitle = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  maxWidth: '70%',
  overflow: 'hidden'
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
  width
}));

export default function LoadingSkeleton({
  isMinHeight = false,
  isSmallHeight = false
}) {
  const imageRef = React.useRef();

  const [width, height] = useGetSizeContainer(imageRef);

  return (
    <RootStyled>
      <ItemWrapper ref={imageRef} width={width} height={height}>
        <HeaderContainer>
          <WrapperTitle>
            <Skeleton
              variant="circular"
              width={isMinHeight || isSmallHeight ? 36 : 48}
              height={isMinHeight || isSmallHeight ? 36 : 48}
            />
            <Box ml={1}>
              <Skeleton variant="text" sx={{ fontSize: '1rem' }} width={120} />
              <Skeleton variant="text" sx={{ fontSize: '1rem' }} width={90} />
            </Box>
          </WrapperTitle>
        </HeaderContainer>
        <ContentStyled height={height} width={width} />
      </ItemWrapper>
      <ItemInteraction />
    </RootStyled>
  );
}
