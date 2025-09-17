/**
 * @type: block
 * name: install.InfoConfig
 * bundle: installation
 */
import React from 'react';
import { InstallForm } from '../components';
import { createBlock } from '@metafox/framework';
import formSchema from '../forms/information.json';

const ConfigureInformation = () => {
  return <InstallForm formSchema={formSchema} />;
};

export default createBlock({
  extendBlock: ConfigureInformation
});
