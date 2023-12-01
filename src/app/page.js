import TranslationList from './components/business/translations/List'
import Link from 'next/link';


export default function Home() {
  return (
    <main className="flex min-h-screen flex-col items-center p-24 container mx-auto">
      <h1 className='text-3xl'>Translation units list</h1>
      <TranslationList />
      <button className='mt-5 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded'>
        <Link href='/translations/new'>
          Add new Unit
        </Link>
      </button>
    </main>
  )
}
