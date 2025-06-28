import { Link } from "react-router-dom";

const TranslationList = ({ translations, loading, deleteTranslation }) => {
  const editTranslation = (translation) => {
    console.log(translation);
    localStorage.setItem("source", translation.source);
    localStorage.setItem("translation", translation.translation);

  }
  return (
    <div className='py-5'>
      {loading && <p>loading ...</p>}
      {translations &&
        <ul>
          {(translations.map((translation) =>
            <li className='flex justify-between border-b-4' key={translation.id}>
              <div className="flex ">
                <p className='my-3 px-3'>{translation.source}</p>
              </div>
              <div>
                <Link to={`/edit/${translation.id}`}>
                  <button
                    className='mx-2 my-3 px-2 py-1 text-green-800
                       hover:bg-green-400 hover:rounded-md hover:border hover:border-green-800'
                       onClick={()=>editTranslation(translation)}
                  >EDIT</button>
                </Link>
                <button
                  className='my-3 px-2 py-1 text-red-800 
                        hover:bg-red-400 hover:rounded-md hover:border hover:border-red-800'
                  onClick={() => deleteTranslation(translation.id)}
                >Delete</button>
              </div>
            </li>
          ))}
        </ul >
      }
    </div >
  )
}

export default TranslationList;