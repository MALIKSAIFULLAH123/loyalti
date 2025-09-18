import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { StoryItemProps } from '@metafox/story/types';
import { LineIcon } from '@metafox/ui';
import { Box, Typography, styled } from '@mui/material';
import React from 'react';

interface Props {
  item: StoryItemProps;
}

const name = 'AddStoryButton';

const RootStyled = styled(Box, { name, slot: 'root-statistic' })(
  ({ theme }) => ({
    flex: 1,
    overflow: 'hidden'
  })
);

const TitleStyled = styled(Box, { name, slot: 'root-statistic' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    height: '40px'
  })
);

const EyeIconStyled = styled(LineIcon)(({ theme }) => ({
  color: theme.palette.text.primary,
  marginRight: theme.spacing(1),
  fontSize: theme.mixins.pxToRem(16),
  fontWeight: theme.typography.fontWeightBold
}));

function StatisticView({ item }: Props) {
  const { i18n, jsxBackend } = useGlobal();

  const { statistic, id } = item;
  const totalView = statistic?.total_view || 0;

  const pageParamsDefault = { story_id: id };

  const ListViewer = jsxBackend.get('story.block.viewListBlock');

  return (
    <>
      <TitleStyled>
        <EyeIconStyled icon="ico-eye-o" />
        <Typography variant="h5" color={'text.primary'}>
          {i18n.formatMessage(
            { id: 'total_story_viewer' },
            { value: totalView }
          )}
        </Typography>
      </TitleStyled>
      <RootStyled>
        {totalView ? (
          <ScrollContainer autoHide autoHeight autoHeightMax={'100%'}>
            <ListViewer pageParamsDefault={pageParamsDefault} />
          </ScrollContainer>
        ) : (
          <Typography variant="body1" color={'text.secondary'}>
            {i18n.formatMessage({ id: 'description_no_viewer_story' })}
          </Typography>
        )}
      </RootStyled>
    </>
  );
}

export default React.memo(
  StatisticView,
  (prev, next) => prev?.item?.id === next?.item?.id
);
