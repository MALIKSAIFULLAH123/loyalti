/**
 * @type: formElement
 * name: form.element.Sortable
 * chunkName: formBasic
 */
import { FormFieldProps } from '@metafox/form';
import { Box, Typography } from '@mui/material';
import { styled } from '@mui/material/styles';
import clsx from 'clsx';
import { camelCase } from 'lodash';
import React from 'react';
import { LineIcon } from '@metafox/ui';
import ElementDnd from './ElementDnd';

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
  setCollapsed
}) => {
  const handleToggle = React.useCallback(() => {
    if (collapsible) {
      setCollapsed(prev => !prev);
    }
  }, []);

  if (!label && !description) return null;

  return (
    <BoxWrapper sx={{ pt: 1 }} className={isMultiStep ? 'multiStep' : ''}>
      <TitleWrapper collapsible onClick={handleToggle}>
        {label ? (
          <Typography component="h3" color="text.primary" variant="h5">
            {label}
          </Typography>
        ) : null}
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
        '&>div': {
          paddingRight: theme.spacing(1)
        }
      }
    : {
        display: 'flex',
        flexDirection: 'column'
      })
}));

export default function Sortable({ formik, config }: FormFieldProps) {
  const {
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
    orderAction
  } = config;
  const [collapsed, setCollapsed] = React.useState(collapsedDefault);
  const isHiddenContainer = collapsible && collapsed;

  if (Wrapper) {
    const noSeparator = wrapperProps?.separator ? '' : 'noSeparator';

    return (
      <Wrapper className={noSeparator} sx={sx}>
        <Header
          isMultiStep={isMultiStep}
          label={label}
          description={description}
        />
        <ElementDnd
          formik={formik}
          elements={elements}
          orderAction={orderAction}
        />
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
      />
      {!isHiddenContainer ? (
        <ContainerBody
          horizontal={variant === 'horizontal'}
          className={clsx(className, separator)}
          sx={sxContainer}
        >
          <ElementDnd
            formik={formik}
            elements={elements}
            orderAction={orderAction}
          />
        </ContainerBody>
      ) : null}
    </FormContainerRoot>
  );
}
