/**
 * @type: ui
 * name: media.ui.viewBlockMobileModal
 */
import { useGlobal, connectSubject } from '@metafox/framework';
import { MediaViewModalProps } from '@metafox/photo/types';
import * as React from 'react';
import {
  actionCreators,
  connectItemView
} from '@metafox/photo/hocs/connectPhoto';

function Base(props: MediaViewModalProps) {
  const { jsxBackend } = useGlobal();

  const PhotoItemViewMobile = jsxBackend.get('media.ui.viewBlockMobile');

  return <PhotoItemViewMobile {...props} />;
}

const Enhance = connectSubject(
  connectItemView(Base, actionCreators, {
    tags: true,
    categories: true
  })
);

export default Enhance;
