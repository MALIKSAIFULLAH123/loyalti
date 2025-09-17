/**
 * @type: formElement
 * name: form.element.SocialButtons
 * chunkName: formBasic
 */
import { Element, FormFieldProps } from '@metafox/form';
import { Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import clsx from 'clsx';
import { camelCase, map } from 'lodash';
import React from 'react';

const FormContainerRoot = styled(Box, {
  name: 'MuiFormContainer',
  slot: 'Root',
  shouldForwardProp: (prop: string) => !/isSidePlacement|horizontal/i.test(prop)
})<{ isSidePlacement?: boolean; horizontal?: boolean }>(
  ({ theme, isSidePlacement, horizontal }) => ({
    width: '100%',
    paddingRight: '0!important',
    position: 'relative'
  })
);

const ContainerBody = styled(Box, {
  name: 'MuiFormContainer',
  slot: 'Body',
  shouldForwardProp: (prop: string) => !/horizontal/.test(prop)
})<{ horizontal: boolean }>(({ theme, horizontal }) => ({
  position: 'relative',
  ...(horizontal
    ? {
        display: 'flex',
        alignItems: 'center',
        flexWrap: 'wrap',
        margin: theme.spacing(-0.5),
        '&>div': {
          margin: theme.spacing(0.5)
        }
      }
    : {
        display: 'flex',
        flexDirection: 'column'
      })
}));

export default function Container({ formik, config }: FormFieldProps) {
  const {
    testid,
    className,
    variant = 'horizontal',
    elements,
    wrapperProps,
    sx,
    separator,
    sxContainer,
    totalButton
  } = config;

  return (
    <FormContainerRoot
      {...wrapperProps}
      data-testid={camelCase(testid)}
      className={clsx(className, separator)}
      sx={sx}
    >
      <ContainerBody
        horizontal={variant === 'horizontal'}
        className={clsx(className, separator)}
        sx={sxContainer}
      >
        {map(elements, (config, key) => (
          <Element
            formik={formik}
            key={key.toString()}
            config={{ ...config, isShowLabel: totalButton === 1 }}
          />
        ))}
      </ContainerBody>
    </FormContainerRoot>
  );
}
