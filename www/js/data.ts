import { Tools } from "./tools";

interface mapsJSON {
    id          : number;
    mapID       : string;
    mapKey      : string;
    difficulty  : string;
    poolID      : number;
    mapStyle    : string;
}
interface dataGrabbed {
    hash: string;
    difficulty: string;
    playerState: string;
    modifiers: {
        disappearingArrows  : boolean;
        ghostNotes          : boolean;
        fasterSong          : boolean;
        superFasterSong     : boolean;

        noBombs             : boolean;
        noWalls             : boolean;
        noArrows            : boolean;
        slowerSong          : boolean;

        noFail              : boolean;
        zenMode             : boolean;
        proMode             : boolean;

        modifierValue       : number;
    };
    performance: {
        score           : number;
        accuracy        : number;
        combo           : number;
        miss            : number;
        health          : number;
        notesPassed     : number;
        paused          : number;
    };
}

export class Data {

    ///////////////
    // @Instance //
    ///////////////
    private static _instance: Data;

    //////////////////////
    // @Class Variables //
    //////////////////////
    private _tools: Tools;

    public maps!: mapsJSON[];
    public mapsHash: Array<string> = [];
    public pool = 1;

    constructor() {
        this._tools = new Tools();

        this.getMaps(this.pool);
    }

    public async sendData(data: dataGrabbed) {
        if (this.mapsHash.includes(data.hash.toUpperCase())) {
            let dataJson = await this._tools.postMethod("/api/setScores", data);

            console.log(dataJson);
        }
    }
    private async getMaps(pool: number) {
        this.maps = await this._tools.getMethod("/api/getMaps/" + pool);

        for (let i = 0; i < this.maps.length; i++) {
            this.mapsHash.push(this.maps[i].mapID.toUpperCase());
        }
    }

    /////////////
    // Getters //
    /////////////
    public static get Instance(): Data {
        return this._instance || (this._instance = new this());
    }
}