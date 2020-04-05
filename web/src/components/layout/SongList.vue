<template>
    <table class="song-table">
        <tbody>
            <tr 
                v-for="(song, index)  in songs"
                :key="song.id"
                class="song-list-row"
                v-bind:class="{
                    'active-song': isActiveSong(song),
                    'loading': isActiveSong(song) && loading,
                    'playing': isActiveSong(song) && playing,
                    'paused': isActiveSong(song) && !playing 
                }">
                <td class='song-list-album-cell' @click="$emit('toggleSong', song)">
                    <svg 
                        class="play-button-arrow" xmlns="http://www.w3.org/2000/svg" 
                        viewBox="-5 -5 34 34" 
                        preserveAspectRatio="xMidYMid meet">
                        <path d="M8 5v14l11-7z" fill="white"></path>
                    </svg>

                    <svg class='pause' xmlns="http://www.w3.org/2000/svg" 
                        viewBox="-5 -5 34 34" 
                        preserveAspectRatio="xMidYMid meet">
                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"  fill="white"/></svg>								
                    <svg class="spinner" v-bind:class="{'active': isActiveSong(song) && loading}"
                        viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg" stroke="#fff">
                        <g fill="none" fill-rule="evenodd" stroke-width="2">
                            <circle cx="22" cy="22" r="1">
                                <animate attributeName="r"
                                    begin="0s" dur="1.8s"
                                    values="1; 20"
                                    calcMode="spline"
                                    keyTimes="0; 1"
                                    keySplines="0.165, 0.84, 0.44, 1"
                                    repeatCount="indefinite" />
                                <animate attributeName="stroke-opacity"
                                    begin="0s" dur="1.8s"
                                    values="1; 0"
                                    calcMode="spline"
                                    keyTimes="0; 1"
                                    keySplines="0.3, 0.61, 0.355, 1"
                                    repeatCount="indefinite" />
                            </circle>
                            <circle cx="22" cy="22" r="1">
                                <animate attributeName="r"
                                    begin="-0.9s" dur="1.8s"
                                    values="1; 20"
                                    calcMode="spline"
                                    keyTimes="0; 1"
                                    keySplines="0.165, 0.84, 0.44, 1"
                                    repeatCount="indefinite" />
                                <animate attributeName="stroke-opacity"
                                    begin="-0.9s" dur="1.8s"
                                    values="1; 0"
                                    calcMode="spline"
                                    keyTimes="0; 1"
                                    keySplines="0.3, 0.61, 0.355, 1"
                                    repeatCount="indefinite" />
                            </circle>
                        </g>
                    </svg>
                    
                    <div v-if="bullet === 'artwork' && song.album" class="song-list-album-artwork-div">
                        <img class='song-list-album-artwork' :src="'../artwork/thumbnail~' + song.album.artworkFilename" :alt="song.album.title">
                    </div>
                    <div v-if="bullet === 'index'" class="song-list-album-artwork-div">
                        <svg  class='song-list-album-artwork song-list-index'>
                            <text x="12" y="22" fill="white">{{index+1}}</text>
                        </svg>
                    </div>                    
                </td>
                <td class="song-title-cell" @click.prevent="$emit('toggleSong', song)">
                    <div class="song-title">{{ song.title }}</div>
                    <a v-if="bullet === 'artwork' && song.album" class="album-title" @click="$emit('openAlbum', song.album)">{{ song.album.title }}</a>
                </td>

                <td>
                    ⋮
                </td>
            </tr>
            
        </tbody>
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
            "bullet"
        ],
    }
</script>

<style scoped>
</style>
