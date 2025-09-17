/**
 * @type: formElement
 * name: form.element.RestoreDefault
 * chunkName: formElement
 */

import { FormFieldProps } from '@metafox/form';
import { Box, FormControl, styled } from '@mui/material';
import { LineIcon, Image } from '@metafox/ui';
import { camelCase } from 'lodash';
import React from 'react';

const ImageWrapper = styled(Box, { name: 'ImageWrapper' })(({ theme }) => ({
  width: 'auto',
  maxWidth: theme.mixins.pxToRem(240),
  maxHeight: theme.mixins.pxToRem(150),
  margin: '0 auto',
  height: theme.mixins.pxToRem(150),
  display: 'flex',
  alignItems: 'center',
  '& img': {
    maxHeight: theme.mixins.pxToRem(150)
  },
  [theme.breakpoints.down('sm')]: {
    height: 'auto'
  }
}));

const Icon = styled(LineIcon, { name: 'Icon' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(20),
  [theme.breakpoints.down('sm')]: {
    padding: theme.spacing(3, 0),
    transform: 'rotate(90deg)'
  }
}));

const Wrapper = styled(Box, { name: 'Wrapper' })(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-evenly',
  alignItems: 'center',
  marginTop: theme.spacing(2),
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column'
  }
}));

const TitleWrapper = styled(Box, { name: 'TitleWrapper' })(({ theme }) => ({
  width: 'auto',
  maxWidth: theme.mixins.pxToRem(240),
  wordBreak: 'break-all'
}));

export default function AttachmentField({ config, name }: FormFieldProps) {
  const { fullWidth = true, margin = 'normal', arrowConfig, from, to } = config;

  const isImageFrom = from?.dataSource.file_type?.match('image/*');
  const isImageTo = to?.dataSource.file_type?.match('image/*');

  return (
    <FormControl
      fullWidth={fullWidth}
      margin={margin}
      data-testid={camelCase(`field ${name}`)}
    >
      <Wrapper>
        <Box>
          {isImageFrom ? (
            <ImageWrapper>
              <Image src={from?.dataSource.url} />
            </ImageWrapper>
          ) : (
            <TitleWrapper>{from?.dataSource.url}</TitleWrapper>
          )}
        </Box>
        <Icon icon={arrowConfig.icon} />
        <Box>
          {isImageTo ? (
            <ImageWrapper>
              <Image src={to?.dataSource.url} />
            </ImageWrapper>
          ) : (
            <TitleWrapper>{to?.dataSource.url}</TitleWrapper>
          )}
        </Box>
      </Wrapper>
    </FormControl>
  );
}
