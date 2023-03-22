import { Tools } from "./tools";
import { mapsSong, playerPerformance } from "./BeatSaberDataManager";

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
interface dataJSON {
    error : string;
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
    public pool = 1;

    constructor() {
        this._tools = new Tools();

        this.getMaps(this.pool);
    }

    public async sendData(data: dataGrabbed, mapInformation: mapsSong, playerPerformance: playerPerformance) {
        if (this.mapsHash.includes(data.hash.toUpperCase())) {
            this.createMapsCard(mapInformation, playerPerformance);

            let dataJson = await this._tools.postMethod("/api/setScores", data);

            console.log(dataJson);

            dataJson = JSON.parse(dataJson);

            if (dataJson.error !== undefined) {
                let elementMapCardLast = $(".mapContainer").last();
                let elementMapStatusUpload = elementMapCardLast.find(".mapStatusUpload");

                elementMapStatusUpload.removeClass("mapStatusUploadProgress mapStatusUploadNotOk mapStatusUploadOk");
                elementMapStatusUpload.addClass("mapStatusUploadNotOk");
                elementMapStatusUpload.text(dataJson.error);
            }

            if (dataJson.success !== undefined) {
                let elementMapCardLast = $(".mapContainer").last();
                let elementMapStatusUpload = elementMapCardLast.find(".mapStatusUpload");

                elementMapStatusUpload.removeClass("mapStatusUploadProgress mapStatusUploadNotOk mapStatusUploadOk");
                elementMapStatusUpload.addClass("mapStatusUploadOk");
                elementMapStatusUpload.text(dataJson.success);
            }
        }
    }
    private async getMaps(pool: number) {
        this.maps = await this._tools.getMethod("/api/getMaps/" + pool);

        for (let i = 0; i < this.maps.length; i++) {
            this.mapsHash.push(this.maps[i].mapID.toUpperCase());
        }
    }

    private createMapsCard(mapInformation: mapsSong, playerPerformance: playerPerformance) {
        let elementStatus = $("#mainContent");

        elementStatus.append(   '<div class="mapContainer">' +
                                    '<img class="mapCover" src="' + mapInformation.cover + '" alt="cover map" />' +
                                    '<p class="mapTitle">' + mapInformation.title + '</p>' +
                                    '<div class="mapPerformance">' +
                                        '<p class="mapPerformanceScore">' + playerPerformance.score.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '</p>' +
                                        '<p class="mapPerformanceAccuracy">' + Number(Math.floor(playerPerformance.accuracy * 100).toFixed(2)) + '%</p>' +
                                        '<p class="mapPerformanceMiss">' + playerPerformance.miss + '</p>' +
                                        '<p class="mapPerformanceNotes">' + playerPerformance.notesPassed + '/' + mapInformation.totalNotes + '</p>' +
                                    '</div>' +
                                    '<p class="mapStatusUpload mapStatusUploadProgress">En cours de vérification</p>' +
                                '</div>');
    }

    /////////////
    // Getters //
    /////////////
    public static get Instance(): Data {
        return this._instance || (this._instance = new this());
    }
}