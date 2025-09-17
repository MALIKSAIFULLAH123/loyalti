/**
 * @type: formElement
 * name: form.element.InlineEditSection
 * chunkName: formElement
 */

import { Element, FormFieldProps } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import { Box, Button, styled } from '@mui/material';
import { camelCase, isEmpty, map } from 'lodash';
import React, { useState } from 'react';

const StyledItemView = styled('div', {
  name: 'StyledItemView'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'baseline',
  width: '100%',
  justifyContent: 'flex-start',

  padding: theme.spacing(2.75, 0),
  [theme.breakpoints.down('sm')]: {
    display: 'block'
  },
  '&+div': {
    borderTop: 'solid 1px',
    borderTopColor: theme.palette.border?.secondary
  }
}));

const StyledTitle = styled('div', {
  name: 'StyledItemView',
  slot: 'StyledTitle'
})(({ theme }) => ({
  width: 300,
  fontSize: theme.typography.body1.fontSize,
  lineHeight: 1.6,
  fontWeight: theme.typography.fontWeightBold,
  color: theme.palette.text.primary
}));

const StyledContent = styled('div', {
  name: 'StyledItemView',
  slot: 'StyledContent'
})(({ theme }) => ({
  padding: theme.spacing(0, 2),
  width: '100%',
  fontSize: theme.typography.body1.fontSize,
  lineHeight: 1.6,
  color: theme.palette.text.primary
}));

const StyledContentInner = styled('div', {
  name: 'StyledItemView',
  slot: 'StyledContentInner'
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  '& button': {
    marginRight: theme.spacing(1)
  },
  '& div:first-of-type': {
    marginTop: 0,
    marginBottom: 0
  }
}));

export default function InlineEditSection({
  config,
  formik,
  name
}: FormFieldProps) {
  const { i18n } = useGlobal();
  const [isEdit, setEdit] = useState(false);

  const handleEdit = () => setEdit(true);

  const handleSave = () => {
    formik.submitForm().then(arg => {
      if (isEmpty(formik.errors)) {
        setEdit(false);
      }
    });
  };

  const handleCancel = () => setEdit(false);

  const { label, value, elements } = config;

  return (
    <StyledItemView data-testid={camelCase(`field ${name}`)}>
      <StyledTitle>{label}</StyledTitle>
      <StyledContent>
        {isEdit ? (
          <StyledContentInner>
            {map(elements, (config, key) => (
              <Element formik={formik} key={key.toString()} config={config} />
            ))}
            <Box sx={{ my: 1 }}>
              <Button variant="contained" onClick={handleSave}>
                {i18n.formatMessage({ id: 'save' })}
              </Button>
              <Button variant="outlined" onClick={handleCancel}>
                {i18n.formatMessage({ id: 'cancel' })}
              </Button>
            </Box>
          </StyledContentInner>
        ) : (
          <p>{value}</p>
        )}
      </StyledContent>
      {!isEdit && (
        <Button variant="text" onClick={handleEdit}>
          {i18n.formatMessage({ id: 'edit' })}
        </Button>
      )}
    </StyledItemView>
  );
}
