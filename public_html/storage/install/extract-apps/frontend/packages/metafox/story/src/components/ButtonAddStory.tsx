/**
 * @type: ui
 * name: button.addCreateStory
 * chunkName: story
 */

import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, Typography, styled } from '@mui/material';
import React from 'react';

const name = 'AddStory';

const Container = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  cursor: 'pointer',
  padding: theme.spacing(1),
  margin: theme.spacing(0, 1),
  ':hover': {
    backgroundColor: theme.palette.action.hover,
    borderRadius: theme.shape.borderRadius
  }
}));

const ButtonAddStyled = styled(Box, { name, slot: 'ButtonAdd' })(
  ({ theme }) => ({
    position: 'relative',
    width: '48px',
    height: '48px',
    backgroundColor: theme.palette.background.default,
    borderRadius: '50%',
    cursor: 'pointer',
    border:
      theme.palette.mode === 'light' ? theme.mixins.border('secondary') : 'none'
  })
);

const AddLineIcon = styled(LineIcon)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(16),
  fontWeight: theme.typography.fontWeightBold,
  color: theme.palette.primary.main,
  position: 'absolute',
  top: '50%',
  left: '50%',
  transform: 'translate(-50%, -50%)'
}));

function AddStory() {
  const { getAcl, i18n, navigate } = useGlobal();
  const createAcl = getAcl('story.story.create');

  const handleCreateStory = React.useCallback(() => {
    navigate('/story/add');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (!createAcl) return null;

  return (
    <Container data-testid="addStoryBlock" onClick={handleCreateStory}>
      <ButtonAddStyled>
        <AddLineIcon icon="ico-plus" />
      </ButtonAddStyled>
      <Typography variant="h5" color={'text.primary'} ml={2}>
        {i18n.formatMessage({ id: 'create_story' })}
      </Typography>
    </Container>
  );
}

export default AddStory;
