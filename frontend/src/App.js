import React, {useState} from 'react';
import './App.css';
import {BrowserRouter as Router, Routes, Route, Link} from 'react-router-dom';
import TranslationList from './pages/TranslationList';
import LanguageManager from './pages/LanguageManager';
import ToastContainer from './components/ToastContainer';

function App() {
    const [activeTab, setActiveTab] = useState('translations');

    return (
        <Router future={{ v7_startTransition: true, v7_relativeSplatPath: true }}>
            <div className="App bg-gray-100 min-h-screen flex flex-col">
                <header className="bg-white shadow-md py-4 px-6">
                    <div className="container mx-auto flex flex-col md:flex-row justify-between items-center">
                        <div className="mb-4 md:mb-0">
                            <h1 className="text-3xl font-bold text-blue-700">Robert CAT Tool</h1>
                            <p className="text-gray-600">Computer-Assisted Translation Tool</p>
                        </div>
                        <nav className="flex space-x-2">
                            <Link
                                to="/"
                                onClick={() => setActiveTab('translations')}
                                className={`px-4 py-2 rounded-md transition-all ${
                                    activeTab === 'translations'
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                }`}
                            >
                                Translations
                            </Link>
                            <Link
                                to="/languages"
                                onClick={() => setActiveTab('languages')}
                                className={`px-4 py-2 rounded-md transition-all ${
                                    activeTab === 'languages'
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                }`}
                            >
                                Languages
                            </Link>
                        </nav>
                    </div>
                </header>

                <main className="container mx-auto py-8 px-4 flex-grow">
                    <Routes>
                        <Route path="/" element={<TranslationList/>}/>
                        <Route path="/languages" element={<LanguageManager/>}/>
                    </Routes>
                </main>

                <footer className="bg-white border-t border-gray-200 py-6 mt-auto">
                    <div className="container mx-auto text-center text-gray-500 text-sm">
                        <p>© {new Date().getFullYear()} Robert CAT Tool</p>
                        <p className="mt-1">A powerful tool for computer-assisted translation</p>
                    </div>
                </footer>

                {/* Toast Notifications */}
                <ToastContainer/>
            </div>
        </Router>
    );
}

export default App;
