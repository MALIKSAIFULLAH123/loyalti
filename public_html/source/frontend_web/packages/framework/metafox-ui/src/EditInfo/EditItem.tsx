import { Link, useGlobal, useResourceAction } from '@metafox/framework';
import { SmartFormBuilder } from '@metafox/form';
import { Button, Skeleton, styled, Box } from '@mui/material';
import React, { useState } from 'react';
import LoadingComponent from './LoadingComponent';
import useStyles from './styles';
import HtmlViewer from '@metafox/html-viewer';
import produce from 'immer';
import { compactUrl } from '@metafox/utils';
import { ButtonAction } from '@metafox/ui';
import { camelCase } from 'lodash';

interface IProps {
  [key: string]: any;
}

const DescriptionPrivacy = styled(Box)(({ theme }) => ({
  fontSize: theme.typography.body2.fontSize
}));

const mappingDisplayValue = ({
  type,
  value,
  options,
  prefix,
  defaultValue
}: {
  type: string;
  value: any;
  option: Array<any>;
  prefix: string;
  defaultValue: any;
}) => {
  switch (type) {
    case 'Select': {
      return options.find(opt => opt.value === value)?.label;
    }
    case 'Textarea':
      return <HtmlViewer html={value || ''} />;
    case 'Editor': {
      return <HtmlViewer html={value || ''} />;
    }
    case 'RadioGroup':
      return (
        <>
          <span>{options[value].label}</span>
          {options[value]?.description && (
            <DescriptionPrivacy>
              {options[value].description}
            </DescriptionPrivacy>
          )}
        </>
      );

    case 'Location':
      return value?.address;

    case 'Password':
      return '**************';

    default: {
      return value ? `${prefix ?? ''}${value}` : defaultValue;
    }
  }
};

export default function EditItem({
  data,
  testid,
  loaded,
  setListData,
  appName = '',
  resourceName = '',
  index
}: IProps) {
  const classes = useStyles();

  const { usePageParams, i18n, dialogBackend, dispatch } = useGlobal();
  const {
    label,
    type,
    value,
    action,
    options,
    contextualDescription,
    defaultValue,
    name,
    dialog = false
  } = data || {};

  const [isEdit, setEdit] = useState(false);

  const params = usePageParams();

  const pageParams = { ...params, ...data };

  const dataSource = useResourceAction(appName, resourceName, action) || {
    apiUrl: compactUrl(action, pageParams)
  };

  const onSuccess = (formValue, meta) => {
    if (formValue)
      setListData(prev =>
        produce(prev, draft => {
          draft[index].value = formValue[name];
        })
      );

    setEdit(false);
  };

  const modifiedValue = mappingDisplayValue({
    type,
    value,
    options,
    prefix: contextualDescription,
    defaultValue
  });

  const onClickEdit = () => {
    if (dialog) {
      dialogBackend.present({
        component: 'core.dialog.MultiStepForm',
        props: {
          pageParams,
          dataSource,
          onSuccess
        }
      });

      return;
    }

    setEdit(true);
  };

  const actionButton = onSuccess => {
    dispatch({
      type: `${appName}/${action}`,
      payload: {
        onSuccess,
        dataSource
      }
    });
  };

  if (type === 'LinkButton') {
    const { to, color, ...rest } = data?.typeProps || {};

    if (to)
      return (
        <div className={classes.item} data-testid={name}>
          {loaded ? (
            <Link to={to} color={color} {...rest}>
              {label || i18n.formatMessage({ id: 'cancel_account' })}
            </Link>
          ) : (
            <Skeleton variant="text" width={100} />
          )}
        </div>
      );

    return (
      <div className={classes.item} data-testid={name}>
        <ButtonAction action={actionButton} color={color} sx={{ ...rest }}>
          {label}
        </ButtonAction>
      </div>
    );
  }

  return (
    <div
      className={classes.item}
      role="form"
      data-testid={camelCase(` edit ${name} section`)}
    >
      <div className={classes.itemText}>
        <div className={classes.itemTitle}>{label}</div>
        <div className={classes.itemContent}>
          {isEdit ? (
            <SmartFormBuilder
              pageParams={pageParams}
              dataSource={dataSource}
              onCancel={() => setEdit(false)}
              onSuccess={onSuccess}
              loadingComponent={LoadingComponent}
            />
          ) : (
            <div>
              {loaded ? (
                <p>{modifiedValue}</p>
              ) : (
                <Skeleton variant="text" width={100} />
              )}
            </div>
          )}
        </div>
      </div>
      <div className={classes.itemButton}>
        {!isEdit && (
          <Button
            data-testid="buttonEdit"
            disabled={!loaded}
            className={classes.btnEdit}
            size={'medium'}
            variant={'outlined'}
            color={'primary'}
            onClick={onClickEdit}
          >
            {i18n.formatMessage({ id: 'edit' })}
          </Button>
        )}
      </div>
    </div>
  );
}
