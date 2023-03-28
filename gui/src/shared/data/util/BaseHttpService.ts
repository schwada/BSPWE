export class HttpService {

    public baseUrl: string = import.meta.env.VITE_API_URL;
    public headers: Headers = new Headers({
        'Content-Type': 'application/json',  
        'Accept': 'application/json',
        "Access-Control-Allow-Origin": "*",
    });
    
    public defaults: RequestInit = {
        headers: this.headers
    };

    public async fetch(input: RequestInfo, init?: RequestInit): Promise<any> {
        const response = await fetch(this.baseUrl + input, {...this.defaults, ...init});
        try{ // if body empty
            return await response.json();
        } catch { }
    }

    public addDefault(defaults: RequestInit) {
        this.defaults = {...this.defaults, ...defaults };
    }

    public getDefaults() {
        return this.defaults;
    }

}

export default new HttpService();