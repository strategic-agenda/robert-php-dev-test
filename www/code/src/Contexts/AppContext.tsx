import { createContext } from 'react';

import AppContextInterface from '../Interfaces/AppContextInterface';

export const AppContextDefaultContent = {
    location: window.location.pathname,
    translations: {
        system: {},
        customer: {}
    }
}

export const AppContext = createContext<AppContextInterface>(AppContextDefaultContent);
