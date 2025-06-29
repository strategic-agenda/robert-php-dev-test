import { Route, Routes } from "react-router"
import TranslationUnitList from "./pages/Translations/TranslationUnitList"

function App() {
  return (
    <>
      <Routes>
        <Route path={'/'} element={<TranslationUnitList/>}></Route>
      </Routes>
    </>
  )
}

export default App
