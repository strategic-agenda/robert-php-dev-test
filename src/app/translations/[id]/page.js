'use client'
 
import { useRouter } from 'next/navigation'
import TranslationSaveForm from '../../components/business/translations/SaveForm'


export default function SaveFormPage() {
  const router = useRouter();

  const handleBack = () => {
    router.push('/');
  };

  return (
    <main className="flex min-h-screen container mx-auto flex-col items-start p-24">
      <button
        onClick={handleBack}
        className=" mb-3 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
      >
        Back
      </button>
     <TranslationSaveForm />
    </main>
  )
}
