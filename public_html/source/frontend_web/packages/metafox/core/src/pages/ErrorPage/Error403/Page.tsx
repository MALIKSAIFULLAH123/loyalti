/**
 * @type: route
 * name: core.error403
 * path: /permission-denied
 * bundle: web
 */
import { Page } from '@metafox/layout';
import * as React from 'react';

export default function ErrorPage() {
  return <Page pageName="core.error403" />;
}
