import AdminCP from '@metafox/framework/admincp/AdminCP';
import React from 'react';
import { createRoot } from 'react-dom/client';

const container = document.getElementById('root');
const root = createRoot(container!);

root.render(<AdminCP/>);
