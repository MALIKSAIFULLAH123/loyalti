import { useGlobal } from '@metafox/framework';
import { FormHelperText, SxProps, Box } from '@mui/material';
import { isArray, isString } from 'lodash';
import React from 'react';
import HtmlViewer from '@metafox/html-viewer';

type Props = {
  text: string;
  sx?: SxProps;
  error?: boolean;
  component?: React.ElementType<any>;
};
export default function Description({
  text,
  sx,
  error = false,
  component = 'span'
}: Props) {
  const { i18n } = useGlobal();

  if (!text) return null;

  if (isString(text) && text.startsWith('<html>')) {
    return (
      <FormHelperText sx={sx} error={error}>
        {i18n.formatMessage({ id: '[placeholder]', defaultMessage: text })}
      </FormHelperText>
    );
  }

  const textParse = isArray(text) ? text[0] : text;

  return (
    <FormHelperText sx={sx} error={error}>
      <Box
        component={component}
        sx={{ whiteSpace: 'normal', wordBreak: 'break-word', mb: 0.5 }}
      >
        <HtmlViewer html={textParse} />
      </Box>
    </FormHelperText>
  );
}
