import { useEffect, useState } from "react";
import { useSearchParams } from "react-router-dom";

const getAvailable = async (searched: string) => await new Promise(resolve => setTimeout(() => {
    resolve([
        { domain: searched + ".cz", available: true },
        { domain: searched + ".com", available: true },
        { domain: searched + ".sk", available: false },
        { domain: searched + ".net", available: true },
        { domain: searched + ".org", available: true }
    ]);
}, 1000));


const extractDomain = (str: any) => {
    let validChars = "";
    for (let i = 0; i < str.length; i++) {
        const char = str.charAt(i);
        if (/^[a-zA-Z0-9\-_]+$/.test(char)) {
            validChars += char;
        } else {
            break;
        }
    }
    return validChars;
}


export default function Domain() {

    const [searchParams, setSearchParams] = useSearchParams();
    const [search, setSearch] = useState<string>("");
    const [loading, setLoading] = useState<boolean>(false);
    const [available, setAvailable] = useState<any>([]);

    useEffect(() => {
        if(searchParams.has("search")) {
            const extracted = extractDomain(searchParams.get("search"));
            setSearchParams({...searchParams, search: extracted })
            setSearch(extracted);
            searchAvailable(extracted);
        }
    }, []);

    const searchAvailable = async (searched: string) => {
        if(search == "") return;
        const extracted = extractDomain(searched);
        setLoading(true);
        setSearch(extracted)
        setAvailable(await getAvailable(extracted));
        setLoading(false);
    };

    const register = async (domain: string) => {
        setSearchParams({...Object.fromEntries(searchParams), domain: domain })
    }

    return (
        <div className="flex-col flex items-center">
            <div className="w-5/6 flex flex-col items-center p-5">
                {(searchParams.has("plan") && !searchParams.has("domain")) && (<h1 className="font-['customsans'] text-gray-700 text-6xl whitespace-pre-wrap">Máte vybraný hosting. Teď už stačí jen doména!</h1>)} 
                {(!searchParams.has("plan") && !searchParams.has("domain")) && (<h1 className="font-['customsans'] text-gray-700 text-6xl whitespace-pre-wrap">Dobrá doména vás vás odliší od ostatních!</h1>)}
                {(!searchParams.has("plan") && searchParams.has("domain")) && (<h1 className="font-['customsans'] text-gray-700 text-6xl whitespace-pre-wrap">Už chybí jen hosting!</h1>)}

                {searchParams.has("domain") ? (
                    <div>mate vybranou domenu</div>
                ):(<>
                    <p className="whitespace-pre text-lg mt-2 text-center text-gray-600">Doména je vaše online adresa. Je to místo, kde vás lidé mohou najít na internetu a kde si můžete vybudovat svou digitální přítomnost.<br/>Vyberte si svůj dokonalý název domény ještě dnes!</p>
                    <div className="mt-10 flex items-center w-full">
                        <input id="domain" type="text" autoFocus placeholder="Název vaší nové domény"
                        className="peer h-12 px-4 w-full rounded-md border-[3px] border-gray-300 text-gray-900
                        focus:outline-none focus:border-sky-500" value={search} onChange={e => setSearch(e.target.value)}/>
                        <button onClick={() => searchAvailable(search)} className="btn btn-blue h-12 w-full flex-1 px-6 ml-2 font-semibold">Ověřit dostupnost</button>
                    </div>
                </>)}
            </div>
                
            <div className="flex flex-col w-5/6 mx-10 mt-3">
                {loading && (
                    <div className="w-full flex justify-center animate-pulse font-['customsans'] text-blue-800">
                        Ověřuji dostupnost domény...
                    </div>
                )}
                {!loading && available.length > 0 && available.map((offer: any, index: number) => (
                    <div key={index} className="border-[3px] flex justify-between mx-5 rounded-md items-center p-1 mt-2">
                        <div className="pl-5 flex">
                        <p className="text-gray-700 font-medium">{offer.domain}</p>
                            <div className={ `${(offer.available ? "bg-green-300" : "bg-red-300")} rounded-xl px-4 ml-4`}>
                                { offer.available ? "volná" : "obsazeno" }
                            </div>
                        </div>
                        <div className="flex items-center">
                            {offer.available && (
                                <div className="text-gray-600 text-lg font-medium mr-5">0 kč</div>
                            )}
                            <button onClick={() => register(offer.domain)} disabled={offer.available ? false : true} className="btn btn-blue">
                                Zaregistrovat
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}