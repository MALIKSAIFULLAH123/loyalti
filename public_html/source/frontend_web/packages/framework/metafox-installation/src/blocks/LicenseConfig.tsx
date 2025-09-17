/**
 * @type: block
 * name: install.LicenseConfig
 * bundle: installation
 */
import React from 'react';
import { InstallForm } from '../components';
import { createBlock } from '@metafox/framework';
import formSchema from '../forms/license.json';

const LicenseConfig = () => {
  return <InstallForm formSchema={formSchema} />;
};

export default createBlock({
  extendBlock: LicenseConfig
});
