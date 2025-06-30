const Translate = (translations: { [key: string]: string }, translationKey: string, defaultValue: string) => {
    // TODO: Collect not found keys and pass them to api
    if (!translationKey) {
        console.log('Translation key does not exists for [' + defaultValue + ']');
    }

    return translations[translationKey] ?? 'translation-not-found';
}

export default Translate;
