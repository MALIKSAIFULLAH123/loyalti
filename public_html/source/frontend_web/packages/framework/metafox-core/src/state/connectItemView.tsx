import {
  getAttachmentsSelector,
  getCategoriesSelector,
  getExtraDataSelector,
  getItemSelector,
  getPhotoSelector,
  getPhotosSelector,
  getPollAnswersSelector,
  getQuizQuestionSelector,
  getUserSelector,
  GlobalState,
  ItemViewBaseProps,
  useGlobal
} from '@metafox/framework';
import { ItemUserShape } from '@metafox/ui';
import React from 'react';
import { useSelector } from 'react-redux';

export default function connectItemView<ItemShape = any>(
  BaseView: ItemViewBaseProps,
  actionCreators: any,
  features?: Partial<
    Record<
      | 'categories'
      | 'photos'
      | 'photo'
      | 'poll_answer'
      | 'quiz_question'
      | 'attachments'
      | 'extra_data',
      boolean
    >
  >
) {
  const ConnectItemView = (props: any) => {
    const { useActionControl } = useGlobal();
    const { identity } = props;
    let categories = undefined;
    let photos = undefined;
    let photo = undefined;
    let pollAnswers = undefined;
    let questions = undefined;
    let extra_data = undefined;
    let attachments = undefined;

    const item = useSelector<GlobalState>(state =>
      getItemSelector(state, identity)
    ) as ItemShape;

    const user = useSelector<GlobalState>(state =>
      getUserSelector(state, item?.user)
    ) as ItemUserShape;

    if (features?.extra_data) {
      // eslint-disable-next-line react-hooks/rules-of-hooks
      extra_data = useSelector<GlobalState>(state =>
        getExtraDataSelector(state, item?.extra_data)
      ) as ItemUserShape;
    }

    if (features?.photos) {
      // eslint-disable-next-line react-hooks/rules-of-hooks
      photos = useSelector<GlobalState>(state =>
        getPhotosSelector(state, item?.photos || item?.items)
      );
    }

    if (features?.photo) {
      // eslint-disable-next-line react-hooks/rules-of-hooks
      photo = useSelector<GlobalState>(state =>
        getPhotoSelector(state, item?.photo)
      );
    }

    if (features?.categories) {
      // eslint-disable-next-line react-hooks/rules-of-hooks
      categories = useSelector<GlobalState, any[]>(state =>
        getCategoriesSelector(state, item?.categories)
      );
    }

    if (features?.poll_answer) {
      // eslint-disable-next-line react-hooks/rules-of-hooks
      pollAnswers = useSelector<GlobalState>(state =>
        getPollAnswersSelector(state, item?.answers)
      );
    }

    if (features?.quiz_question) {
      // eslint-disable-next-line react-hooks/rules-of-hooks
      questions = useSelector<GlobalState>(state =>
        getQuizQuestionSelector(state, item?.questions)
      );
    }

    if (features?.attachments) {
      // eslint-disable-next-line react-hooks/rules-of-hooks
      attachments = useSelector<GlobalState>(state =>
        getAttachmentsSelector(state, item?.attachments)
      );
    }

    const [handleAction, state, setState, actions] = useActionControl<
      unknown,
      unknown
    >(identity, {}, actionCreators);

    return (
      <BaseView
        {...props}
        photo={photo}
        photos={photos}
        answers={pollAnswers}
        extra_data={extra_data}
        categories={categories}
        attachments={attachments}
        identity={identity}
        item={item}
        user={user}
        questions={questions}
        state={state}
        actions={actions}
        setState={setState}
        handleAction={handleAction}
      />
    );
  };

  ConnectItemView.displayName = `ConnectItemView(${BaseView.displayName})`;

  return ConnectItemView;
}
