import StatusEnum from '../store/StatusEnum';
export default {

    methods: {
        ticksToTimeString: function(ticks) {
            if (isNaN(ticks)) {
                return '00:00';
            }
            const minute = parseInt(ticks / 60) % 60;
            const seconds = (ticks % 60).toFixed();
            return minute + ":" + (seconds < 10 ? "0" + seconds : seconds);
        },
        toggleSong(song) {
            this.$emit("toggleSong", song);
        },
        openAlbum(album) {
            this.$emit('openAlbum', album);
        },
        setQueueAndPlay(songs) {
            this.$emit("setQueueAndPlay", songs);  
        },
        isActiveSong(song) {
            return this.activeSong.id === song.id;
        },      
    },
    computed: {
        loading() {
            return this.loadingState === StatusEnum.LOADING;
        }
    }
};    