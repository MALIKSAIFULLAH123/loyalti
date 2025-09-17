import React from 'react';
import { LineIcon } from '@metafox/ui';

export default function ParentUser({ name }) {
  return (
    <>
      <LineIcon icon="ico-caret-right" />
      <b>{name}</b>
    </>
  );
}
