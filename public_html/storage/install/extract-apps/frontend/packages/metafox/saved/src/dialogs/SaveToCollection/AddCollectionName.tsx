import ErrorMessage from '@metafox/form-elements/ErrorMessage';
import { useGlobal } from '@metafox/framework';
import { Box, Button, styled, TextField } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';

const RootStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(0, 1)
}));

const ButtonStyled = styled(Button)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15)
}));

function AddCollectionName({ handleToggleName }: any) {
  const { i18n, getSetting, dispatch } = useGlobal();
  const [name, setName] = React.useState('');
  const maximum_name_length = getSetting('saved.maximum_name_length');
  const [errorText, setErrorText] = React.useState(null);

  const handleCreate = () => {
    if (isEmpty(name)) {
      setErrorText(
        i18n.formatMessage({ id: 'collection_name_required_field' })
      );

      return;
    }

    handleToggleName();
    dispatch({
      type: 'saved/addNewCollectionName',
      payload: { name }
    });
  };

  const onChange = evt => {
    const value = evt.target.value;
    setName(value);
    setErrorText(null);
  };

  return (
    <Box>
      <RootStyled>
        <TextField
          id="question"
          name="question"
          sx={{ flex: 1, minWidth: 0, marginRight: 1 }}
          size="medium"
          label={i18n.formatMessage({ id: 'collection_name' })}
          variant="outlined"
          error={!!errorText}
          value={name}
          required
          onChange={onChange}
          inputProps={{
            maxLength: maximum_name_length
          }}
        />
        <ButtonStyled
          variant="outlined"
          onClick={handleCreate}
          size="large"
          color="primary"
          id="create"
        >
          {i18n.formatMessage({ id: 'create' })}
        </ButtonStyled>
      </RootStyled>
      {!!errorText && (
        <Box px={1}>
          <ErrorMessage error={errorText} />
        </Box>
      )}
    </Box>
  );
}

export default AddCollectionName;
