import React from 'react';
import TranslationList from './TranslationList';
import './App.css';

function App() {
  return (
    <div className="min-h-screen bg-gray-50">
      <nav className="bg-white shadow-sm">
        <div className="container mx-auto px-4 py-4">
          <h1 className="text-xl font-bold text-gray-800">Robert Translation Tool</h1>
        </div>
      </nav>
      <main className="container mx-auto px-4 py-8">
        <TranslationList />
      </main>
    </div>
  );
}

export default App; 