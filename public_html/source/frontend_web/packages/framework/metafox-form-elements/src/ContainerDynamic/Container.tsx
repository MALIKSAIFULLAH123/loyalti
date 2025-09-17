/**
 * @type: formElement
 * name: form.element.ContainerAddable
 * chunkName: formBasic
 */
import { Element, FormFieldProps } from '@metafox/form';
import { Box, Typography, Button } from '@mui/material';
import { styled } from '@mui/material/styles';
import clsx from 'clsx';
import { camelCase, map } from 'lodash';
import React from 'react';
import { LineIcon } from '@metafox/ui';
import { useGlobal } from '@metafox/framework';
import produce from 'immer';
import ErrorMessage from '../ErrorMessage';
import { useField } from 'formik';

const name = 'MuiFormContainerDynamic';

const FormContainerRoot = styled(Box, {
  name,
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
  name,
  slot: 'BoxWrapper'
})(({ theme }) => ({}));

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
    <BoxWrapper sx={{ pt: 1, ...sx }}>
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
              height: '32px',
              marginTop: theme => theme.spacing(-1)
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
    className,
    variant = 'vertical',
    label,
    elements,
    wrapperProps,
    sx,
    separator,
    sxContainer,
    collapsible = false,
    collapsed: collapsedDefault,
    sxHeader = {},
    name,
    btnAddProps = {}
  } = config;
  const [collapsed, setCollapsed] = React.useState(collapsedDefault);
  const isHiddenContainer = collapsible && collapsed;
  const [containerKeys, setContainerKeys] = React.useState([`${name}.0`]);
  const { i18n } = useGlobal();
  const [, meta] = useField(name ?? 'ContainerAddable');

  const handleAddNewItem = () => {
    setContainerKeys(prev =>
      produce(prev, draft => {
        draft.push(`${name}.${draft.length}`);
      })
    );
  };

  const haveError = Boolean(meta.error && (meta.touched || formik.submitCount));

  return (
    <FormContainerRoot
      {...wrapperProps}
      data-testid={camelCase(testid)}
      className={clsx(className, separator)}
      sx={sx}
    >
      <Header
        label={label}
        description={description}
        collapsible={collapsible}
        collapsed={collapsed}
        setCollapsed={setCollapsed}
        required={required}
        sx={sxHeader}
      />

      {!isHiddenContainer ? (
        <>
          {containerKeys.map(x => (
            <ContainerBody
              horizontal={variant === 'horizontal'}
              className={clsx(className, separator)}
              sx={sxContainer}
              key={`${x}`}
            >
              {map(elements, (config, key) => (
                <Element
                  formik={formik}
                  key={key.toString()}
                  config={{ ...config, name: `${x}.${config.name}` }}
                />
              ))}
            </ContainerBody>
          ))}
          <Button
            variant="outlined"
            size="small"
            color="primary"
            data-testid={camelCase(`button ${name}`)}
            onClick={handleAddNewItem}
            startIcon={<LineIcon icon="ico-plus" />}
            {...btnAddProps}
          >
            {i18n.formatMessage({ id: btnAddProps?.label || 'add_more' })}
          </Button>
        </>
      ) : null}
      {haveError ? <ErrorMessage error={meta.error} /> : null}
    </FormContainerRoot>
  );
}
