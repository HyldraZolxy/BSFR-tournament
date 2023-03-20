import { WebSocketManager }     from './websocket-manager';
import { HTTPSiraStatus }     from "./HTTPSiraStatus";

export class Plugins {

    ///////////////
    // @Instance //
    ///////////////
    private static _instance: Plugins;

    //////////////////////
    // @Class Variables //
    //////////////////////
    private _websocketManager   : WebSocketManager;
    private _httpSiraStatus     : HTTPSiraStatus;

    ///////////////////////
    // Private Variables //
    ///////////////////////
    private websocketVersion = 0;

    //////////////////////
    // Public Variables //
    //////////////////////
    public websocketStatus  : "CONNECTED" | "DISCONNECTED" = "DISCONNECTED";

    constructor() {
        this._websocketManager  = new WebSocketManager();
        this._httpSiraStatus    = new HTTPSiraStatus();
    }

    ////////////////////
    // Public Methods //
    ////////////////////
    public async connection(): Promise<void> {
        this.websocketVersion++;

        this._websocketManager.add("HttpSiraStatus" + this.websocketVersion, "ws://127.0.0.1:6557/socket",
            (data) => { this._httpSiraStatus.dataParser(data); },
            () => {
                console.log("socket initialized on HttpSiraStatus!");
                this.websocketStatus    = "CONNECTED";
            },
            () => {
                if (this.websocketStatus === "CONNECTED") this.websocketStatus = "DISCONNECTED";
                else this.websocketStatus = "DISCONNECTED";
            },
            () => { console.log("init of HttpSiraStatus socket failed!"); }
        );
    }

    public removeConnection(): Promise<unknown> {
        return new Promise(resolve => {
            this._websocketManager.remove("BSPlus" + this.websocketVersion);

            setTimeout(() => resolve(""), 250);
        });
    }

    /////////////
    // Getters //
    /////////////
    public static get Instance(): Plugins {
        return this._instance || (this._instance = new this());
    }
}