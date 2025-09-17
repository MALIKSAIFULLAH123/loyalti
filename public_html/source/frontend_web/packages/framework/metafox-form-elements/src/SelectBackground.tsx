/**
 * @type: formElement
 * name: form.element.SelectBackground
 * chunkName: formBasic
 */
import { FormFieldProps } from '@metafox/form';
import { LineIcon } from '@metafox/ui';
import {
  Box,
  Button,
  FormControl,
  styled,
  TextFieldProps,
  Typography
} from '@mui/material';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';

const ITEM_WIDTH = 32;
const Container = styled(Box, { slot: 'ContetnWrapper' })(({ theme }) => ({
  border: theme.mixins.border('secondary'),
  borderRadius: theme.shape.borderRadius / 2
}));

const ContainerOutler = styled(Box, {
  slot: 'ContainerOutler',
  shouldForwardProp: props => props !== 'showFull'
})<{ showFull?: boolean }>(({ theme, showFull }) => ({
  padding: theme.spacing(2),
  ...(!showFull && {
    paddingBottom: 0
  })
}));

const ContentWrapper = styled(Box, { slot: 'ContetnWrapper' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  flexWrap: 'wrap'
}));

const ImageItem = styled(Box, {
  slot: 'ImageItem',
  shouldForwardProp: props => props !== 'active' && props !== 'disabled'
})<{ active?: boolean; disabled?: boolean }>(({ theme, active, disabled }) => ({
  height: ITEM_WIDTH,
  width: ITEM_WIDTH,
  borderRadius: theme.shape.borderRadius / 2,
  margin: theme.spacing(1),
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  ...(active && {
    '&:before': {
      content: '""',
      position: 'absolute',
      height: '36px',
      width: '36px',
      border: theme.mixins.border('primary'),
      borderWidth: '3px',
      ...(theme.palette.mode === 'dark' && {
        borderColor: '#fff'
      }),
      borderRadius: theme.shape.borderRadius / 2
    }
  }),
  backgroundRepeat: 'no-repeat',
  backgroundSize: 'cover',
  backgroundPosition: 'center',
  cursor: disabled ? 'not-allowed' : 'pointer'
}));

const ButtonIcon = styled(Button)(({ theme }) => ({
  width: '100%'
}));

const LineIconStyled = styled(LineIcon)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(16),
  color: theme.palette.mode === 'light' ? '#000' : '#fff'
}));

function outerWidthMarginItem(el) {
  if (!el) return ITEM_WIDTH;

  let width = el?.offsetWidth;
  const style = getComputedStyle(el);
  const marginLeft = style.marginLeft
    ? parseFloat(style.marginLeft.replace('px', ''))
    : 0;
  const marginRight = style.marginRight
    ? parseFloat(style.marginRight.replace('px', ''))
    : 0;

  width += marginLeft + marginRight;

  return width;
}

function computeFirstRowItems(elWrapper, elItem) {
  const widthWrapper = elWrapper?.getBoundingClientRect()?.width;

  return Math.floor(widthWrapper / outerWidthMarginItem(elItem));
}

const SelectBackgroundField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps<TextFieldProps>) => {
  const [field, , { setValue, setTouched }] = useField(
    name ?? 'SelectBackground'
  );

  const {
    label,
    margin = 'dense',
    variant,
    fullWidth,
    sxFieldWrapper,
    options: optionsProps = [],
    showFull = false,
    lines = 2
  } = config;

  const refWrapper = React.useRef<any>();
  const elItem = document.querySelector('.item-image-bg');
  const totalRowItems = computeFirstRowItems(refWrapper.current, elItem);
  const shortOption = optionsProps.slice(0, totalRowItems * lines);

  const [options, setOptions] = React.useState(optionsProps);
  const [toggle, setToggle] = React.useState(false);

  const handleClick = item => {
    if (forceDisabled || formik.isSubmitting || !item) return;

    setValue(item.id);
  };

  const handleBlur = e => {
    field.onBlur(e);
    setTouched(true);
  };

  React.useEffect(() => {
    if (!field.value && options.length) {
      setValue(options[0].id);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (!showFull && totalRowItems) {
      setOptions(shortOption);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [totalRowItems]);

  const handleToggle = () => {
    setOptions(toggle ? shortOption : optionsProps);
    setToggle(prev => !prev);
  };

  return (
    <FormControl
      margin={margin}
      variant={variant}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
      id={name}
      disabled={forceDisabled || formik.isSubmitting}
    >
      <Container onBlur={handleBlur}>
        <ContainerOutler showFull={showFull}>
          <Typography sx={{ mb: 2 }} variant={variant}>
            {label}
          </Typography>

          <ContentWrapper ref={refWrapper}>
            {options.map((item, key) => (
              <ImageItem
                className="item-image-bg"
                disabled={forceDisabled || formik.isSubmitting}
                key={key}
                active={item.id === field.value}
                onClick={() => handleClick(item)}
                style={{ backgroundImage: `url(${item.label})` }}
              />
            ))}
          </ContentWrapper>
        </ContainerOutler>
        {showFull ? null : (
          <ButtonIcon
            disableRipple
            disableFocusRipple
            data-testid={camelCase(`button ${name}`)}
            onClick={handleToggle}
            disabled={forceDisabled || formik.isSubmitting}
          >
            <LineIconStyled icon={toggle ? 'ico-caret-up' : 'ico-caret-down'} />
          </ButtonIcon>
        )}
      </Container>
    </FormControl>
  );
};

export default SelectBackgroundField;
