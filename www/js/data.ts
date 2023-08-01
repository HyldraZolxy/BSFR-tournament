import { Tools } from "./tools";
import { mapsSong, playerPerformance } from "./BeatSaberDataManager";

interface mapsJSON {
    id          : number;
    hash        : string;
    difficulty  : string;
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
interface dataJSON {
    error : string;
    warning: string;
    success: string;
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
    public tournamentID = 4;
    public poolID = 7;
    public mapID = 0;

    private infoGrabberInteraction = $(".infoGrabberInteraction");

    constructor() {
        this._tools = new Tools();

        this.getMaps(this.tournamentID, this.poolID);
    }

    public async sendData(data: dataGrabbed, mapInformation: mapsSong, playerPerformance: playerPerformance) {

        this.infoGrabberInteraction.removeClass("text-glow-cyan text-green text-red text-orange");

        if (this.mapsHash.includes(data.hash.toUpperCase())) {
            this.infoGrabberInteraction.html("<i class=\"fa-solid fa-magnifying-glass-chart\"></i> Analysing: " + mapInformation.author + " - " + mapInformation.title);
            this.infoGrabberInteraction.addClass("text-glow-cyan");

            this.createMapsCard(mapInformation);

            await this.getMapID(data.hash);

            const formData = {
                tournamentID: this.tournamentID,
                poolID: this.poolID,
                mapID: this.mapID,
                data: data
            };

            let dataJson = await this._tools.postMethod("/api/setScore", formData);

            console.log(dataJson);

            setTimeout(() => {
                if (dataJson.error !== undefined) {
                    this.infoGrabberInteraction.removeClass("text-glow-cyan text-green text-red text-orange");
                    this.infoGrabberInteraction.html("<i class=\"fa-regular fa-circle-xmark\"></i> Fail: " + mapInformation.author + " - " + mapInformation.title);
                    this.infoGrabberInteraction.addClass("text-red");

                    $(".pass .grabberSong").last().find(".infoAPI")
                        .html("<i class=\"fa-regular fa-circle-xmark\"></i> Fail: " + dataJson.error)
                        .removeClass("text-glow-cyan text-green text-red text-orange")
                        .addClass("text-red");
                }
                if (dataJson.warning !== undefined) {
                    this.infoGrabberInteraction.removeClass("text-glow-cyan text-green text-red text-orange");
                    this.infoGrabberInteraction.html("<i class=\"fa-solid fa-circle-exclamation\"></i> Warning: " + mapInformation.author + " - " + mapInformation.title);
                    this.infoGrabberInteraction.addClass("text-orange");

                    $(".pass .grabberSong").last().find(".infoAPI")
                        .html("<i class=\"fa-solid fa-circle-exclamation\"></i> Warning: " + dataJson.warning)
                        .removeClass("text-glow-cyan text-green text-red text-orange")
                        .addClass("text-orange");
                }
                if (dataJson.success !== undefined) {
                    this.infoGrabberInteraction.removeClass("text-glow-cyan text-green text-red text-orange");
                    this.infoGrabberInteraction.html("<i class=\"fa-regular fa-circle-check\"></i> Success: " + mapInformation.author + " - " + mapInformation.title);
                    this.infoGrabberInteraction.addClass("text-green");

                    $(".pass .grabberSong").last().find(".infoAPI")
                        .html("<i class=\"fa-regular fa-circle-check\"></i> Success: " + dataJson.success)
                        .removeClass("text-glow-cyan text-green text-red text-orange")
                        .addClass("text-green");
                }
            }, 1000);
        }
    }
    private async getMaps(tournament: number, pool: number) {
        this.maps = await this._tools.getMethod("/api/getMaps/" + tournament + "/" + pool);

        for (let i = 0; i < this.maps.length; i++) {
            this.mapsHash.push(this.maps[i].hash.toUpperCase());
        }
    }
    private async getMapID(hash: string) {
        for (let i = 0; i < this.maps.length; i++) {
            if (this.maps[i].hash.toUpperCase() === hash.toUpperCase()) {
                this.mapID = this.maps[i].id;
            }
        }
    }

    private createMapsCard(mapInformation: mapsSong) {
        const infoGrabberMaps = $(".pass");

        infoGrabberMaps.append("<div class=\"wrapper grabberSong\">" +
                "                <div class=\"collapsible-background\">" +
                "                    <img src=\""+ mapInformation.cover + "\"  alt=\"cover song\"/>" +
                "                </div>" +
                "                <div class=\"collapsible-content\">" +
                "                    <p class=\"collapsible-content-title\">" + mapInformation.author + "</p>" +
                "                    <div class=\"collapsible-content-information flex flex-column flex-spaced\">" +
                "                        <p class=\"flex-grow2 text-left text-cut\">" + mapInformation.title + "</p>" +
                "                        <p class=\"infoAPI text-left text-cut\"></p>" +
                "                    </div>" +
                "                </div>" +
                "            </div>");
    }

    /////////////
    // Getters //
    /////////////
    public static get Instance(): Data {
        return this._instance || (this._instance = new this());
    }
}