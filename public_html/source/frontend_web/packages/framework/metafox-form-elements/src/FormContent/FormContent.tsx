/**
 * @type: formElement
 * name: form.element.FormContent
 * chunkName: formBasic
 */
import { DialogContent } from '@metafox/dialog';
import { Element, FormElementShape, useFormSchema } from '@metafox/form';
import { ScrollContainer } from '@metafox/layout';
import { Typography } from '@mui/material';
import { styled } from '@mui/material/styles';
import { map } from 'lodash';
import React from 'react';

const FormContentRoot = styled('div', {
  name: 'Form',
  slot: 'ContentRoot',
  shouldForwardProp: prop => prop !== 'isSidePlacement' && prop !== 'horizontal'
})<{
  isSidePlacement?: boolean;
}>(({ theme, isSidePlacement }) => ({
  display: 'block',
  flexGrow: 2,
  ...(isSidePlacement && {
    overflow: 'auto',
    paddingRight: 0,
    '& > *:first-of-type': {
      height: '100% !important'
    }
  })
}));

export default function FormContent({
  config: { description, label, elements }
}: {
  config: FormElementShape;
}) {
  const { dialog, isSidePlacement, dialogEmbedItem } = useFormSchema();

  const Wrapper = dialog ? DialogContent : FormContentRoot;
  const Container = isSidePlacement ? ScrollContainer : React.Fragment;
  const props = isSidePlacement
    ? { autoHeight: true, autoHeightMax: '100%', autoHeightMin: '100%' }
    : null;

  return (
    <Wrapper isSidePlacement={isSidePlacement}>
      {label || description ? (
        <div className="pt2 pb0">
          {label ? (
            <Typography component="div" color="textPrimary" variant="subtitle1">
              {label}
            </Typography>
          ) : null}
          {description ? (
            <Typography component="p" color="textPrimary" variant="body2">
              {description}
            </Typography>
          ) : null}
        </div>
      ) : null}
      {dialog && dialogEmbedItem ? dialogEmbedItem : null}
      <Container {...props}>
        {map(elements, (config, key) => (
          <Element key={key.toString()} config={config} />
        ))}
      </Container>
    </Wrapper>
  );
}
