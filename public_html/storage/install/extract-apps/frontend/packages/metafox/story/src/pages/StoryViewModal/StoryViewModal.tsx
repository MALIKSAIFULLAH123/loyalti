/**
 * @type: dialog
 * name: story.dialog.storyView
 * chunkName: dialog.Story
 */

import { Dialog, DialogContent } from '@metafox/dialog';
import { connectItem, useGlobal } from '@metafox/framework';
import { Box, Typography, styled } from '@mui/material';
import * as React from 'react';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';
import { ItemInteractionModal, PlayStoryView } from '@metafox/story/components';
import { LineIcon } from '@metafox/ui';
import { useGetSizeContainer } from '@metafox/story/hooks';

const name = 'storyView';

const Root = styled(DialogContent, {
  name: 'StoryView',
  slot: 'dialogStatistic'
})<{}>(({ theme }) => ({
  padding: '0 !important',
  height: '100%',
  display: 'flex',
  overflowX: 'hidden',
  [theme.breakpoints.down('md')]: {
    height: '100%',
    flexFlow: 'column'
  }
}));

const DialogStory = styled('div', { name: 'StoryView', slot: 'dialogStory' })(
  ({ theme }) => ({
    position: 'relative',
    backgroundColor: '#000',
    width: '100%',
    overflow: 'hidden',
    flex: 1,
    minWidth: 0,
    '& iframe': {
      width: '100%',
      height: '100%'
    },
    [theme.breakpoints.down('md')]: {
      width: '100%',
      height: 'auto',
      borderRadius: 0,
      overflow: 'initial'
    }
  })
);

const DialogStatistic = styled('div', {
  name: 'StoryView',
  slot: 'dialogStatistic'
})(({ theme }) => ({
  height: '100%',
  width: '420px',
  [theme.breakpoints.down('md')]: {
    width: '100%'
  },
  [theme.breakpoints.down('xs')]: {
    width: '100%',
    height: '400px'
  }
}));

const StyledWrapperStatistic = styled(Box, { name, slot: 'WrapperStatistic' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    height: '100%',
    padding: theme.spacing(2),
    paddingTop: theme.spacing(2.5),
    paddingBottom: 0
  })
);

const HeaderBlock = styled(Box, { name, slot: 'HeaderBlock' })(({ theme }) => ({
  padding: theme.spacing(2),
  paddingTop: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));
const HeaderTitle = styled(Box, { name, slot: 'HeaderTitle' })(() => ({}));
const ContentBlock = styled(Box, { name, slot: 'ContentBlock' })(
  ({ theme }) => ({
    borderTop: theme.mixins.border('secondary'),
    overflow: 'hidden',
    display: 'flex',
    flexDirection: 'column',
    flex: 1,
    minHeight: 0
  })
);

//
const RootStyled = styled(Box, {
  name,
  slot: 'RootStyled'
})<{}>(({ theme }) => ({
  width: '100%',
  height: '100%',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  userSelect: 'none',
  backgroundColor: '#000',
  paddingTop: theme.spacing(1.5),
  flexDirection: 'column'
}));

const FooterStyled = styled(Box, {
  slot: 'FooterStyled',
  shouldForwardProp: props =>
    props !== 'showView' && props !== 'width' && props !== 'isMobile'
})<{ showView?: boolean; width?: any; isMobile?: boolean }>(
  ({ theme, width, isMobile }) => ({
    minHeight: '64px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    cursor: 'pointer',
    ...(width && {
      width
    }),
    ...(isMobile && {
      padding: theme.spacing(0, 2),
      width: '100%'
    })
  })
);

const ContentWrapper = styled(Box, {
  name,
  slot: 'ContentWrapper',
  shouldForwardProp: props => props !== 'width' && props !== 'isMobile'
})<{ showView?: boolean; width?: any; isMobile?: boolean }>(
  ({ theme, width }) => ({
    width: width ? width : '100%',
    height: '100%',
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    flex: 1,
    minHeight: 0,
    position: 'relative',
    flexDirection: 'column'
  })
);

const ViewTextStyled = styled(Typography)(({ theme }) => ({
  color: '#fff'
}));
const ToggleIconStyled = styled(LineIcon)(({ theme }) => ({
  color: '#fff',
  marginRight: theme.spacing(1),
  fontSize: theme.mixins.pxToRem(20),
  fontWeight: theme.typography.fontWeightBold
}));

const ViewWrapper = styled(Box, { name, slot: 'ViewWrapper' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center'
}));

function StoryViewDialog({ item, identity, error, searchParams }: any) {
  const { useDialog, i18n, useIsMobile } = useGlobal();
  const { dialogProps } = useDialog();
  const isMobile = useIsMobile(true);

  const [openDetail, setOpenDetail] = React.useState(false);
  const imageRef = React.useRef();

  const [width] = useGetSizeContainer(imageRef);

  const handleViewDetail = () => {
    setOpenDetail(prev => !prev);
  };

  if (isMobile) {
    return (
      <Dialog
        scroll={'body'}
        {...dialogProps}
        fullScreen
        data-testid="popupDetailStory"
        onBackdropClick={undefined}
      >
        <ErrorBoundary error={error}>
          <RootStyled>
            <ContentWrapper ref={imageRef} width={width}>
              <PlayStoryView story={item} isModal />
              <ItemInteractionModal
                item={item}
                setOpen={setOpenDetail}
                open={openDetail}
              />
            </ContentWrapper>
            <FooterStyled isMobile={isMobile} width={width}>
              <ViewWrapper onClick={handleViewDetail}>
                <ToggleIconStyled icon="ico-angle-up" />
                <ViewTextStyled variant="h4" color={'text.primary'}>
                  {i18n.formatMessage({ id: 'story_details' })}
                </ViewTextStyled>
              </ViewWrapper>
            </FooterStyled>
          </RootStyled>
        </ErrorBoundary>
      </Dialog>
    );
  }

  return (
    <Dialog
      scroll={'body'}
      {...dialogProps}
      fullScreen
      data-testid="popupDetailStory"
      onBackdropClick={undefined}
    >
      <ErrorBoundary error={error}>
        <Root>
          <DialogStory>
            <PlayStoryView story={item} isModal />
          </DialogStory>
          <DialogStatistic>
            <StyledWrapperStatistic>
              <HeaderBlock>
                <HeaderTitle>
                  <Typography variant="h4" color={'text.primary'}>
                    {i18n.formatMessage({ id: 'story_details' })}
                  </Typography>
                </HeaderTitle>
              </HeaderBlock>
              <ContentBlock>
                <ItemInteractionModal item={item} />
              </ContentBlock>
            </StyledWrapperStatistic>
          </DialogStatistic>
        </Root>
      </ErrorBoundary>
    </Dialog>
  );
}

export default connectItem(StoryViewDialog);
