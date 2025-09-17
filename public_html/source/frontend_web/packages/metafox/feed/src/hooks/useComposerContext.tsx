import React from 'react';
import { ComposerContext } from '../dialogs/StatusComposer/Base';

export default function useComposerContext() {
  return React.useContext(ComposerContext);
}
