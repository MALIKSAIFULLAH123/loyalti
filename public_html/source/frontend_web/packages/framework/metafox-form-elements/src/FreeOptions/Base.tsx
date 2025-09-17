/**
 * @type: formElement
 * name: form.element.FreeOptions
 * chunkName: formExtras
 */
import { FormControl, FormLabel, Box, styled, Button } from '@mui/material';
import { useField } from 'formik';
import { camelCase, uniqueId, get, range, isObject } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import ItemOption from './ItemOption';
import produce from 'immer';
import Description from '../Description';

enum TypeStatus {
  Create = 'new',
  Remove = 'remove',
  Edit = 'update'
}

const name = 'FreeOptionsField';

const FormLabelStyled = styled(FormLabel, {
  name
})(({ theme }) => ({
  display: 'block',
  marginBottom: theme.spacing(1)
}));

const MoreButton = styled(Button, { name, slot: 'MoreButton' })(
  ({ theme }) => ({
    marginTop: theme.spacing(0.5)
  })
);

type ConfigProps = {
  translatableOptions: Array<string>;
  collapseOptions?: boolean | { less_label?: string; more_label?: string };
};

const FreeOptionsField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps<ConfigProps>) => {
  const {
    label,
    margin,
    disabled: disabledProp,
    fullWidth,
    required,
    variant,
    size,
    style,
    minLength,
    sxFieldWrapper,
    sortable,
    translatableOptions,
    description,
    collapseOptions
  } = config;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const [field, meta, { setValue, setTouched }] = useField(
    name ?? 'DropdownField'
  );
  const disabled = forceDisabled || disabledProp || formik.isSubmitting;
  const fieldValue = field?.value || [];
  const hasTranslatable = !!translatableOptions;

  const { i18n } = useGlobal();

  React.useEffect(() => {
    if (!field?.value && minLength > 0) {
      setValue(
        range(0, minLength).map((x, index) => ({
          uid: uniqueId('freeOption'),
          value: hasTranslatable ? {} : '',
          status: TypeStatus.Create,
          ordering: index + 1
        }))
      );
    }
  }, []);

  const handleAddAnswer = () => {
    const newValue = [
      ...fieldValue.filter(x => x.status !== TypeStatus.Remove),
      {
        uid: uniqueId('freeOption'),
        value: '',
        status: TypeStatus.Create
      },
      ...fieldValue.filter(x => x.status === TypeStatus.Remove)
    ].map((x, index) => ({ ...x, ordering: index + 1 }));

    setValue(newValue);
  };

  const onChangeTextField = (value, index) => {
    if (fieldValue[index]) {
      setValue(
        produce(fieldValue, draft => {
          draft[index] = {
            status: TypeStatus.Edit,
            ...draft[index],
            value: isObject(value)
              ? { ...draft[index]?.value, ...value }
              : value
          };
        })
      );
    }
  };

  const onRemove = index => {
    if (disabled) return;

    if (fieldValue[index]) {
      setValue(
        produce(fieldValue, draft => {
          if (draft[index].status === TypeStatus.Create) {
            draft.splice(index, 1);
          } else {
            draft[index].status = TypeStatus.Remove;
            draft.push(draft.splice(index, 1)[0]);
          }

          draft.forEach((x, index) => {
            const newOrdering = index + 1;

            if (draft[index].ordering !== newOrdering) {
              draft[index].ordering = newOrdering;
              draft[index].status = draft[index].status ?? TypeStatus.Edit;
            }
          });
        })
      );
    }
  };

  const onMoveRow = (dragIndex: number, hoverIndex: number) => {
    let newArr = [...field.value];
    newArr.splice(hoverIndex, 0, newArr.splice(dragIndex, 1)[0]);

    newArr = newArr?.map((item, idx) => ({
      status: TypeStatus.Edit,
      ...item,
      ordering: idx + 1
    }));
    setValue(newArr);
  };

  const handleDrop = () => {};

  const canRemoveItem =
    fieldValue.filter(item => item.status !== TypeStatus.Remove).length >
    minLength;

  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );

  return (
    <FormControl
      margin={margin ?? 'none'}
      disabled={disabled}
      fullWidth={fullWidth}
      required={required}
      style={style}
      size={size}
      variant={variant as any}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
    >
      <Box>
        {label ? (
          <FormLabelStyled focused={false} required={required || forceRequired}>
            {label}
          </FormLabelStyled>
        ) : null}
        {description ? <Description text={description} sx={{ mb: 1 }} /> : null}
        {fieldValue.map((option, index) => (
          <ItemOption
            index={index}
            option={option}
            error={haveError ? get(meta.error, `${index}`) : undefined}
            disabled={disabled}
            onChange={value => onChangeTextField(value, index)}
            onRemove={() => onRemove(index)}
            canRemove={canRemoveItem}
            sortable={sortable}
            key={option?.id || option?.uid}
            onMoveRow={onMoveRow}
            handleDrop={handleDrop}
            setTouched={setTouched}
            translatableOptions={translatableOptions}
            collapseOptions={collapseOptions}
          />
        ))}
        <MoreButton
          onClick={handleAddAnswer}
          variant="text"
          color="primary"
          size="small"
          disabled={disabled}
        >
          {i18n.formatMessage({ id: 'add_options' })}
        </MoreButton>
      </Box>
    </FormControl>
  );
};

export default FreeOptionsField;
