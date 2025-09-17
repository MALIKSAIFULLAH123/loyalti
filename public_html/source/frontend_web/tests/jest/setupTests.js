/**
 * @testEnvironmentOptions.url http://localhost
 */
import '@testing-library/jest-dom';
import '@testing-library/jest-dom/extend-expect';
import '@testing-library/react';
import { Manager } from '@metafox/framework/Manager';
import fallbacks from '@metafox/react-app/bundle-web/mockServices';

window.scrollTo = () => {};

// mock to manager fallback services

Manager.fallbacks = fallbacks;
