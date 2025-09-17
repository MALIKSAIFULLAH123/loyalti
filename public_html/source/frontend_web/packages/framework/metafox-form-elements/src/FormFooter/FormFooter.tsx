/**
 * @type: formElement
 * name: form.element.FormFooter
 * chunkName: formBasic
 */
import { Element, FormFieldProps, useFormSchema } from '@metafox/form';
import { Box, Typography } from '@mui/material';
import { DialogActions } from '@metafox/dialog';
import { styled } from '@mui/material/styles';
import { map } from 'lodash';
import React from 'react';
import Container from '../Container';

const FooterRoot = styled(Box, {
  name: 'Form',
  slot: 'Footer',
  shouldForwardProp: (prop: string) => !/separator|variant/.test(prop)
})<{ variant?: string; separator?: boolean; fixed?: boolean }>(
  ({ theme, variant, separator, fixed }) => ({
    ...{
      display: 'block',
      padding: theme.spacing(2, 0),
      marginTop: theme.spacing(2),
      flexShrink: 0,
      '& > div': {
        marginRight: theme.spacing(1)
      },
      [theme.breakpoints.down('sm')]: {
        paddingTop: theme.spacing(1),
        '& > div': {
          marginTop: theme.spacing(1)
        }
      }
    },
    ...(variant === 'horizontal' && {
      display: 'flex',
      flexDirection: 'row'
    }),
    ...(separator
      ? {
          borderTop: '1px solid',
          borderTopColor: theme.palette.divider,
          paddingTop: theme.spacing(2),
          paddingBottom: theme.spacing(0)
        }
      : {
          marginTop: 0,
          paddingTop: theme.spacing(1.5)
        }),
    ...(fixed && {
      position: 'fixed',
      bottom: 0,
      background: theme.palette.background.paper,
      display: 'flex',
      left: 0,
      right: 0,
      padding: `${theme.spacing(2)} !important`,
      flexDirection: 'row-reverse',
      boxShadow: theme.shadows[1],
      zIndex: 10
    })
  })
);

export default function FormFooter({
  config: {
    description,
    isMultiStep,
    label,
    variant,
    style,
    elements,
    separator = true,
    sx,
    fixed
  },
  name
}: FormFieldProps) {
  const { dialog, fixedFooter } = useFormSchema();
  const rootRef = React.useRef<HTMLDivElement>(null);
  const [height, setHeight] = React.useState<number>(0);

  React.useEffect(() => {
    if (rootRef.current) {
      const { clientHeight = 0 } = rootRef.current || {};
      setHeight(Math.max(72, clientHeight));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rootRef.current]);

  // hide if empty
  if (!Object.keys(elements).length) return null;

  if (dialog) {
    return (
      <Container
        config={{
          elements,
          wrapAs: DialogActions,
          name,
          label,
          sx,
          description,
          isMultiStep,
          wrapperProps: {
            separator
          }
        }}
      />
    );
  }

  if (fixed ?? fixedFooter) {
    return (
      <Box sx={{ height }}>
        <FooterRoot
          variant={variant}
          separator={separator}
          style={style}
          sx={sx}
          fixed={height > 0}
          ref={rootRef}
        >
          {map(elements, (config, key) => (
            <Element key={key} config={config} />
          ))}
        </FooterRoot>
      </Box>
    );
  }

  return (
    <FooterRoot variant={variant} separator={separator} style={style} sx={sx}>
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
      {map(elements, (config, key) => (
        <Element key={key} config={config} />
      ))}
    </FooterRoot>
  );
}
