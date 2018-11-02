import {Component, OnInit} from '@angular/core';
import {ArtistService} from "../artist.service";
import {YouTubeService} from "../you-tube.service";
import {VideoService} from "../services/video.service";
import {Router} from "@angular/router";
import {map} from "rxjs/internal/operators";

@Component({
    selector: 'app-add',
    templateUrl: './add.component.html',
    styleUrls: ['./add.component.css']
})
export class AddComponent implements OnInit {

    public youtubeId = '';

    public playerOptions;

    public input = '';

    public select2Options: Select2Options;

    public selectedArtist;

    public artists = [];

    public tempArtists = [];

    public artist;

    public title = '';

    public errorMsg;

    constructor(private _artistService: ArtistService, private _youTubeService: YouTubeService,
                private _videoService: VideoService, private router: Router) {
    }

    ngOnInit() {
        this._artistService.getArtists().
        pipe(map(arr => arr.map(el => el.name)),)
            .subscribe(
                data => {
                    this.tempArtists = data;
                },
                error => this.errorMsg = error);
    }

    onPaste(event: ClipboardEvent) {
        let clipboardData = event.clipboardData;
        this.input = clipboardData.getData('text');
        this.embedIfYoutubeId();
    }

    onKey(event: any) {
        this.input = event.target.value;
        this.embedIfYoutubeId();
    }

    private embedIfYoutubeId() {
        this.findYouTubeId();

        if (this.youtubeId) {

            this._youTubeService.getArtistAndTitle(this.youtubeId).subscribe(data => {
                    this.artist = data.artist;
                    this.title = data.title;
                    this.tempArtists.push(this.artist);
                    this.tempArtists.sort();
                    this.artists = this.tempArtists;
                    this.selectedArtist = this.artist;
                },
                error => this.errorMsg = error);

            this.select2Options = {
                placeholder: 'Select an artist or type a new one',
                tags: true
            };

            this.playerOptions = {
                videoId: this.youtubeId,
                height: 'auto',
                width: 'auto',
            };
        }
    }

    public changed(e: any): void {
        this.selectedArtist = e.value;
    }

    private findYouTubeId(): void {
            let regEx = "^(?:https?:)?//[^/]*(?:youtube(?:-nocookie)?\.com|youtu\.be).*[=/]([-\\w]{11})(?:\\?|=|&|$)";
            let matches = this.input.match(regEx);
            if(matches){
                this.youtubeId = matches[1];
            }else{
                this.youtubeId = '';
            }
    }

    addVideo(){
        this._videoService.addVideo(
            this.selectedArtist, this.title, this.youtubeId
        ).subscribe(data => {
                this.router.navigate([`/videos/${this.youtubeId}`]);
            },
            error => this.errorMsg = error);
    }
}
