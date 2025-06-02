// App.js
import React from 'react';
import TranslationList from './components/TranslationList';
import { Card, CardContent, Typography } from '@mui/material';

function App() {
  return (

      <CardContent>
        <Typography variant="h4" gutterBottom>
          Robert CAT Tool
        </Typography>
        <TranslationList />
      </CardContent>
 
  );
}

export default App;
