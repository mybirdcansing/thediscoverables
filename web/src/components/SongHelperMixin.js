export default {
    methods: {
        durationToString: function(duration) {
            if (isNaN(duration)) {
                return '00:00';
            }
            const minutes = Math.floor(duration / 60);
            const seconds = (duration - minutes * 60).toString().substr(0, 2);
            return `${minutes}:${seconds}`;
        },
        currentTimeToString: function(currentTime) {
            if (isNaN(currentTime)) {
                return '00:00';
            }
            const minute = parseInt(currentTime / 60) % 60;
            const seconds = (currentTime % 60).toFixed();
            return (minute < 10 ? "0" + minute : minute) + ":" + (seconds < 10 ? "0" + seconds : seconds);
        },
        playSongToggle(song) {
            this.$emit("playSongToggle", song);            
        }
    }
};    