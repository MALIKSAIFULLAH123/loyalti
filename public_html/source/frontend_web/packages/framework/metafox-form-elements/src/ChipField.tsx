/**
 * @type: formElement
 * name: form.element.Chip
 * chunkName: formExtras
 */
import { Box, Chip, Stack, styled, Typography } from '@mui/material';
import { useField } from 'formik';
import React from 'react';
import { FormFieldProps } from '@metafox/form';

const StackContent = styled(Stack, { slot: 'StackContent' })(({ theme }) => ({
  flexWrap: 'wrap',
  '& .MuiChip-root': {
    margin: theme.spacing(0.5)
  }
}));

const ChipField = ({ config, name }: FormFieldProps) => {
  const [field, , { setValue }] = useField(name ?? 'ChipField');
  const { options = [], direction = 'row', spacing = 1, label } = config;

  if (!field.value) {
    field.value = config.defaultValue ?? '';
  }

  const handChange = item => {
    setValue(item);
  };

  return (
    <Box>
      {label && (
        <Typography sx={{ paddingBottom: 1 }} variant="h5">
          {label}
        </Typography>
      )}
      <StackContent direction={direction} spacing={spacing}>
        {options.map((item, index) => (
          <Chip
            onClick={() => handChange(item)}
            key={index}
            label={item.title}
            variant="outlined"
          />
        ))}
      </StackContent>
    </Box>
  );
};

export default ChipField;
