import { Tools } from "./tools";
import { Data }  from "./data";

export interface BeatSaverSongJSON {
    error       : string; // Provided by BeatSaver API

    id          : string; // BSR Key
    name        : string; // Song name include "Song name" and "Song sub name"

    /// WARNING: Qualified and Ranked can be true at the same time ! If it appends, use qualified, not ranked
    qualified   : boolean; // Song is qualified ?
    ranked      : boolean; // Song is ranked ?

    metadata: {
        songName        : string; // Song name
        songSubName     : string; // Song sub name
        levelAuthorName : string; // Mapper name
        songAuthorName  : string; // Author name

        bpm             : number; // BPM of the song
        duration        : number; // Duration length of the song
    };

    versions: [{
        coverURL : string; // Song cover
        diffs: [{
            difficulty: string;
            notes: number;
        }];
    }];
}

export class BeatSaberDataManager {

    ///////////////
    // @Instance //
    ///////////////
    private static _instance: BeatSaberDataManager;

    //////////////////////
    // @Class Variables //
    //////////////////////
    private _tools: Tools;
    private _data:  Data;

    //////////////////////
    // Public Variables //
    //////////////////////
    public mapsSong             = {
        title       : "Title",
        subtitle    : "SubTitle",
        author      : "Beat Saber",
        mapper      : "Beat Games",
        cover       : "",
        songLength  : 0,
        songBeatSaverLength: 0,
        songSpeed   : 1,
        bpm         : 0,
        totalNotes  : 0,

        hashMap     : "",
        bsrKey      : "",

        difficulty  : "",
        ranked      : false,
        qualified   : false
    };
    public mapsModifier         = {
        disappearingArrows  : false,
        ghostNotes          : false,
        fasterSong          : false,
        superFasterSong     : false,

        noBombs             : false,
        noWalls             : false,
        noArrows            : false,
        slowerSong          : false,

        noFail              : false,
        zenMode             : false,
        proMode             : false,

        modifierValue       : 1
    };
    public mapsPerformance      = {
        actualSongTime: 0
    };
    public player               = {
        playerID        : "",
        name            : "Player",
        avatar          : "",
        country         : "",
        performancePoint: "",
        worldRank       : 0,
        countryRank     : 0
    };
    public playerPerformance    = {
        score           : 0,
        accuracy        : 1,
        combo           : 0,
        miss            : 0,
        health          : 0.5,
        notesPassed     : 0,
        paused          : 0
    };

    public gameState    : "None" | "Connected" | "Menu" | "Playing" | "Paused"  = "None";
    public playerState  : "None" | "Failed" | "Finish" | "Quit"                 = "None";

    constructor() {
        this._tools = new Tools();
        this._data  = Data.Instance;
    }

    public async songDetails(songHash: string) {
        let data = await this.getSongDetails(songHash);

        if (data.error !== undefined) {
            return;
        }

        this.mapsSong.songBeatSaverLength = data.metadata.duration * 1000;

        let lastVersion = data.versions.length - 1;

        if ("diffs" in data.versions[lastVersion]) {
            for (let i = 0; i < data.versions[lastVersion].diffs.length; i++) {
                if ("difficulty" in data.versions[lastVersion].diffs[i]) {
                    if (data.versions[lastVersion].diffs[i].difficulty === this.mapsSong.difficulty) {
                        this.mapsSong.totalNotes = data.versions[lastVersion].diffs[i].notes;
                        break;
                    }
                }
            }
        }
    }
    public pushData() {
        if (this.playerState === "Finish") {
            let data = {
                hash: this.mapsSong.hashMap,
                difficulty: this.mapsSong.difficulty,
                totalNote: this.mapsSong.totalNotes,
                songLength: ((this.mapsSong.songLength - this.mapsSong.songBeatSaverLength) < 1000 && (this.mapsSong.songLength - this.mapsSong.songBeatSaverLength) > -1000),
                playerState: this.playerState,
                modifiers: this.mapsModifier,
                performance: this.playerPerformance
            };

            this._data.sendData(data);
        }
    }
    private async getSongDetails(songHash: string): Promise<BeatSaverSongJSON> {
        return await this._tools.getMethod("https://api.beatsaver.com/maps/hash/" + songHash);
    }

    /////////////
    // Getters //
    /////////////
    public static get Instance(): BeatSaberDataManager {
        return this._instance || (this._instance = new this());
    }
}