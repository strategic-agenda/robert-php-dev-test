const BACKEND_URL = 'http://api.interview.localhost';

const HttpGetRequestService = async (url: string) => {
    const response = await fetch(BACKEND_URL + url, {
        method: 'GET',
    });

    return response.json();
}

export default HttpGetRequestService;
