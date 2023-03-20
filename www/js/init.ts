import { Data }    from "./data";
import { Plugins } from "./plugins";

declare global {
    interface Window {
        timeStamp: number;
    }
}

class Init {

    //////////////////////
    // @Class Variables //
    //////////////////////
    private _data   : Data;
    private _plugins: Plugins;

    constructor() {
        console.info("Grabber !",);
        console.info("For Team France");
        console.info("Plugins used: HTTPSiraStatus");
        console.info("Games used: Beat Saber");

        this._data    = Data.Instance;
        this._plugins = Plugins.Instance;

        (async () => {
            await this.appInit();

            // Try to catch the performance of the internet connection
            window.timeStamp = window.timeStamp + performance.now();
        })();
    }

    ////////////////////
    // Public Methods //
    ////////////////////
    public async appInit() {
        await this._plugins.connection();
    }
}

new Init();