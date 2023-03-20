export class Tools {

    ////////////////////
    // Public Methods //
    ////////////////////
    public async getMethod(URI: string, options?: any): Promise<any> {
        return await fetch(URI, {
            method: "GET",
            headers: options
        })
        .then(response => response.json());
    }
    public async postMethod(URI: string, parameters: any, options?: any): Promise<any> {
        return await fetch(URI, {
            method: "POST",
            headers: options,
            body: JSON.stringify(parameters)
        })
        .then(response => response.text());
    }
}