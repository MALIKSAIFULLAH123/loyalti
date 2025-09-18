import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, Typography, styled } from '@mui/material';
import React from 'react';

const name = 'AddStoryButton';

const RootStyled = styled(Box)(({ theme }) => ({
  minWidth: '100px',
  width: '100px',
  height: '135px',
  display: 'flex',
  alignItems: 'center',
  cursor: 'pointer',
  padding: theme.spacing(1),
  flexDirection: 'column',
  background: theme.palette.background.default,
  borderRadius: theme.shape.borderRadius,
  justifyContent: 'center'
}));

const ButtonAddStyled = styled(Box, { name, slot: 'ButtonAdd' })(
  ({ theme }) => ({
    position: 'relative',
    width: '48px',
    height: '48px',
    backgroundColor: theme.palette.background.paper,
    borderRadius: '50%',
    marginBottom: theme.spacing(1.5),
    cursor: 'pointer',
    border:
      theme.palette.mode === 'light' ? theme.mixins.border('secondary') : 'none'
  })
);

const AddLineIcon = styled(LineIcon)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(16),
  color: theme.palette.primary.main,
  position: 'absolute',
  top: '50%',
  left: '50%',
  transform: 'translate(-50%, -50%)'
}));

const TextStyled = styled(Typography)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(12),
  textAlign: 'center'
}));

function AddStoryButton() {
  const { i18n, navigate } = useGlobal();

  const handleCreateStory = React.useCallback(() => {
    navigate('/story/add');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <RootStyled onClick={handleCreateStory}>
      <ButtonAddStyled>
        <AddLineIcon icon="ico-plus" />
      </ButtonAddStyled>
      <TextStyled variant="body1" color={'text.primary'}>
        {i18n.formatMessage({ id: 'create_story' })}
      </TextStyled>
    </RootStyled>
  );
}

export default AddStoryButton;
