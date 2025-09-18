import { useGlobal } from '@metafox/framework';
import { useGetSizeContainer, useStoryViewContext } from '@metafox/story/hooks';
import { Box, Typography, styled } from '@mui/material';
import React from 'react';

const name = 'PenddingView';

const RootStyled = styled(Box, {
  name,
  slot: 'root',
  shouldForwardProp: props => props !== 'isDetailPage'
})<{ isDetailPage?: boolean }>(({ theme, isDetailPage }) => ({
  width: '100%',
  height: '100%',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  userSelect: 'none',
  backgroundColor: '#000',
  paddingTop: theme.spacing(1.5),
  flexDirection: 'column',
  cursor: 'pointer',
  '& .MuiSkeleton-root': {
    ...(theme.palette.mode === 'light' && {
      backgroundColor: theme.palette.grey['A200']
    })
  },
  ...(isDetailPage && {
    paddingTop: 0
  })
}));

const ItemWrapper = styled(Box, {
  name,
  slot: 'ItemWrapper',
  shouldForwardProp: props => props !== 'width' && props !== 'height'
})<{ height?: number; width?: number }>(({ theme, width }) => ({
  position: 'relative',
  margin: 'auto',
  height: '100%',
  display: 'flex',
  justifyContent: 'center',
  flexDirection: 'column',
  width
}));

const TextStyled = styled(Typography)(({ theme }) => ({
  color: '#fff',
  fontSize: theme.mixins.pxToRem(14),
  paddingBottom: theme.spacing(1.5),
  textAlign: 'center',
  width: '80%'
}));

const ItemInteractionStyled = styled(Box, {
  name,
  slot: 'ItemInteractionStyled',
  shouldForwardProp: props =>
    props !== 'isDetailPage' && props !== 'isOwner' && props !== 'isMobile'
})<{ isDetailPage?: boolean; isOwner?: boolean; isMobile?: boolean }>(
  ({ theme, isDetailPage, isOwner, isMobile }) => ({
    minHeight: isDetailPage ? 54 : 64,
    ...((isOwner || (isDetailPage && isMobile)) && {
      display: 'none'
    })
  })
);

const ContentStyled = styled(Box, {
  name,
  slot: 'ContentStyled',
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

interface Props {
  isDetailPage?: boolean;
  isOwner?: boolean;
}

export default function PenddingView({
  isDetailPage = false,
  isOwner = false
}: Props) {
  const { i18n, useIsMobile, dispatch } = useGlobal();
  const { fire } = useStoryViewContext();

  const isMobile = useIsMobile(true);
  const imageRef = React.useRef();
  const [width, height] = useGetSizeContainer(imageRef);

  const handleClick = React.useCallback(() => {
    fire({
      type: 'setReady',
      payload: true
    });
    fire({
      type: 'setMuted',
      payload: false
    });

    dispatch({
      type: 'story/updateMutedStatus',
      payload: false
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [fire]);

  return (
    <RootStyled isDetailPage={isDetailPage}>
      <ItemWrapper ref={imageRef} width={width}>
        <ContentStyled height={height} width={width} onClick={handleClick}>
          <TextStyled component={'span'}>
            {i18n.formatMessage({ id: 'click_to_view_story' })}
          </TextStyled>
        </ContentStyled>
      </ItemWrapper>
      <ItemInteractionStyled
        isDetailPage={isDetailPage}
        isOwner={isOwner}
        isMobile={isMobile}
      />
    </RootStyled>
  );
}
