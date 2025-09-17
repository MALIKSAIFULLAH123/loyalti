import { ConfirmParams, DialogItemContext } from '@metafox/dialog';
import { RemoteDataSource } from '@metafox/framework';
import { FetchDataConfig } from '@metafox/rest-client';
import { FormikHelpers, FormikProps } from 'formik';
import React from 'react';

export type FormElementType = 'field' | 'container' | undefined;

export interface FormFieldProps<T extends {} = {}> {
  config: T & FormElementShape;
  name?: string;
  disabled?: boolean;
  required?: boolean;
  acceptTypeFile?: any;
  formik?: FormikProps<any>;
  aspectRatio?: any;
  widthPhoto?: string;
}

export interface FormElementShape {
  testid?: string;
  container?: boolean;
  component?: string;
  name?: string;
  label?: string;
  noFeedback?: boolean;
  description?: string;
  placeholder?: string;
  required?: boolean;
  showWhen?: any;
  enabledWhen?: any;
  tagName?: string;
  wrapAs?: React.ElementType;
  template?: string;
  elements?: Record<string, FormElementShape>;
  variant?: 'text' | 'outlined' | 'contained' | string;
  labelProps?: { shrink?: boolean; [key: string]: any };
  margin?: 'normal' | 'dense' | 'none';
  fullWidth?: boolean;
  defaultValue?: any;
  prefix?: string;
  [key: string]: any;
}

export type BreadcrumbsShape = {
  label: string;
  link: string;
  separator?: string;
}[];

export interface FormSchemaShape extends FormElementShape {
  dialog?: boolean;
  closeDialog?: () => void;
  noBreadcrumb?: boolean;
  breadcrumbs?: BreadcrumbsShape;
  noHeader?: boolean;
  acceptPageParams?: string[]; // which page params can be merged to form initialValues, allow dot separator props.
  successAction?: string;
  backProps?: { label: string; url?: string };
  failureAction?: string;
  submitUrl?: string;
  onCancel?: () => void;
  onReset?: () => void;
  submitOnValueChanged?: boolean;
  keepPaginationData?: boolean;
}

interface FormBuilderBase<T extends object = object> {
  readonly initialValues?: T;
  breadcrumbs?: BreadcrumbsShape;
  readonly submitAction?: string;
  readonly successAction?: string;
  readonly failureAction?: string;
  readonly onUpdate?: (values: any) => void;
  readonly onSubmit?: (
    values: T,
    form: FormikHelpers<T>
  ) => void | Promise<any>;
  readonly onChange?: (values: unknown) => void;
  readonly changeEventName?: string; // see evenCenter.dispatch
  readonly dialog?: boolean; // present form as dialog
  readonly noBreadcrumb?: boolean;
  readonly noHeader?: boolean;
  readonly fixedFooter?: boolean;
  readonly noTitle?: boolean;
  readonly disableFormOnSuccess?: boolean;
  readonly onCancel?: () => void;
  readonly onSubmitting?: () => void;
  readonly onReset?: () => void;
  readonly onSuccess?: (values?: unknown) => void;
  readonly onFailure?: (error: any) => void;
  isSidePlacement?: boolean;
  validationSchema?: any;
  dialogItem?: DialogItemContext; // when dialog item is truthy
  dialogTitle?: string; // when dialog is truthy
  pageParams?: FetchDataConfig['pageParams'];
  resetFormWhenSuccess?: boolean;
  resetDirtyWhenSuccess?: boolean;
  reloadFormWhenSuccess?: boolean;
  keepPaginationData?: boolean;
  dialogEmbedItem?: React.FunctionComponent;
  navigationConfirmWhenDirty?: ConfirmParams | boolean;
  formContext?: Record<string, any>;
  debounceSubmitTime?: number;
}

export interface FormRefHandler {
  submit: () => void;
}

export interface FormBuilderProps<T extends object = object>
  extends FormBuilderBase<T> {
  readonly name?: string; // overwrite form name
  readonly formSchema: FormSchemaShape;
  readonly validateOnMount?: boolean;
  readonly ref?: React.MutableRefObject<FormRefHandler>;
  children?: React.ReactNode;
}

export interface RemoteFormBuilderProps<T extends object = object>
  extends FormBuilderBase<T> {
  dataSource?: RemoteDataSource;
  pageParams?: FetchDataConfig['pageParams'];
  loadingComponent?: JSX.Element;
  readonly onLoaded?: ({ data: FormSchemaShape, meta: any }) => void;
  readonly onFailure?: (error: any) => void;
  forceReload?: boolean;
  preventReload?: boolean;
  hideWhenError?: boolean;
  allowRiskParams?: boolean;
}
