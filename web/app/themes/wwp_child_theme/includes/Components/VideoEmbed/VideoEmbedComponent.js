import {PewComponent} from "../../../assets/raw/js/components/pew-component";

export class VideoEmbedComponent extends PewComponent {
    init() {
        this.registerVideoToggle();
    }

    registerVideoToggle() {

        const $videoTrigger = this.element.find('.video-trigger');

        if ($videoTrigger.length) {

            $videoTrigger.on('click', (e) => {
                e.preventDefault();
                //console.log('click');
                if (this.element.hasClass('video-active')) {
                    this.element.removeClass('video-active');
                    if (window.wonderwp.ytplayer) {
                        window.wonderwp.ytplayer.pauseVideo();
                    }
                } else {
                    this.element.addClass('video-active');
                    if (window.wonderwp.ytplayer) {
                        window.wonderwp.ytplayer.playVideo();
                    }
                }
            });

            $(document).off("onYouTubeIframeAPIReadyCustom");
            $(document).on("onYouTubeIframeAPIReadyCustom", () => {
                //console.log('onYouTubeIframeAPIReadyCustom');
                //console.log(this.element.find('.video-embed-player').data('video'));
                const $player              = this.element.find('.video-player');
                window.wonderwp.ytplayer = new YT.Player($player[0], {
                    videoId: $player.data('video')
                });
            });

            if (!document.getElementById('yt-iframe-lib')) {
                //console.log('yt-iframe-lib');
                let tag            = document.createElement('script');
                tag.id             = 'yt-iframe-lib';
                tag.src            = 'https://www.youtube.com/iframe_api';
                let firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            } else {
                onYouTubeIframeAPIReady();
            }
        }
    }
}

window.onYouTubeIframeAPIReady = function () {
    console.log('onYouTubeIframeAPIReady');
    $(document).trigger("onYouTubeIframeAPIReadyCustom");
};

window.pew.addRegistryEntry({key: 'video-wrapper', domSelector: '[data-video-embed-component]', classDef: VideoEmbedComponent});
