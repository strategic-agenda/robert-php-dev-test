import React from 'react';
import TranslationUnitList from './TranslationUnitList';
import { LanguageProvider } from './contexts/LanguageContext';

function App() {
  return (
    <LanguageProvider>
      <div className="w-full">
        <h1 className="ps-4 text-3xl font-bold">Robert CAT Tool</h1>
        <TranslationUnitList />
      </div>
    </LanguageProvider>
  );
}

export default App;
