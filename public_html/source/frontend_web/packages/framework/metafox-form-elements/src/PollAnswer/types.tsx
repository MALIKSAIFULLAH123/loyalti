export interface AnswerPollProps {
  lastElement?: boolean;
  item: AnswerItemProps;
  handleDelete?: (order?: number) => void;
  handleAdd?: () => void;
  handleChange?: (e, id: number) => void;
  submitCount?: number;
  index?: number;
  error?: string;
  disabled?: boolean; // default false
  maxLength?: number;
  disabledAddMore?: boolean;
  disableRemove?: boolean;
  meta?: Record<string, any>;
  setTouched: (value: boolean, shouldValidate?: boolean) => void;
  name: string;
  fieldName: string;
}

export interface AnswerItemProps {
  id?: number;
  order?: number;
  answer: string;
}
