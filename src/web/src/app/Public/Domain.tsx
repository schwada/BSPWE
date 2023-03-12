export default function Domain() {

    return (
        <div className="flex-col flex items-center">
                <div className="w-5/6 flex flex-col items-center p-5">
                    <h1 className="font-['customsans'] text-gray-700 text-6xl whitespace-pre-wrap">Dobrá doména vás vás odliší od ostatních!</h1>
                    <p className="whitespace-pre text-lg mt-2 text-center text-gray-600">Doména je vaše online adresa. Je to místo, kde vás lidé mohou najít na internetu a kde si můžete vybudovat svou digitální přítomnost.<br/>Vyberte si svůj dokonalý název domény ještě dnes!</p>
                    <div className="mt-10 flex items-center w-full">
                        <input id="domain" type="text" autoFocus placeholder="Název vaší nové domény"
                        className="peer h-12 px-4 w-full rounded-md border-[3px] border-gray-300 text-gray-900
                        focus:outline-none focus:border-sky-500" />
                        <button className="btn btn-blue h-12 w-full flex-1 px-6 ml-2 font-semibold">Ověřit dostupnost</button>
                    </div>
                </div>
                
                <div className="flex flex-col w-5/6 mx-10 mt-3">
                    <div className="border-[3px] flex justify-between mx-5 rounded-md items-center p-1 mt-2">
                        <div className="pl-5 flex">
                        <p className="text-gray-700 font-medium">pipipoopoo.com</p>
                            <div className="bg-green-300 rounded-xl px-4 ml-4">volná</div>
                        </div>
                        <div className="flex items-center">
                            <div className="text-gray-600 text-lg font-medium mr-5">0 kč</div>
                            <button className="btn btn-blue">Zaregistrovat</button>
                        </div>
                    </div>

                    <div className="border-[3px] flex justify-between mx-5 rounded-md items-center p-1 mt-2">
                        <div className="pl-5 flex">
                            <p className="text-gray-700 font-medium">pipipoopoo.com</p>
                            <div className="bg-green-300 rounded-xl px-4 ml-4">volná</div>
                        </div>
                        <div className="flex items-center">
                            <div className="text-gray-600 text-lg font-medium mr-5">0 kč</div>
                            <button className="btn btn-blue">Zaregistrovat</button>
                        </div>
                    </div>
                </div>
        </div>
    );
}