import axios from "axios";
import { useEffect, useState } from "react";
import List from "./translations/List";
import Add from "./translations/Add";

const Home = () => {
  const [translations, setTranslations] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true)
    axios.get("http://localhost/robert-php-dev-test/api/translations.php")
      .then(response => {
        setTranslations(response.data)
      })
    setLoading(false)
  }, [])

  const deleteTranslation = (id) => {
    axios.delete(`http://localhost/robert-php-dev-test/api/translations.php`, { data: {
      id
    } }).then((response) => {
      const newTranslation = translations.filter((translation) => translation.id !== id);
      console.log(response)
      setTranslations(newTranslation);
    }).catch(error => {
      console.log(error)
    });
  }

  return (
    <div className="max-w-xl pb-8 mx-auto px-5 bg-slate-100">
      <List
        translations={translations}
        loading={loading}
        deleteTranslation={deleteTranslation}
      />
      <Add
        translations={translations}
        setTranslations={setTranslations}
      />
    </div>
  );
}

export default Home;