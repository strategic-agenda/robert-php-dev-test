import logo from './logo.svg';
import Header from "./components/body/Header";
import Index from "./components/Index";
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import Edit from "./components/translation/Edit";
import Add from "./components/language/Add";
import Translator from "./components/translation/Translator";

function App() {
  return (
    <div className="App">
        <Header />
        <div className="content">
            <div className="container">
                    <Routes>
                        <Route path="/" element={<Index />} />
                        <Route path="/tranlation/create" element={<Edit />} />
                        <Route path="/language/add" element={<Add />} />
                        <Route path="/translator" element={<Translator />} />
                    </Routes>
            </div>
        </div>
    </div>
  );
}

export default App;
