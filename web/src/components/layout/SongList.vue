<template>
    <table class="song-table">
        <tr v-for="(song, index)  in songs"
            :key="song.id"
            class="song-list-row"
            v-bind:class="{
                'active-song': isActiveSong(song),
                'loading': isActiveSong(song) && loading,
                'playing': isActiveSong(song) && playing,
                'paused': isActiveSong(song) && !playing 
            }">
            <td class='song-list-album-cell' @click="toggleSong(song)">
                <img src="../../assets/play-icon.svg" alt="Play" class="play">
                <img src="../../assets/pause-icon.svg" alt="Pause" class="pause">
                <img src="../../assets/spinner-icon.svg" alt="Loading" class="spinner">
                <div v-if="bullet === 'artwork' && song.album" class="song-list-album-artwork-div">
                    <img :src="'../artwork/thumbnail~' + song.album.artworkFilename" 
                        :alt="song.album.title"
                        class='song-list-album-artwork' 
                    >
                </div>
                <div v-else-if="bullet === 'index'" class="song-list-album-artwork-div">
                    <svg class='song-list-album-artwork song-list-index'>
                        <text x="12" y="22" fill="white">{{index+1}}</text>
                    </svg>
                </div>
                <div v-else class="song-list-album-artwork-div">
                    <img src="../../assets/headphones-icon.svg" class='song-list-album-artwork song-list-listen-icon'>
                </div>                                      
            </td>
            <td class="song-title-cell">
                <div class="song-title" @click="toggleSong(song)">{{ song.title }}</div>
                <a @click.prevent="openAlbum(song.album)"
                    v-if="showAlbumLink && song.album" 
                    class="album-title">{{ song.album.title }}</a>
            </td>
            <td>{{durationToString(song.duration)}}</td>
        </tr>
    </table>
</template>

<script>
    import SongHelperMixin from '../SongHelperMixin';
    export default {
        name: "SongList",
        mixins: [SongHelperMixin],
        props: [
            "activeSong",
            "loadingState",
            "playing",
            "songs",
            "bullet",
            "showAlbumLink"
        ],
    }
</script>