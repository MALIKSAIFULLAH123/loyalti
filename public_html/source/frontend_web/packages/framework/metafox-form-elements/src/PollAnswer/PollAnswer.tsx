/**
 * @type: formElement
 * name: form.element.PollAnswer
 * chunkName: formElement
 */

import { FormFieldProps } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import { useField } from 'formik';
import produce from 'immer';
import { get, isArray } from 'lodash';
import React from 'react';
import ErrorMessage from '../ErrorMessage';
import AnswerItem from './AnswerItem';
import { AnswerItemProps, AnswerPollProps } from './types';

const PollAnswer = ({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) => {
  const { i18n, dialogBackend } = useGlobal();
  const { minAnswers, maxAnswers, disabled, maxLength } = config;
  const [field, meta, { setValue, setTouched }] = useField(
    name ?? 'PollAnswer'
  );
  const [answer, setAnswer] = React.useState<AnswerItemProps[]>(field?.value);
  const key = React.useRef(field?.value?.length || 0);
  const handleDelete = React.useCallback(
    (order: number) => {
      setAnswer(prev =>
        produce(prev, draft => {
          if (draft.length === minAnswers) {
            dialogBackend.alert({
              message: i18n.formatMessage(
                { id: 'you_must_have_min_answer' },
                { value: minAnswers }
              )
            });
          } else if (order) {
            const itemIndex = draft.findIndex(item => item.order === order);
            draft.splice(itemIndex, 1);
          } else {
            draft.pop();
          }
        })
      );
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const handleChange = React.useCallback(
    (e, index: number) => {
      const answer = e.target.value;

      setAnswer(prev =>
        produce(prev, draft => {
          draft[index] = { ...draft[index], answer };
        })
      );
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const handleAdd = React.useCallback(() => {
    setAnswer(prev =>
      produce(prev, draft => {
        if (draft.length === maxAnswers) {
          dialogBackend.alert({
            message: i18n.formatMessage(
              { id: 'you_must_have_max_answer' },
              { value: maxAnswers }
            )
          });
        } else {
          const AnswerItem: AnswerPollProps = {
            item: {
              answer: '',
              order: ++key.current
            }
          };
          draft.push(AnswerItem.item);
        }
      })
    );
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (answer.length < minAnswers) handleAdd();
    else {
      // trim()
      const results = answer.map(item => {
        return { ...item, answer: item.answer.trim() };
      });
      setValue(results);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [answer]);

  const isEachFieldError = isArray(meta.error);

  return (
    <div>
      {answer?.map((item, index) => (
        <AnswerItem
          lastElement={index === answer.length - 1}
          index={index}
          key={item.order.toString()}
          item={item}
          maxLength={maxLength}
          handleAdd={handleAdd}
          submitCount={formik.submitCount}
          meta={meta}
          setTouched={setTouched}
          handleDelete={handleDelete}
          disabled={disabled || forceDisabled || formik.isSubmitting}
          handleChange={e => handleChange(e, index)}
          error={get(meta.error, `[${index}].answer`)}
          disabledAddMore={answer.length === maxAnswers}
          disableRemove={answer.length === minAnswers}
          name={`${name}_${index + 1}`}
          fieldName={name}
        />
      ))}
      {meta.error && !isEachFieldError ? (
        <ErrorMessage error={meta.error?.toString()} />
      ) : null}
    </div>
  );
};

export default PollAnswer;
