/**
 * @type: ui
 * name: story.ui.createStory
 */

import { useGlobal } from '@metafox/framework';
import React from 'react';
import { Box, Typography, styled } from '@mui/material';
import { LineIcon } from '@metafox/ui';
import { alpha } from '@mui/system/colorManipulator';

const NewStoryStyled = styled(Box, {
  name: 'StoryListing',
  slot: 'newStoryStyled',
  overridesResolver(props, styles) {
    return [styles.newStoryStyled];
  }
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  cursor: 'pointer',
  marginBottom: 0,
  marginTop: theme.spacing(2),
  paddingTop: theme.spacing(2.5),
  paddingBottom: theme.spacing(2.5),
  paddingLeft: theme.spacing(2),
  paddingRight: theme.spacing(2),
  backgroundColor: theme.palette.background.paper,
  borderRadius: theme.shape.borderRadius,
  [theme.breakpoints.down('md')]: {
    borderRadius: 0,
    marginTop: 0,
    padding: 0
  }
}));

const ButtonAddStyled = styled(Box, { slot: 'ButtonAdd' })(({ theme }) => ({
  marginRight: theme.spacing(1.5),
  position: 'relative',
  width: '48px',
  height: '48px',
  backgroundColor: alpha(theme.palette.primary.main, 0.15),
  borderRadius: '50%',
  cursor: 'pointer',
  border:
    theme.palette.mode === 'light' ? theme.mixins.border('secondary') : 'none'
}));

const AddLineIcon = styled(LineIcon)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(16),
  fontWeight: theme.typography.fontWeightBold,
  color: theme.palette.primary.main,
  position: 'absolute',
  top: '50%',
  left: '50%',
  transform: 'translate(-50%, -50%)'
}));

function CreateStory() {
  const { navigate, i18n } = useGlobal();

  const handleCreateStory = React.useCallback(() => {
    navigate('/story/add');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <NewStoryStyled data-testid="blockNewStory" onClick={handleCreateStory}>
      <ButtonAddStyled>
        <AddLineIcon icon="ico-plus" />
      </ButtonAddStyled>
      <Box>
        <Typography variant="h4" color={'text.primary'} mb={0.5}>
          {i18n.formatMessage({ id: 'create_story' })}
        </Typography>
        <Typography variant="body1" color={'text.secondary'}>
          {i18n.formatMessage({ id: 'share_photo_or_write_something' })}
        </Typography>
      </Box>
    </NewStoryStyled>
  );
}

export default CreateStory;
