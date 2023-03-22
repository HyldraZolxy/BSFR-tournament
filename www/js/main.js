(()=>{"use strict";class t{async getMethod(t,s){return await fetch(t,{method:"GET",headers:s}).then((t=>t.json()))}async postMethod(t,s,e){return await fetch(t,{method:"POST",headers:e,body:JSON.stringify(s)}).then((t=>t.text()))}}class s{constructor(){this.mapsHash=[],this.pool=1,this._tools=new t,this.getMaps(this.pool)}async sendData(t,s,e){if(this.mapsHash.includes(t.hash.toUpperCase())){this.createMapsCard(s,e);let a=await this._tools.postMethod("/api/setScores",t);if(console.log(a),a=JSON.parse(a),void 0!==a.error){let t=$(".mapContainer").last().find(".mapStatusUpload");t.removeClass("mapStatusUploadProgress mapStatusUploadNotOk mapStatusUploadOk"),t.addClass("mapStatusUploadNotOk"),t.text(a.error)}if(void 0!==a.success){let t=$(".mapContainer").last().find(".mapStatusUpload");t.removeClass("mapStatusUploadProgress mapStatusUploadNotOk mapStatusUploadOk"),t.addClass("mapStatusUploadOk"),t.text(a.success)}}}async getMaps(t){this.maps=await this._tools.getMethod("/api/getMaps/"+t);for(let t=0;t<this.maps.length;t++)this.mapsHash.push(this.maps[t].mapID.toUpperCase())}createMapsCard(t,s){$("#mainContent").append('<div class="mapContainer"><img class="mapCover" src="'+t.cover+'" alt="cover map" /><p class="mapTitle">'+t.title+'</p><div class="mapPerformance"><p class="mapPerformanceScore">'+s.score.toString().replace(/\B(?=(\d{3})+(?!\d))/g,",")+'</p><p class="mapPerformanceAccuracy">'+Number(Math.floor(100*s.accuracy).toFixed(2))+'%</p><p class="mapPerformanceMiss">'+s.miss+'</p><p class="mapPerformanceNotes">'+s.notesPassed+"/"+t.totalNotes+'</p></div><p class="mapStatusUpload mapStatusUploadProgress">En cours de vérification</p></div>')}static get Instance(){return this._instance||(this._instance=new this)}}class e{constructor(){this.sockets=new Map,this.reconnectInterval=new Map,this.reconnecting=new Map}anyOpen(){for(const t of this.sockets.values())if(t.readyState===WebSocket.OPEN)return!0;return!1}add(t,s,e,a,o,n,i=!1,r=5e3){const c=new WebSocket(s);c.onopen=t=>a(t),c.onmessage=t=>e(t.data),c.onclose=c=>{o(c),this.sockets.get(t)&&this.reconnect(t,s,e,a,o,n,i,r)},c.onerror=t=>n(t),this.sockets.set(t,c),this.reconnectInterval.set(t,r),this.reconnecting.set(t,!1)}reconnect(t,s,e,a,o,n,i,r){this.sockets.get(t)&&(!i&&!this.anyOpen()||i?this.reconnecting.get(t)||(this.reconnecting.set(t,!0),console.log(t+" WebSocket reconnecting in "+this.reconnectInterval.get(t)+"ms"),setTimeout((()=>{this.sockets.get(t)&&this.add(t,s,e,a,o,n,i,r),this.reconnecting.set(t,!1)}),r)):this.reconnecting.get(t)||setTimeout((()=>{this.reconnecting.set(t,!1),this.sockets.get(t)&&this.reconnect(t,s,e,a,o,n,i,r)}),r))}remove(t){this.sockets.get(t)?.close(),this.sockets.delete(t),this.reconnectInterval.delete(t),this.reconnecting.delete(t)}get(t){return this.sockets.get(t)}send(t,s){const e=this.sockets.get(t);e&&e.send(s)}}class a{constructor(){this.mapsSong={title:"Title",subtitle:"SubTitle",author:"Beat Saber",mapper:"Beat Games",cover:"",songLength:0,songBeatSaverLength:0,songSpeed:1,bpm:0,totalNotes:0,hashMap:"",bsrKey:"",difficulty:"",ranked:!1,qualified:!1},this.mapsModifier={disappearingArrows:!1,ghostNotes:!1,fasterSong:!1,superFasterSong:!1,noBombs:!1,noWalls:!1,noArrows:!1,slowerSong:!1,noFail:!1,zenMode:!1,proMode:!1,modifierValue:1},this.mapsPerformance={actualSongTime:0},this.player={playerID:"",name:"Player",avatar:"",country:"",performancePoint:"",worldRank:0,countryRank:0},this.playerPerformance={score:0,accuracy:1,combo:0,miss:0,health:.5,notesPassed:0,paused:0},this.gameState="None",this.playerState="None",this._tools=new t,this._data=s.Instance}async songDetails(t){let s=await this.getSongDetails(t);if(void 0!==s.error)return;this.mapsSong.songBeatSaverLength=1e3*s.metadata.duration;let e=s.versions.length-1;if("diffs"in s.versions[e])for(let t=0;t<s.versions[e].diffs.length;t++)if("difficulty"in s.versions[e].diffs[t]&&s.versions[e].diffs[t].difficulty===this.mapsSong.difficulty){this.mapsSong.totalNotes=s.versions[e].diffs[t].notes;break}}pushData(){if("Finish"===this.playerState){let t={hash:this.mapsSong.hashMap,difficulty:this.mapsSong.difficulty,totalNote:this.mapsSong.totalNotes,songLength:this.mapsSong.songLength-this.mapsSong.songBeatSaverLength<1e3&&this.mapsSong.songLength-this.mapsSong.songBeatSaverLength>-1e3,playerState:this.playerState,modifiers:this.mapsModifier,performance:this.playerPerformance};this._data.sendData(t,this.mapsSong,this.playerPerformance)}}async getSongDetails(t){return await this._tools.getMethod("https://api.beatsaver.com/maps/hash/"+t)}static get Instance(){return this._instance||(this._instance=new this)}}class o extends a{eHandshake(t){console.log("Beat Saber "+t.status.game.gameVersion+" | HTTPSiraStatus Version "+t.status.game.pluginVersion),console.log("\n\n"),this.gameState="Connected"}eHandler(t){switch(t.event){case"hello":this.eHandshake(t),this.gameState="Menu",this.playerState="None",null!==t.status.beatmap&&(this.mapInfoParser(t),null!==t.status.beatmap.paused?(null!==t.status.beatmap.start&&(this.mapsPerformance.actualSongTime=t.status.beatmap.paused-t.status.beatmap.start),this.gameState="Paused"):(null!==t.status.beatmap.start&&(this.mapsPerformance.actualSongTime=t.time-t.status.beatmap.start),this.gameState="Playing"),this.scoreParser(t));break;case"songStart":console.log(t.status.game.mode),this.gameState="Playing",this.playerState="None",this.mapInfoParser(t),console.log("Playing: "+this.playerState+"\n\n");break;case"pause":this.gameState="Paused",this.playerPerformance.paused++;break;case"resume":this.gameState="Playing";break;case"finished":this.playerPerformance.notesPassed>=this.mapsSong.totalNotes?this.playerState="Finish":this.playerState="Quit",this.pushData();break;case"menu":this.gameState="Menu",console.log("Menu: "+this.playerState),console.log("Total notes: "+this.mapsSong.totalNotes),console.log("Note count: "+this.playerPerformance.notesPassed);break;case"noteMissed":case"scoreChanged":this.scoreParser(t)}}mapInfoParser(t){this.mapsSong.title=t.status.beatmap?.songName,this.mapsSong.subtitle=t.status.beatmap?.songSubName,this.mapsSong.author=t.status.beatmap?.songAuthorName,this.mapsSong.mapper=""!==t.status.beatmap?.levelAuthorName?"["+t.status.beatmap?.levelAuthorName.trim()+"]":"[Beat Games]",this.mapsSong.cover=null!==t.status.beatmap?.songCover?"data:image/png;base64,"+t.status.beatmap?.songCover:"./pictures/default/notFound.jpg",this.mapsSong.songLength=t.status.beatmap?.length,this.mapsSong.songBeatSaverLength=0,this.mapsSong.songSpeed=1,this.mapsSong.bpm=t.status.beatmap?.songBPM,this.mapsSong.totalNotes=0,this.mapsSong.hashMap=t.status.beatmap?.songHash,this.mapsSong.bsrKey="",this.mapsSong.difficulty=t.status.beatmap?.difficultyEnum,this.mapsSong.ranked=!1,this.mapsSong.qualified=!1,this.mapsModifier.disappearingArrows=t.status.mod.disappearingArrows,this.mapsModifier.ghostNotes=t.status.mod.ghostNotes,this.mapsModifier.fasterSong="Faster"===t.status.mod.songSpeed,this.mapsModifier.superFasterSong="SuperFast"===t.status.mod.songSpeed,this.mapsModifier.noBombs=t.status.mod.noBombs,this.mapsModifier.noWalls=!1===t.status.mod.obstacles,this.mapsModifier.noArrows=t.status.mod.noArrows,this.mapsModifier.slowerSong="Slower"===t.status.mod.songSpeed,this.mapsModifier.noFail=t.status.mod.noFail,this.mapsModifier.zenMode=t.status.mod.zenMode,this.mapsModifier.proMode=t.status.mod.proMode,this.mapsModifier.modifierValue=t.status.mod.multiplier,this.mapsPerformance.actualSongTime=null!==t.status.beatmap?.start&&void 0!==t.status.beatmap?.start?t.time-t.status.beatmap?.start:0,this.playerPerformance.score=0,this.playerPerformance.accuracy=1,this.playerPerformance.combo=0,this.playerPerformance.miss=0,this.playerPerformance.health=.5,this.playerPerformance.notesPassed=0,this.playerPerformance.paused=0,console.log("Hash maps: "+t.status.beatmap?.songHash),this.songDetails(t.status.beatmap?.songHash)}scoreParser(t){void 0!==t.status.performance?.currentSongTime&&(this.mapsPerformance.actualSongTime=1e3*t.status.performance?.currentSongTime),this.playerPerformance.score=t.status.performance?.score,this.playerPerformance.accuracy=t.status.performance?.relativeScore,this.playerPerformance.combo=t.status.performance?.combo,this.playerPerformance.miss=t.status.performance?.missedNotes,this.playerPerformance.health=1,this.playerPerformance.notesPassed=t.status.performance?.passedNotes,t.status.performance?.softFailed&&(this.playerState="Failed")}dataParser(t){let s=JSON.parse(t);this.eHandler(s)}}class n{constructor(){this.websocketVersion=0,this.websocketStatus="DISCONNECTED",this._websocketManager=new e,this._httpSiraStatus=new o}async connection(){this.websocketVersion++,this._websocketManager.add("HttpSiraStatus"+this.websocketVersion,"ws://127.0.0.1:6557/socket",(t=>{this._httpSiraStatus.dataParser(t)}),(()=>{console.log("socket initialized on HttpSiraStatus!"),this.websocketStatus="CONNECTED",$(".pluginStatusValue").text("Connected"),$(".pluginStatusValue").removeClass("pluginStatusDisconnected pluginStatusConnected").addClass("pluginStatusConnected")}),(()=>{this.websocketStatus,this.websocketStatus="DISCONNECTED",$(".pluginStatusValue").text("Not connected"),$(".pluginStatusValue").removeClass("pluginStatusConnected pluginStatusDisconnected").addClass("pluginStatusDisconnected")}),(()=>{console.log("init of HttpSiraStatus socket failed!")}))}removeConnection(){return new Promise((t=>{this._websocketManager.remove("BSPlus"+this.websocketVersion),setTimeout((()=>t("")),250)}))}static get Instance(){return this._instance||(this._instance=new this)}}new class{constructor(){console.info("Grabber !"),console.info("For Team France"),console.info("Plugins used: HTTPSiraStatus"),console.info("Games used: Beat Saber"),this._data=s.Instance,this._plugins=n.Instance,(async()=>{await this.appInit(),window.timeStamp=window.timeStamp+performance.now()})()}async appInit(){await this._plugins.connection()}}})();