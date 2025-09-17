/**
 * @type: formElement
 * name: form.element.CustomField
 * chunkName: formExtras
 */
import {
  FormControl,
  Box,
  styled,
  TextField,
  Tooltip,
  Button
} from '@mui/material';
import { useField } from 'formik';
import { camelCase, differenceWith, isEmpty, isEqual, pick } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { LineIcon } from '@metafox/ui';
import { useGlobal } from '@metafox/framework';
import ErrorMessage from '../ErrorMessage';
import ItemField from './ItemField';

enum TypeStatus {
  Create = 'new',
  Remove = 'remove',
  Edit = 'update'
}

const BtnMoreAnswer = styled(Button)(({ theme }) => ({
  fontWeight: theme.typography.fontWeightMedium,
  fontSize: theme.mixins.pxToRem(13),
  color: theme.palette.primary.main,
  cursor: 'pointer',
  display: 'flex',
  alignItems: 'center',
  marginTop: theme.spacing(0.5),
  width: 'fit-content',
  '&:hover': {
    textDecoration: 'underline'
  }
}));

const StyledIconClose = styled('div', {
  name: 'AnswerItem',
  slot: 'IconClose',
  shouldForwardProp: prop => prop !== 'enable'
})<{ enable: boolean }>(({ theme, enable }) => ({
  cursor: enable ? 'pointer' : 'default',
  pointerEvents: enable ? 'auto' : 'none',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  width: theme.spacing(5),
  height: theme.spacing(5),
  margin: theme.spacing(0, 0.5),
  '& .ico': {
    fontSize: theme.mixins.pxToRem(16),
    color: theme.palette.text.hint
  }
}));

const CustomField = ({
  config,
  disabled: forceDisabled,
  name,
  formik
}: FormFieldProps) => {
  const {
    margin,
    disabled,
    fullWidth,
    hiddenLabel,
    required,
    variant,
    size,
    style,
    minLength,
    sxFieldWrapper,
    sortable = true
  } = config;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const [field, meta, { setValue, setTouched }] = useField(
    name ?? 'DropdownField'
  );

  const { i18n } = useGlobal();
  const prevDataRef = React.useRef(field.value);

  React.useEffect(() => {
    if (!field.value) {
      setValue([
        {
          label: '',
          status: TypeStatus.Create,
          ordering: field.value?.length || 1
        }
      ]);
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [field.value]);

  const handleAddAnswer = () => {
    const newOrdering =
      field.value?.[field.value?.length - 1]?.ordering + 1 || 1;
    const newValue = [
      ...field.value,
      {
        label: '',
        status: TypeStatus.Create,
        ordering: newOrdering
      }
    ];

    setValue(newValue);
  };

  const onChangeTextField = (e, index) => {
    const newArr = [...field.value];
    newArr[index].label = e.target.value;

    if (field.value[index].status !== TypeStatus.Create)
      newArr[index].status = TypeStatus.Edit;

    setValue(newArr);
  };

  const handleRemove = index => {
    if (formik.isSubmitting) return;

    const newArr = [...field.value];

    if (field.value[index].status === TypeStatus.Create) {
      newArr.splice(index, 1);
    } else {
      newArr[index].status = TypeStatus.Remove;
    }

    setValue(newArr);
  };

  const moveRow = (dragIndex: number, hoverIndex: number) => {
    let newArr = [...field.value];
    newArr.splice(hoverIndex, 0, newArr.splice(dragIndex, 1)[0]);

    newArr = newArr?.map((item, idx) => ({
      ...item,
      ordering: idx + 1
    }));
    setValue(newArr);
  };

  const handleDrop = () => {
    if (isEmpty(field.value) || isEqual(prevDataRef.current, field.value))
      return;

    try {
      const differences = differenceWith(
        prevDataRef.current,
        field.value,
        isEqual
      );

      let newArr = [...field.value];
      newArr = newArr?.map(item => {
        const indexItemDiff = differences.findIndex(
          difference => item?.id === difference?.id
        );
        const indexItemPrev = prevDataRef.current.findIndex(
          prev => item?.id === prev?.id
        );

        if (
          item?.id &&
          item?.status !== TypeStatus.Remove &&
          indexItemDiff > -1
        ) {
          if (
            isEqual(
              pick(prevDataRef.current?.[indexItemPrev], [
                'id',
                'ordering',
                'label'
              ]),
              pick(item, ['id', 'ordering', 'label'])
            )
          ) {
            delete item.status;

            return item;
          }

          return { ...item, status: TypeStatus.Edit };
        }

        return item;
      });

      setValue(newArr);
    } catch (err) {}
  };

  const renderRow = (option, index) => {
    if (!option || option?.status === TypeStatus.Remove) return null;

    return (
      <ItemField
        sortable={sortable}
        key={option?.id}
        index={index}
        id={option?.id}
        error={meta.error && meta.error[index]?.label}
        moveRow={moveRow}
        handleDrop={handleDrop}
        disable={formik.isSubmitting}
      >
        <Box sx={{ display: 'flex', width: '100%' }}>
          <TextField
            size="small"
            fullWidth
            id="outlined-basic"
            value={option.label}
            variant="outlined"
            onChange={e => onChangeTextField(e, index)}
            error={meta?.error?.length && meta.error[index]}
            disabled={formik.isSubmitting}
          />

          <StyledIconClose
            onClick={() => handleRemove(index)}
            enable={
              field.value.filter(item => item.status !== TypeStatus.Remove)
                .length > minLength
            }
          >
            <Tooltip
              title={i18n.formatMessage({ id: 'remove' })}
              placement="top"
            >
              <LineIcon icon="ico-close" />
            </Tooltip>
          </StyledIconClose>
        </Box>
        {!sortable && meta.error && (
          <Box ml={1}>
            <ErrorMessage error={meta.error[index]?.label} />
          </Box>
        )}
      </ItemField>
    );
  };

  return (
    <FormControl
      margin={margin ?? 'none'}
      disabled={disabled}
      fullWidth={fullWidth}
      hiddenLabel={hiddenLabel}
      required={required}
      style={style}
      size={size}
      variant={variant as any}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
    >
      <Box ml={2}>
        {(field.value || []).map((option, index) => renderRow(option, index))}
        <BtnMoreAnswer
          onClick={handleAddAnswer}
          variant="text"
          color="primary"
          size="small"
          disabled={formik.isSubmitting}
        >
          {i18n.formatMessage({ id: 'add_options' })}
        </BtnMoreAnswer>
      </Box>
    </FormControl>
  );
};

export default CustomField;
