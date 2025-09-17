/**
 * @type: formElement
 * name: form.element.Container
 * chunkName: formBasic
 */
import { Element, FormFieldProps } from '@metafox/form';
import { Box, Typography } from '@mui/material';
import { styled } from '@mui/material/styles';
import clsx from 'clsx';
import { camelCase, map, intersection } from 'lodash';
import React from 'react';
import { LineIcon } from '@metafox/ui';
import { useFormikContext } from 'formik';

const hasErrorContainer = (obj1, obj2) => {
  const keys1 = Object.keys(obj1);
  const keys2 = Object.keys(obj2);

  return intersection(keys1, keys2).length > 0;
};

const FormContainerRoot = styled(Box, {
  name: 'MuiFormContainer',
  slot: 'Root',
  shouldForwardProp: (prop: string) => !/isSidePlacement|horizontal/i.test(prop)
})<{ isSidePlacement?: boolean; horizontal?: boolean }>(
  ({ theme, isSidePlacement, horizontal }) => ({
    position: 'relative',
    ...(isSidePlacement && {
      paddingRight: theme.spacing(2)
    })
  })
);

const BoxWrapper = styled(Box, {
  name: 'BoxWrapper'
})(({ theme }) => ({
  '&.multiStep': {
    marginRight: 'auto',
    order: 1
  }
}));

const TitleWrapper = styled(Box, {
  name: 'TitleWrapper',
  shouldForwardProp: prop => prop !== 'collapsible'
})<{ collapsible?: boolean }>(({ theme, collapsible }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  cursor: collapsible ? 'pointer' : 'auto'
}));

const Header = ({
  label,
  description,
  isMultiStep,
  collapsible,
  collapsed,
  setCollapsed,
  required,
  sx = {}
}) => {
  const handleToggle = React.useCallback(() => {
    if (collapsible) {
      setCollapsed(prev => !prev);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (!label && !description) return null;

  return (
    <BoxWrapper
      sx={{ pt: 1, ...sx }}
      className={isMultiStep ? 'multiStep' : ''}
    >
      <TitleWrapper collapsible onClick={handleToggle}>
        <Box component={'span'} sx={{ display: 'inline-flex' }}>
          {label ? (
            <Typography component="h3" color="text.primary" variant="h5">
              {label}
            </Typography>
          ) : null}
          {required ? '*' : ''}
        </Box>
        {collapsible ? (
          <Box
            component={'span'}
            sx={{
              display: 'inline-flex',
              alignItems: 'center',
              justifyContent: 'center',
              width: '32px',
              height: '32px'
            }}
          >
            <LineIcon icon={collapsed ? 'ico-angle-right' : 'ico-angle-down'} />
          </Box>
        ) : null}
      </TitleWrapper>
      {description ? (
        <Typography component="p" color="text.secondary" variant="body2">
          {description}
        </Typography>
      ) : null}
    </BoxWrapper>
  );
};

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
        '&>div:not(:last-child)': {
          paddingRight: theme.spacing(1)
        }
      }
    : {
        display: 'flex',
        flexDirection: 'column'
      })
}));

export default function Container({ formik, config }: FormFieldProps) {
  const {
    required,
    testid,
    description,
    isMultiStep,
    className,
    wrapAs: Wrapper,
    variant = 'vertical',
    label,
    elements,
    wrapperProps,
    sx,
    separator,
    sxContainer,
    collapsible = false,
    collapsed: collapsedDefault,
    sxHeader = {}
  } = config;
  const [collapsed, setCollapsed] = React.useState(collapsedDefault);
  const isHiddenContainer = collapsible && collapsed;
  const { errors, submitCount } = useFormikContext();

  React.useEffect(() => {
    // expand container when submit have error
    if (submitCount && collapsible && hasErrorContainer(errors, elements)) {
      setCollapsed(false);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [submitCount]);

  if (Wrapper) {
    const noSeparator = wrapperProps?.separator ? '' : 'noSeparator';

    return (
      <Wrapper className={noSeparator} sx={sx}>
        <Header
          isMultiStep={isMultiStep}
          label={label}
          description={description}
          required={required}
        />
        {map(elements, (config, key) => (
          <Element formik={formik} key={key.toString()} config={config} />
        ))}
      </Wrapper>
    );
  }

  return (
    <FormContainerRoot
      {...wrapperProps}
      data-testid={camelCase(testid)}
      className={clsx(className, separator)}
      sx={sx}
    >
      <Header
        isMultiStep={isMultiStep}
        label={label}
        description={description}
        collapsible={collapsible}
        collapsed={collapsed}
        setCollapsed={setCollapsed}
        required={required}
        sx={sxHeader}
      />
      {!isHiddenContainer ? (
        <ContainerBody
          horizontal={variant === 'horizontal'}
          className={clsx(className, separator)}
          sx={sxContainer}
        >
          {map(elements, (config, key) => (
            <Element formik={formik} key={key.toString()} config={config} />
          ))}
        </ContainerBody>
      ) : null}
    </FormContainerRoot>
  );
}
