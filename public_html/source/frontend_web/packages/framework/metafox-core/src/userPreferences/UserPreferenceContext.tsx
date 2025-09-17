import { UserPreferenceConfig } from '@metafox/framework';
import React from 'react';

const UserPreferenceContext = React.createContext<UserPreferenceConfig>({});

export default UserPreferenceContext;
