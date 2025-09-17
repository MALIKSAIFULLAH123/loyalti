import React from 'react';
import { isObject } from 'lodash';

const parseError = error => {
  if (!error) return null;

  if (isObject(error)) {
    return parseError(Object.values(error)[0]);
  }

  return error.toString();
};

export default function ErrorMessage({
  error,
  className = 'invalid-feedback order-last'
}: {
  name?: string;
  error?: string;
  className?: string;
  component?: React.ElementType;
}) {
  return error ? (
    <div data-testid="error" className={className}>
      {parseError(error)}
    </div>
  ) : null;
}
