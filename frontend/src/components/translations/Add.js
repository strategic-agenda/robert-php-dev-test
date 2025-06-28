import React, { useState } from 'react';
import axios from "axios";

const Add = ({ refreshTranslations, translations }) => {
	const [source, setSource] = useState("")
	const [translation, setTranslation] = useState("")

	const handleSubmit = (e) => {
		e.preventDefault();
		axios
			.post("http://localhost/robert-php-dev-test/api/v1/translations.php", { source, translation })
			.then((res) => {
				refreshTranslations();
				setSource('');
				setTranslation('');
			});
	};

	return (
			<form onSubmit={handleSubmit}>
				<div className='flex flex-col justify-center items-center sm:flex-row sm:justify-evenly'>
					<div className='mb-2'>
						<input
							className='px-3 py-1 rounded-full border border-gray-600'
							value={source} onChange={(e) => setSource(e.target.value)} required
							type="text" placeholder="Source" />
					</div>
					<div className='mb-2'>
						<input
							className='px-3 py-1 rounded-full border border-gray-600'
							value={translation} onChange={(e) => setTranslation(e.target.value)} required
							type="text" placeholder="Translation" />
					</div>
					<div className='mb-2'>
						<button className="bg-blue-500 hover:bg-blue-700 text-white py-1 px-3 rounded-full">
							Add
						</button>
					</div>
				</div>
			</form>


	);
}

export default Add;