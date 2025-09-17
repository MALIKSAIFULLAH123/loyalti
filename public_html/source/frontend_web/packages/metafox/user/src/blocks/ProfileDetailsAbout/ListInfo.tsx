/**
 * @type: ui
 * name: layout.section.list_info
 */

import { LineIcon, HtmlViewerWrapper } from '@metafox/ui';
import { Box, Typography, styled } from '@mui/material';
import React from 'react';
import { useGlobal } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const StyledTextInfo = styled('div')(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  color: theme.palette.text.primary
}));

const BoxWrapper = styled(Box)(({ theme }) => ({
  '&:not(:last-child)': {
    marginBottom: theme.spacing(2)
  }
}));

const TitleStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  padding: theme.spacing(1, 0)
}));

const Field = ({ field }) => {
  const { jsxBackend } = useGlobal();
  const { value, value_text, as } = field || {};

  if (!value && !value_text) return null;

  const AsComponent = as ? jsxBackend.get(`listInfo.as.${as}`) : '';

  return (
    <BoxWrapper>
      {field.icon || field.label ? (
        <TitleStyled>
          <Typography component="h3" color="text.primary" variant="h5">
            {field.icon ? <LineIcon icon={field.icon} /> : field.label}
          </Typography>
        </TitleStyled>
      ) : null}
      <Typography paragraph variant="body1" color="text.secondary">
        {AsComponent ? (
          <AsComponent {...field} />
        ) : (
          <HtmlViewerWrapper mt={0}>
            <HtmlViewer html={value_text || value || ''} />
          </HtmlViewerWrapper>
        )}
      </Typography>
    </BoxWrapper>
  );
};

const Section = ({ section }) => {
  return Object.values(section.fields).map((field, index) => (
    <Field field={field} key={index.toString()} />
  ));
};

export default Section;
