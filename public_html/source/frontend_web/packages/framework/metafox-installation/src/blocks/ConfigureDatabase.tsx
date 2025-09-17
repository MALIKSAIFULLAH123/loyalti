/**
 * @type: block
 * name: install.DatabaseConfig
 * bundle: installation
 */
import React from 'react';
import { InstallForm } from '../components';
import { createBlock } from '@metafox/framework';
import formSchema from '../forms/database.json';

const ConfigureDatabase = () => {
  return <InstallForm formSchema={formSchema} />;
};

export default createBlock({
  extendBlock: ConfigureDatabase
});
