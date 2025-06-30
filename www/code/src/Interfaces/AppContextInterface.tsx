export default interface AppContextInterface {
    location: string;
    translations: {
        system: {
            [key: string]: string
        },
        customer: {
            [key: string]: string
        }
    }
}
