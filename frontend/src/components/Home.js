import axios from "axios";
import { useEffect, useState } from "react";
import TranslationList from "./TranslationList";
import AddTranslation from "./AddTranslation";

const Home = () => {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true)
    axios.get("http://localhost/robert-php-dev-test/api/translations.php")
      .then(response => {
        setUsers(response.data)
      })
    setLoading(false)
  }, [])

  const deleteUser = (id) => {
    axios.delete(`http://localhost/robert-php-dev-test/api/translations.php`).then((response) => {
      const newUser = users.filter((user) => user.id !== id);
      console.log(response)
      setUsers(newUser);
    }).catch(error => {
      console.log(error)
    });
  }

  return (
    <div className="max-w-xl pb-8 mx-auto px-5 bg-slate-100">
      <TranslationList
        users={users}
        loading={loading}
        deleteUser={deleteUser}
      />
      <AddTranslation
        users={users}
        setUsers={setUsers}
      />
    </div>
  );
}

export default Home;