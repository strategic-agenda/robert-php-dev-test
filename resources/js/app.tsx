import React from 'react';
import { createRoot } from 'react-dom/client';
import TranslationUnits from './components/TranslationUnits';

// Assuming your blade has <div id="app"></div>
const container = document.getElementById('app');
if (container) {
    createRoot(container).render(<TranslationUnits />);
}
