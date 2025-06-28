import axios from "axios";
import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router";

const EditTranslation = () => {
  const [data, setData] = useState([])
  const [source, setSource] = useState('');
  const [translation, setTranslation] = useState('');

  const { id } = useParams();
  const navigate = useNavigate()

  useEffect(() => {
    setSource(localStorage.getItem("source"));
    setTranslation(localStorage.getItem("translation"));
  }, [id])

  const handleSubmit = (e) => {
    e.preventDefault()
    axios.put("http://localhost/robert-php-dev-test/api/translations.php", {
      id,
      source,
      translation
    })
      .then(response => {
        setData(response.data);
        navigate('/')
      })
  }

  const goHome = () => {
    navigate('/')
  }

  return (
    <div className="flex items-center justify-center">
      <form onSubmit={handleSubmit}>
        <div className='flex flex-col justify-center items-center bg-slate-100 p-10 mt-10 rounded-md'>
          <input
            className='my-2 px-5 py-1 rounded-full border border-gray-600'
            type="text" placeholder="Source"
            value={source}
            onChange={(e) => setSource(e.target.value)}
          />
          <input
            className='my-2 px-5 py-1 rounded-full border border-gray-600'
            type="text" placeholder="Translation"
            value={translation}
            onChange={(e) => setTranslation(e.target.value)}
          />
          <div className="flex my-2">
            <button
              className='text-white mx-1 px-5 py-1 rounded-full bg-blue-500 hover:bg-blue-700'>
              EDIT
            </button>
            <button
              onClick={goHome}
              className='text-white mx-1 px-5 py-1 rounded-full bg-blue-500 hover:bg-blue-700'
            >
              CANCEL
            </button>
          </div>
        </div>
      </form>
    </div>

  );
}

export default EditTranslation;