import React from 'react';
import { Routes, Route } from "react-router-dom";
import Home from './components/Home';
import EditTranslation from './components/EditTranslation';
import AddTranslation from './components/AddTranslation';

const App = () => {
  return (
    <>
      <blockquote className="text-2xl font-semibold italic text-center text-blue-500 my-10">
        <span className="mx-2 before:block before:absolute before:-inset-1 before:-skew-y-3 before:bg-pink-400 relative inline-block">
          <span className="relative text-white "> Translations App </span>
        </span>
      </blockquote>
      <Routes>
        <Route path='/' element={<Home />} />
        <Route path='/edit/:id' element={<EditTranslation />} />
        <Route path='/create' element={< AddTranslation />} />
      </Routes>
    </>
  );
}

export default App;