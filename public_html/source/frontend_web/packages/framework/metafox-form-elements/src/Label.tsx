import { useGlobal } from '@metafox/framework';
import React from 'react';

type Props = {
  text: string;
};
export default function Label({ text }: Props) {
  const { i18n } = useGlobal();

  if (!text) return null;

  if (text.startsWith('<html>')) {
    return (
      <>{i18n.formatMessage({ id: '[placeholder]', defaultMessage: text })}</>
    );
  }

  // eslint-disable-next-line react/jsx-no-useless-fragment
  return <>{text}</>;
}
