/**
 * @type: formElement
 * name: form.element.QrCode
 * chunkName: formBasic
 */

import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { QRCodeSVG } from 'qrcode.react';
import { Box, styled, Typography } from '@mui/material';
import { camelCase } from 'lodash';

const RootStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  margin: theme.spacing(1.5, 0)
}));
const QRCodeStyled = styled(Box)<{ width: number; height: number }>(
  ({ theme, width, height }) => ({
    width: `${width + 35}px`,
    height: `${height + 35}px`,
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative'
  })
);

const ConnerBefore = styled('span')<{ width?: number; height?: number }>(
  ({ theme, width, height }) => ({
    '&::before': {
      content: "''",
      zIndex: 1,
      position: 'absolute',
      width: 18,
      height: 18,
      border: theme.mixins.border('secondary'),
      top: 0,
      left: 0,
      borderBottom: 0,
      borderRight: 0
    },
    '&::after': {
      content: "''",
      zIndex: 1,
      position: 'absolute',
      width: 18,
      height: 18,
      border: theme.mixins.border('secondary'),
      bottom: 0,
      left: 0,
      borderTop: 0,
      borderRight: 0
    }
  })
);
const ConnerAfter = styled('span')<{ width?: number; height?: number }>(
  ({ theme, width, height }) => ({
    '&::before': {
      content: "''",
      zIndex: 1,
      position: 'absolute',
      width: 18,
      height: 18,
      border: theme.mixins.border('secondary'),
      top: 0,
      right: 0,
      borderBottom: 0,
      borderLeft: 0
    },
    '&::after': {
      content: "''",
      zIndex: 1,
      position: 'absolute',
      width: 18,
      height: 18,
      border: theme.mixins.border('secondary'),
      bottom: 0,
      right: 0,
      borderTop: 0,
      borderLeft: 0
    }
  })
);

const QRCodeSVGStyled = styled(QRCodeSVG)<{ width: number; height: number }>(
  ({ theme, width, height }) => ({
    width: `${width}px`,
    height: `${height}px`,
    zIndex: 5
  })
);
const KeySecurityStyled = styled(Box)<{ width: number; height: number }>(
  ({ theme, width, height }) => ({
    width: `${width + 35}px`,
    height: `${height + 35}px`,
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    flexDirection: 'column',
    border: theme.mixins.border('secondary'),
    padding: theme.spacing(3),
    marginLeft: theme.spacing(1.5)
  })
);

const QRCode = ({ config, name }: FormFieldProps) => {
  const { content, description, label, width = 210, height = 210 } = config;

  if (!content && !description) return null;

  return (
    <RootStyled data-testid={camelCase(`field ${name}`)}>
      {content ? (
        <QRCodeStyled className="border-corner" width={width} height={height}>
          <ConnerBefore />
          <QRCodeSVGStyled value={content} width={width} height={height} />
          <ConnerAfter />
        </QRCodeStyled>
      ) : null}
      {description ? (
        <KeySecurityStyled width={width} height={height}>
          <Typography sx={{ textAlign: 'center', mb: 1 }} component={'h2'}>
            {label}
          </Typography>
          <Typography
            variant="body1"
            fontWeight={600}
            sx={{ textAlign: 'center' }}
          >
            {description}
          </Typography>
        </KeySecurityStyled>
      ) : null}
    </RootStyled>
  );
};

export default QRCode;
