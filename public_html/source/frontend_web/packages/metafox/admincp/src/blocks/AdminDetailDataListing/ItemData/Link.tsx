/**
 * @type: ui
 * name: acp.detail.ui.link
 */

import { Link } from '@metafox/framework';
import React from 'react';

export default function DateItem({ link, target, value }) {
  if (!value) {
    return null;
  }

  return (
    <Link color="primary" target={target} to={link}>
      {value}
    </Link>
  );
}
